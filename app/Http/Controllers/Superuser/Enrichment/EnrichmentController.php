<?php

namespace App\Http\Controllers\Superuser\Enrichment;

use App\Entities\Enrichment\EnrichmentLog;
use App\Entities\Enrichment\FragranceAccord;
use App\Entities\Enrichment\FragranceNote;
use App\Entities\Enrichment\FragranceReference;
use App\Entities\Enrichment\ProductReferenceMap;
use App\Entities\Master\Product;
use App\Enrichment\DriverManager;
use App\Enrichment\Services\FuzzyMatcher;
use App\Enrichment\Services\NameNormalizer;
use App\Enrichment\Services\NotesParser;
use App\Enrichment\Services\ReferenceGrouper;
use App\Enrichment\Services\ResultValidator;
use App\Enrichment\Services\SearahParser;
use App\Http\Controllers\Controller;
use Auth;
use DB;
use Illuminate\Http\Request;
use Validator;

class EnrichmentController extends Controller
{
    public function __construct()
    {
        $this->route = "superuser.enrichment.products";
        $this->access = null;
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            $access = \App\Entities\Setting\UserMenu::where('user_id', $user->id)
                             ->whereHas('menu', function ($query2) {
                                $query2->where('route_name', $this->route);
                             })->first();
            $this->access = $access;
            return $next($request);
        });
    }

    protected function checkAccess(Request $request)
    {
        if (Auth::user()->is_superuser == 0 && empty($this->access)) {
            if ($request->ajax()) {
                return false;
            }
            abort(redirect()->route('superuser.index')->with('error', 'Anda tidak punya akses untuk membuka menu terkait'));
        }

        return true;
    }

    public function products(Request $request)
    {
        if (!$this->checkAccess($request)) {
            return $this->response(403, ['notification' => ['alert' => 'block', 'type' => 'alert-danger', 'header' => 'Error', 'content' => 'Anda tidak punya akses']]);
        }

        $baseQuery = Product::where('status', Product::STATUS['ACTIVE'])
                    ->whereNotNull('brand_name')->where('brand_name', '!=', '')
                    ->whereHas('sub_brand_reference', function ($query) {
                        $query->whereNotNull('name')->where('name', '!=', '')->where('name', '!=', '-')
                              ->where('link', 'like', '%fragrantica%');
                    });

        $ready = (clone $baseQuery)->count();

        $query = (clone $baseQuery)->with(['sub_brand_reference']);

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', '%' . $q . '%')
                    ->orWhere('code', 'like', '%' . $q . '%')
                    ->orWhere('brand_name', 'like', '%' . $q . '%')
                    ->orWhereHas('sub_brand_reference', function ($searahQuery) use ($q) {
                        $searahQuery->where('name', 'like', '%' . $q . '%');
                    });
            });
        }

        $products = $query->orderBy('name')->paginate(50)->appends($request->only('q'));

        $maps = ProductReferenceMap::with(['reference:id,status,needs_review'])
                    ->whereIn('product_id', $products->getCollection()->pluck('id')->all())
                    ->get()
                    ->keyBy('product_id');

        $stats = [
            'ready' => $ready,
            'references' => FragranceReference::count(),
            'needs_review' => FragranceReference::where('needs_review', true)->where('status', FragranceReference::STATUS['PENDING'])->count(),
        ];

        return view('superuser.enrichment.products', compact('products', 'maps', 'stats'));
    }

    public function runSelected(Request $request)
    {
        if (!$this->checkAccess($request)) {
            return $this->response(403, ['notification' => ['alert' => 'block', 'type' => 'alert-danger', 'header' => 'Error', 'content' => 'Anda tidak punya akses']]);
        }

        $validator = Validator::make($request->all(), [
            'ids' => 'required|array|min:1|max:30',
            'ids.*' => 'string',
        ]);

        if ($validator->fails()) {
            return $this->response(400, ['notification' => ['alert' => 'block', 'type' => 'alert-danger', 'header' => 'Error', 'content' => 'Pilih 1-30 produk dulu']]);
        }

        try {
            $drivers = $this->driverChain();
        } catch (\InvalidArgumentException $e) {
            return $this->response(400, ['notification' => ['alert' => 'block', 'type' => 'alert-danger', 'header' => 'Error', 'content' => $e->getMessage()]]);
        }

        $products = Product::with(['sub_brand_reference'])
                    ->whereIn('id', $request->ids)
                    ->get();

        $rows = [];
        $skipped = 0;
        foreach ($products as $product) {
            $reference = $product->sub_brand_reference;
            $searah = $reference ? trim((string) $reference->name) : '';
            $hasFragrantica = $reference && stripos((string) $reference->link, 'fragrantica') !== false;
            if (!$product->brand_name || $searah === '' || $searah === '-' || !$hasFragrantica) {
                $skipped++;
                continue;
            }
            $rows[] = [
                'id' => $product->id,
                'code' => $product->code,
                'name' => $product->name,
                'brand' => $this->referenceBrand($reference, $product->brand_name),
                'searah' => $searah,
                'description' => $product->description,
            ];
        }

        if (count($rows) === 0) {
            return $this->response(400, ['notification' => ['alert' => 'block', 'type' => 'alert-danger', 'header' => 'Error', 'content' => 'Tidak ada produk dengan brand + searah Fragrantica']]);
        }

        set_time_limit(0);

        $grouped = ReferenceGrouper::group($rows);
        $results = [];
        $quotaHit = false;
        $driverIndex = 0;
        $driver = $drivers[0];
        $switchedDrivers = [];
        foreach ($grouped['references'] as $reference) {
            $result = $this->enrichReference($driver, $reference);
            while (!empty($result['quota_exceeded']) && isset($drivers[$driverIndex + 1])) {
                $driverIndex++;
                $driver = $drivers[$driverIndex];
                $switchedDrivers[$driver->name()] = true;
                $result = $this->enrichReference($driver, $reference);
            }
            $results[] = $result;
            if (!empty($result['quota_exceeded'])) {
                $quotaHit = true;
                break;
            }
        }

        $lines = [];
        foreach ($results as $result) {
            $lines[] = htmlspecialchars($result['label'], ENT_QUOTES) . ' &mdash; ' . $result['summary'];
        }
        if ($skipped > 0) {
            $lines[] = $skipped . ' produk dilewati (tanpa brand atau searah Fragrantica)';
        }
        if (count($switchedDrivers) > 0) {
            $lines[] = 'Driver aktif kena kuota &mdash; dialihkan ke cadangan: ' . htmlspecialchars(implode(', ', array_keys($switchedDrivers)), ENT_QUOTES);
        }
        if ($quotaHit) {
            $lines[] = '<strong>Kuota API ' . htmlspecialchars(ucfirst($driver->name()), ENT_QUOTES) . ' habis</strong> &mdash; proses dihentikan. Cek kuota/rate limit di dashboard provider, atau ganti driver di halaman Pengaturan.';
        }

        $response = [
            'notification' => [
                'alert' => 'block',
                'type' => 'alert-success',
                'header' => 'Selesai',
                'content' => implode('<br>', $lines) . '<br>Buka halaman Hasil untuk memeriksa dan memverifikasi.',
            ],
            'redirect_to' => route('superuser.enrichment.results'),
        ];

        return $this->response(200, $response);
    }

    /**
     * Merek parfum acuan diambil dari link Fragrantica (/perfume/{Merek}/...),
     * bukan brand internal PPI — brand internal membuat LLM menjawab not_found.
     */
    protected function referenceBrand($reference, $fallback)
    {
        if ($reference && preg_match('#/perfume/([^/]+)/#i', (string) $reference->link, $matches)) {
            $brand = trim(str_replace('-', ' ', urldecode($matches[1])));
            if ($brand !== '') {
                return $brand;
            }
        }

        return $fallback;
    }

    /**
     * Driver aktif + cadangan dari Pengaturan. Cadangan tanpa key dilewati.
     */
    protected function driverChain()
    {
        $names = array_merge([DriverManager::activeName()], (array) setting('enrichment.fallback', []));
        $chain = [];
        foreach (array_unique($names) as $name) {
            try {
                $chain[] = DriverManager::make($name);
            } catch (\InvalidArgumentException $e) {
                if (count($chain) === 0) {
                    throw $e;
                }
            }
        }

        return $chain;
    }

    protected function enrichReference($driver, array $reference)
    {
        $label = $reference['brand'] . ' - ' . $reference['searah'];
        $summary = ['label' => $label, 'ref_key' => $reference['ref_key'], 'summary' => '', 'error' => false, 'quota_exceeded' => false];

        $referenceRow = FragranceReference::withTrashed()->firstOrNew(['ref_key' => $reference['ref_key']]);
        if ($referenceRow->trashed()) {
            $referenceRow->restore();
        }
        $referenceRow->brand = $reference['brand'];
        $referenceRow->searah = $reference['searah'];

        $internalNotes = ReferenceGrouper::internalNotesFor($reference);
        $searahExpected = SearahParser::parse($reference['searah']);

        $startAt = microtime(true);
        $enrichResult = $driver->enrich($reference['brand'], $reference['searah'], $internalNotes);
        $durationMs = (int) round((microtime(true) - $startAt) * 1000);

        DB::transaction(function () use ($referenceRow, $driver, $enrichResult, $durationMs) {
            $referenceRow->save();
            EnrichmentLog::create([
                'fragrance_reference_id' => $referenceRow->id,
                'driver' => $driver->name(),
                'model' => $enrichResult['model'],
                'use_search' => !empty($enrichResult['use_search']),
                'status' => $enrichResult['success'] ? EnrichmentLog::STATUS['SUCCESS'] : EnrichmentLog::STATUS['ERROR'],
                'http_status' => $enrichResult['http_status'],
                'error_message' => $enrichResult['error'],
                'raw_response' => $enrichResult['raw'],
                'duration_ms' => $durationMs,
            ]);
        });

        if (!$enrichResult['success']) {
            if ($referenceRow->match_status === null) {
                $referenceRow->needs_review = true;
                $referenceRow->review_reasons = ['Request LLM gagal: ' . $enrichResult['error']];
                $referenceRow->status = FragranceReference::STATUS['PENDING'];
                $referenceRow->verified_at = null;
                $referenceRow->save();
            }

            $summary['summary'] = '<span class="badge badge-danger">GAGAL</span> ' . htmlspecialchars($enrichResult['error'], ENT_QUOTES);
            $summary['error'] = true;
            $summary['quota_exceeded'] = ($enrichResult['http_status'] === 429
                || stripos((string) $enrichResult['error'], 'quota') !== false
                || stripos((string) $enrichResult['error'], 'RESOURCE_EXHAUSTED') !== false);

            return $summary;
        }

        $data = $enrichResult['data'];
        $internalParsed = NotesParser::parse($internalNotes);
        $internalFlat = NotesParser::flatList($internalParsed);
        $llmNotes = array_merge(
            isset($data['notes']['top']) && is_array($data['notes']['top']) ? $data['notes']['top'] : [],
            isset($data['notes']['middle']) && is_array($data['notes']['middle']) ? $data['notes']['middle'] : [],
            isset($data['notes']['base']) && is_array($data['notes']['base']) ? $data['notes']['base'] : []
        );
        $crossCheck = FuzzyMatcher::crossCheck($llmNotes, $internalFlat);
        $data['verification']['cross_check']['reference_notes_status'] = $crossCheck['status'];
        $data['verification']['cross_check']['differences'] = $crossCheck['differences'];

        $validation = ResultValidator::evaluate($data, $searahExpected);

        if (!$validation['ok']) {
            $referenceRow->needs_review = true;
            $referenceRow->review_reasons = $validation['errors'];
            $referenceRow->status = FragranceReference::STATUS['PENDING'];
            $referenceRow->verified_at = null;
            $referenceRow->save();

            $summary['summary'] = '<span class="badge badge-danger">SCHEMA INVALID</span> ' . htmlspecialchars(implode('; ', array_slice($validation['errors'], 0, 3)), ENT_QUOTES);
            $summary['error'] = true;

            return $summary;
        }

        DB::transaction(function () use ($referenceRow, $data, $crossCheck, $validation, $reference) {
            $this->applyResult($referenceRow, $data, $crossCheck);
            $referenceRow->needs_review = count($validation['review_reasons']) > 0;
            $referenceRow->review_reasons = $validation['review_reasons'] ?: null;
            $referenceRow->status = FragranceReference::STATUS['PENDING'];
            $referenceRow->verified_at = null;
            $referenceRow->verified_by = null;
            $referenceRow->save();

            $this->syncNotes($referenceRow, $data);
            $this->syncAccords($referenceRow, $data);

            foreach ($reference['products'] as $product) {
                $map = ProductReferenceMap::withTrashed()->firstOrNew(['product_id' => $product['id']]);
                $map->fragrance_reference_id = $referenceRow->id;
                if ($map->trashed()) {
                    $map->restore();
                }
                $map->save();
            }
        });

        $matchStatus = isset($data['match']['status']) ? $data['match']['status'] : '?';
        if ($matchStatus === 'found' && $validation['review_reasons'] && count($validation['review_reasons']) > 0) {
            $badge = '<span class="badge badge-warning">PERLU REVIEW</span>';
        } elseif ($matchStatus === 'found') {
            $badge = '<span class="badge badge-success">FOUND</span>';
        } else {
            $badge = '<span class="badge badge-secondary">' . htmlspecialchars(mb_strtoupper($matchStatus), ENT_QUOTES) . '</span>';
        }

        $year = $referenceRow->release_year !== null ? ' (' . $referenceRow->release_year . ')' : '';
        $summary['summary'] = $badge . htmlspecialchars($year, ENT_QUOTES) . ' ' . htmlspecialchars($referenceRow->confidence ?: '', ENT_QUOTES);

        return $summary;
    }

    protected function applyResult(FragranceReference $referenceRow, array $data, array $crossCheck)
    {
        $referenceRow->match_status = isset($data['match']['status']) ? $data['match']['status'] : null;
        $referenceRow->matched_name = isset($data['match']['matched_name']) ? $data['match']['matched_name'] : null;
        $referenceRow->matched_brand = isset($data['match']['matched_brand']) ? $data['match']['matched_brand'] : null;
        $referenceRow->concentration = isset($data['match']['concentration']) ? $data['match']['concentration'] : null;
        $referenceRow->match_note = isset($data['match']['match_note']) ? $data['match']['match_note'] : null;
        $referenceRow->release_year = isset($data['release_year']) && is_int($data['release_year']) ? $data['release_year'] : null;
        $referenceRow->release_year_confidence = isset($data['verification']['release_year_confidence']) ? $data['verification']['release_year_confidence'] : null;
        $referenceRow->perfumers = isset($data['perfumers']) ? $data['perfumers'] : [];
        $referenceRow->gender_target = isset($data['gender_target']) ? $data['gender_target'] : null;
        $referenceRow->scent_family = isset($data['scent_family']) ? $data['scent_family'] : null;
        $referenceRow->main_accords = isset($data['main_accords']) ? $data['main_accords'] : [];
        $referenceRow->notes_top = isset($data['notes']['top']) && is_array($data['notes']['top']) ? $data['notes']['top'] : [];
        $referenceRow->notes_middle = isset($data['notes']['middle']) && is_array($data['notes']['middle']) ? $data['notes']['middle'] : [];
        $referenceRow->notes_base = isset($data['notes']['base']) && is_array($data['notes']['base']) ? $data['notes']['base'] : [];
        $referenceRow->tags_mood = isset($data['derived_tags']['mood']) && is_array($data['derived_tags']['mood']) ? $data['derived_tags']['mood'] : [];
        $referenceRow->tags_season = isset($data['derived_tags']['season']) && is_array($data['derived_tags']['season']) ? $data['derived_tags']['season'] : [];
        $referenceRow->tags_time_of_day = isset($data['derived_tags']['time_of_day']) && is_array($data['derived_tags']['time_of_day']) ? $data['derived_tags']['time_of_day'] : [];
        $referenceRow->description_id = isset($data['description_id']) ? $data['description_id'] : null;
        $referenceRow->confidence = isset($data['verification']['confidence']) ? $data['verification']['confidence'] : null;
        $referenceRow->sources = isset($data['verification']['sources']) && is_array($data['verification']['sources']) ? $data['verification']['sources'] : [];
        $referenceRow->reference_notes_status = $crossCheck['status'];
        $referenceRow->notes_differences = $crossCheck['differences'];
    }

    protected function syncNotes(FragranceReference $referenceRow, array $data)
    {
        $referenceRow->notes()->detach();

        foreach (['top' => 'notes_top', 'middle' => 'notes_middle', 'base' => 'notes_base'] as $position => $field) {
            foreach ($referenceRow->{$field} ?: [] as $noteName) {
                $note = $this->upsertTerm(FragranceNote::class, $noteName);
                $referenceRow->notes()->attach($note->id, ['position' => $position]);
            }
        }
    }

    protected function syncAccords(FragranceReference $referenceRow, array $data)
    {
        $referenceRow->accords()->detach();

        $prominence = 0;
        foreach ($referenceRow->main_accords ?: [] as $accordName) {
            $prominence++;
            $accord = $this->upsertTerm(FragranceAccord::class, $accordName);
            $referenceRow->accords()->attach($accord->id, ['prominence' => $prominence]);
        }
    }

    protected function upsertTerm($class, $name)
    {
        $name = trim((string) $name);
        $model = $class::withTrashed()->firstOrNew(['slug' => NameNormalizer::slug($name)]);
        $model->name = $name;
        $model->status = $class::STATUS['ACTIVE'];
        if ($model->trashed()) {
            $model->restore();
        }
        $model->save();

        return $model;
    }

    public function results(Request $request)
    {
        if (!$this->checkAccess($request)) {
            return $this->response(403, ['notification' => ['alert' => 'block', 'type' => 'alert-danger', 'header' => 'Error', 'content' => 'Anda tidak punya akses']]);
        }

        $query = FragranceReference::with(['notes', 'accords']);

        if ($request->filled('filter')) {
            if ($request->filter === 'needs_review') {
                $query->where('needs_review', true);
            } elseif ($request->filter === 'pending') {
                $query->where('status', FragranceReference::STATUS['PENDING']);
            } elseif ($request->filter === 'verified') {
                $query->where('status', FragranceReference::STATUS['VERIFIED']);
            }
        }

        $references = $query->orderBy('needs_review', 'desc')->orderBy('id', 'desc')->paginate(25)->appends($request->only('filter'));

        $counts = [
            'all' => FragranceReference::count(),
            'needs_review' => FragranceReference::where('needs_review', true)->count(),
            'pending' => FragranceReference::where('status', FragranceReference::STATUS['PENDING'])->count(),
            'verified' => FragranceReference::where('status', FragranceReference::STATUS['VERIFIED'])->count(),
        ];

        $provenance = EnrichmentLog::select('id', 'fragrance_reference_id', 'driver', 'model', 'created_at')
                    ->where('status', EnrichmentLog::STATUS['SUCCESS'])
                    ->whereIn('fragrance_reference_id', $references->pluck('id')->all())
                    ->orderBy('id', 'desc')
                    ->get()
                    ->unique('fragrance_reference_id')
                    ->keyBy('fragrance_reference_id');

        $driverLabels = DriverManager::available();

        return view('superuser.enrichment.results', compact('references', 'counts', 'provenance', 'driverLabels'));
    }

    public function compare(Request $request)
    {
        if (!$this->checkAccess($request)) {
            return $this->response(403, ['notification' => ['alert' => 'block', 'type' => 'alert-danger', 'header' => 'Error', 'content' => 'Anda tidak punya akses']]);
        }

        $logs = EnrichmentLog::select('id', 'fragrance_reference_id', 'driver', 'model', 'status', 'http_status', 'error_message', 'raw_response', 'created_at')
                    ->orderBy('id', 'desc')
                    ->take(600)
                    ->get();

        $latest = [];
        foreach ($logs as $log) {
            if (!isset($latest[$log->fragrance_reference_id])) {
                $latest[$log->fragrance_reference_id] = [];
            }
            if (!isset($latest[$log->fragrance_reference_id][$log->driver])) {
                $latest[$log->fragrance_reference_id][$log->driver] = $log;
            }
        }

        $references = FragranceReference::whereIn('id', array_keys($latest))->get()->keyBy('id');
        $drivers = array_keys(DriverManager::available());

        $rows = [];
        foreach ($latest as $referenceId => $perDriver) {
            if (!isset($references[$referenceId])) {
                continue;
            }
            $cells = [];
            $filled = 0;
            foreach ($drivers as $driverName) {
                $cells[$driverName] = isset($perDriver[$driverName]) ? $this->summarizeLog($perDriver[$driverName]) : null;
                if ($cells[$driverName] !== null) {
                    $filled++;
                }
            }
            $rows[] = ['reference' => $references[$referenceId], 'cells' => $cells, 'filled' => $filled];
        }

        usort($rows, function ($a, $b) {
            return $b['filled'] - $a['filled'];
        });

        if ($request->get('scope', 'multi') !== 'all') {
            $rows = array_values(array_filter($rows, function ($row) {
                return $row['filled'] >= 2;
            }));
        }

        $rows = array_slice($rows, 0, 50);

        $driverLabels = DriverManager::available();

        return view('superuser.enrichment.compare', compact('rows', 'drivers', 'driverLabels'));
    }

    protected function summarizeLog($log)
    {
        $summary = [
            'status' => $log->status,
            'model' => $log->model,
            'http_status' => $log->http_status,
            'error' => $log->error_message,
            'when' => $log->created_at,
            'match' => null,
            'name' => null,
            'year' => null,
            'confidence' => null,
            'sources' => null,
            'notes' => null,
        ];

        if ($log->status !== EnrichmentLog::STATUS['SUCCESS']) {
            return $summary;
        }

        $text = (string) $log->raw_response;
        $start = strpos($text, '{');
        $end = strrpos($text, '}');
        if ($start === false || $end === false || $end <= $start) {
            return $summary;
        }

        $data = json_decode(substr($text, $start, $end - $start + 1), true);
        if (!is_array($data)) {
            return $summary;
        }

        $summary['match'] = isset($data['match']['status']) ? $data['match']['status'] : null;
        $summary['name'] = isset($data['match']['matched_name']) ? $data['match']['matched_name'] : null;
        $summary['year'] = isset($data['release_year']) ? $data['release_year'] : null;
        $summary['confidence'] = isset($data['verification']['confidence']) ? $data['verification']['confidence'] : null;
        $summary['sources'] = isset($data['verification']['sources']) && is_array($data['verification']['sources']) ? count($data['verification']['sources']) : 0;

        $notes = 0;
        foreach (['top', 'middle', 'base'] as $layer) {
            if (isset($data['notes'][$layer]) && is_array($data['notes'][$layer])) {
                $notes += count($data['notes'][$layer]);
            }
        }
        $summary['notes'] = $notes;

        return $summary;
    }

    public function verify(Request $request)
    {
        if (!$this->checkAccess($request)) {
            return $this->response(403, ['notification' => ['alert' => 'block', 'type' => 'alert-danger', 'header' => 'Error', 'content' => 'Anda tidak punya akses']]);
        }

        $validator = Validator::make($request->all(), ['id' => 'required|integer']);
        if ($validator->fails()) {
            return $this->response(400, ['notification' => ['alert' => 'block', 'type' => 'alert-danger', 'header' => 'Error', 'content' => 'ID tidak valid']]);
        }

        $reference = FragranceReference::find($request->id);
        if (!$reference) {
            return $this->response(404, ['notification' => ['alert' => 'block', 'type' => 'alert-danger', 'header' => 'Error', 'content' => 'Data tidak ditemukan']]);
        }

        $reference->status = FragranceReference::STATUS['VERIFIED'];
        $reference->verified_at = now();
        $reference->verified_by = Auth::id();
        $reference->save();

        return $this->response(200, [
            'notification' => ['alert' => 'notify', 'type' => 'success', 'content' => 'Referensi diverifikasi'],
            'redirect_to' => '#datatable',
        ]);
    }

    public function reject(Request $request)
    {
        if (!$this->checkAccess($request)) {
            return $this->response(403, ['notification' => ['alert' => 'block', 'type' => 'alert-danger', 'header' => 'Error', 'content' => 'Anda tidak punya akses']]);
        }

        $validator = Validator::make($request->all(), ['id' => 'required|integer']);
        if ($validator->fails()) {
            return $this->response(400, ['notification' => ['alert' => 'block', 'type' => 'alert-danger', 'header' => 'Error', 'content' => 'ID tidak valid']]);
        }

        $reference = FragranceReference::find($request->id);
        if (!$reference) {
            return $this->response(404, ['notification' => ['alert' => 'block', 'type' => 'alert-danger', 'header' => 'Error', 'content' => 'Data tidak ditemukan']]);
        }

        $reference->status = FragranceReference::STATUS['REJECTED'];
        $reference->verified_at = null;
        $reference->verified_by = null;
        $reference->save();

        return $this->response(200, [
            'notification' => ['alert' => 'notify', 'type' => 'success', 'content' => 'Referensi ditolak'],
            'redirect_to' => '#datatable',
        ]);
    }

    public function sampleRun(Request $request)
    {
        if (!$this->checkAccess($request)) {
            return $this->response(403, ['notification' => ['alert' => 'block', 'type' => 'alert-danger', 'header' => 'Error', 'content' => 'Anda tidak punya akses']]);
        }

        $validator = Validator::make($request->all(), [
            'brand' => 'required|string',
            'searah' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->response(400, ['notification' => ['alert' => 'block', 'type' => 'alert-danger', 'header' => 'Error', 'content' => 'Brand dan searah wajib diisi']]);
        }

        try {
            $driver = DriverManager::make();
        } catch (\InvalidArgumentException $e) {
            return $this->response(400, ['notification' => ['alert' => 'block', 'type' => 'alert-danger', 'header' => 'Error', 'content' => $e->getMessage()]]);
        }

        set_time_limit(0);

        $enrichResult = $driver->enrich($request->brand, $request->searah, (string) $request->notes);

        if (!$enrichResult['success']) {
            return $this->response(200, [
                'notification' => ['alert' => 'block', 'type' => 'alert-danger', 'header' => 'Request gagal', 'content' => htmlspecialchars($enrichResult['error'], ENT_QUOTES)],
            ]);
        }

        $data = $enrichResult['data'];
        $internalParsed = NotesParser::parse((string) $request->notes);
        $llmNotes = array_merge(
            isset($data['notes']['top']) && is_array($data['notes']['top']) ? $data['notes']['top'] : [],
            isset($data['notes']['middle']) && is_array($data['notes']['middle']) ? $data['notes']['middle'] : [],
            isset($data['notes']['base']) && is_array($data['notes']['base']) ? $data['notes']['base'] : []
        );
        $crossCheck = FuzzyMatcher::crossCheck($llmNotes, NotesParser::flatList($internalParsed));
        $data['verification']['cross_check']['reference_notes_status'] = $crossCheck['status'];
        $data['validation'] = ResultValidator::evaluate($data, SearahParser::parse($request->searah));

        $matchStatus = isset($data['match']['status']) ? $data['match']['status'] : '?';
        $content = 'match.status: <strong>' . htmlspecialchars($matchStatus, ENT_QUOTES) . '</strong><br>'
                 . 'Validasi skema: <strong>' . ($data['validation']['ok'] ? 'OK' : 'GAGAL') . '</strong>'
                 . ($data['validation']['ok'] ? '' : '<br>' . htmlspecialchars(implode('; ', array_slice($data['validation']['errors'], 0, 5)), ENT_QUOTES)) . '<br>'
                 . 'Alasan review: ' . htmlspecialchars(implode('; ', $data['validation']['review_reasons'] ?: ['-']), ENT_QUOTES);

        if ($matchStatus === 'not_found') {
            $correct = ($data['validation']['ok'] || true) && count($data['validation']['errors']) === 0;
            $content .= '<br><strong>' . ($correct ? 'Sampel negatif DITERIMA (not_found, benar)' : 'Ada masalah di atas') . '</strong>';
        }

        return $this->response(200, [
            'notification' => ['alert' => 'block', 'type' => $matchStatus === 'found' ? 'alert-info' : 'alert-warning', 'header' => 'Hasil uji (tidak disimpan)', 'content' => $content],
            'data' => $data,
        ]);
    }
}
