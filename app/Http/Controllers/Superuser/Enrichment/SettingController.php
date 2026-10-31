<?php

namespace App\Http\Controllers\Superuser\Enrichment;

use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Http\Request;
use Validator;

class SettingController extends Controller
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

    public function index(Request $request)
    {
        if (Auth::user()->is_superuser == 0 && empty($this->access)) {
            return redirect()->route('superuser.index')->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
        }

        $driver = \App\Enrichment\DriverManager::activeName();

        $driverMeta = [];
        foreach (config('enrichment.drivers', []) as $name => $config) {
            $driverMeta[$name] = [
                'model' => setting('enrichment.model.' . $name, $config['model']),
                'default_model' => $config['model'],
                'supports_search' => !empty($config['supports_search']),
                'has_key' => !empty(setting('enrichment.credentials.' . $name . '.api_key', '')) || !empty(env($config['env_key'])),
            ];
        }

        return view('superuser.enrichment.settings', [
            'driverOptions' => \App\Enrichment\DriverManager::available(),
            'driver' => $driver,
            'driverMeta' => $driverMeta,
            'model' => setting('enrichment.model.' . $driver, config('enrichment.drivers.' . $driver . '.model')),
            'useSearch' => setting('enrichment.use_search', true),
            'fallback' => array_values((array) setting('enrichment.fallback', [])),
            'hasApiKey' => !empty($driverMeta[$driver]['has_key']),
        ]);
    }

    public function save(Request $request)
    {
        if (Auth::user()->is_superuser == 0 && empty($this->access)) {
            return $this->response(403, ['notification' => ['alert' => 'block', 'type' => 'alert-danger', 'header' => 'Error', 'content' => 'Anda tidak punya akses']]);
        }

        $validator = Validator::make($request->all(), [
            'driver' => 'required|string',
            'model' => 'nullable|string|max:50',
            'fallback' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->response(400, ['notification' => ['alert' => 'block', 'type' => 'alert-danger', 'header' => 'Error', 'content' => $validator->errors()->all()]]);
        }

        if (!array_key_exists($request->driver, \App\Enrichment\DriverManager::available())) {
            return $this->response(400, ['notification' => ['alert' => 'block', 'type' => 'alert-danger', 'header' => 'Error', 'content' => 'Driver tidak dikenal']]);
        }

        $available = array_keys(\App\Enrichment\DriverManager::available());
        $fallback = array_values(array_intersect($available, array_diff((array) $request->fallback, [$request->driver])));

        $payload = [
            'enrichment.driver' => $request->driver,
            'enrichment.model.' . $request->driver => $request->model ?: config('enrichment.drivers.' . $request->driver . '.model'),
            'enrichment.use_search' => (bool) $request->use_search,
            'enrichment.fallback' => $fallback,
        ];

        if ($request->filled('api_key')) {
            $payload['enrichment.credentials.' . $request->driver . '.api_key'] = trim($request->api_key);
        }

        setting($payload);
        setting()->save();

        return $this->response(200, [
            'notification' => ['alert' => 'notify', 'type' => 'success', 'content' => 'Pengaturan Enrichment tersimpan'],
            'redirect_to' => 'reload()',
        ]);
    }
}
