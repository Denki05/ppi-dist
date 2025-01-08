<?php

namespace App\Http\Controllers\Superuser\Accounting;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Entities\Accounting\FinanceSimulationPrice;
use App\Entities\Accounting\FinanceSimulationPriceDetail;
use App\Entities\Penjualan\PackingOrder;
use App\Entities\Penjualan\PackingOrderDetail;
use App\Entities\Penjualan\PackingOrderItem;
use App\Entities\Master\ProductFinance;
use App\Entities\Master\CustomerOtherAddress;
use App\Entities\Setting\UserMenu;
use Validator;
use Carbon\Carbon;
use Auth;
use DB;

class FinanceSimulationPriceController extends Controller
{
    public function __construct()
    {
        $this->view = "superuser.accounting.finance_simulation.";
        $this->route = "superuser.accounting.finance_simulation";
        $this->user_menu = new UserMenu;
        $this->access = null;

        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            $this->access = $this->user_menu->where('user_id', $user->id)
                ->whereHas('menu', function ($query) {
                    $query->where('route_name', $this->route);
                })
                ->first();

            return $next($request);
        });
    }

    public function getInvoice(Request $request)
    {
        $month = $request->input('month');
        
        $invoices = PackingOrder::query()
            ->leftJoin('penjualan_so', 'penjualan_do.so_id', '=', 'penjualan_so.id')
            ->leftJoin('master_customer_other_addresses', 'penjualan_do.customer_other_address_id', '=', 'master_customer_other_addresses.id')
            ->where('penjualan_do.status', 6)
            ->whereMonth('penjualan_so.so_date', $month)
            ->select([
                'penjualan_do.id AS do_id',
                'penjualan_do.do_code AS invoice_code',
                'penjualan_so.so_date AS invoice_date',
                'master_customer_other_addresses.name AS customer_name',
                'master_customer_other_addresses.text_kota AS customer_city',
            ])
            ->get();

        return response()->json($invoices);
    }

    public function index(Request $request)
    {
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user) || $this->access->can_read == 0) {
                return redirect()->route('superuser.index')->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $months = collect(range(1, 12))->map(function ($month) {
            $date = Carbon::create(null, $month);
            return [
                'id' => $month,
                'monthName' => $date->format('F'),
            ];
        });

        $customers = CustomerOtherAddress::get();

        // Filter data berdasarkan parameter pencarian
        $simulations = FinanceSimulationPrice::query();

        if ($request->filled('customer')) {
            $simulations->whereHas('simulation_detail', function ($query) use ($request) {
                $query->where('customer_id', $request->customer);
            });
        }

        if ($request->filled('month')) {
            $simulations->whereMonth('created_at', $request->month);
        }

        if ($request->filled('year')) {
            $simulations->whereYear('created_at', $request->year);
        }

        $data = [
            'months' => $months,
            'simulations' => $simulations->get(),
            'customers' => $customers,
        ];

        return view($this->view . "index", $data);
    }

    public function store(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['message' => 'Invalid request'], 400);
        }

        $validator = Validator::make($request->all(), [
            'month' => 'required',
            'year' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->response(400, [
                'notification' => [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $validator->errors()->all(),
                ],
            ]);
        }

        try {
            DB::beginTransaction();

            // Fetch packing orders for the specified month
            $packingOrders = PackingOrder::leftJoin('penjualan_so', 'penjualan_do.so_id', '=', 'penjualan_so.id')
            ->where('penjualan_do.status', 6)
            ->whereMonth('penjualan_so.so_date', $request->month)
            ->whereYear('penjualan_so.so_date', $request->year);

            if ($request->filled('customer')) {
                $packingOrders->where('penjualan_do.customer_other_address_id', $request->customer);
            }

            $packingOrders = $packingOrders->select(
                'penjualan_do.id as id', 
                'penjualan_do.do_code as invoice_code',
                'penjualan_so.so_date as invoice_date',
            )->get();

            if ($packingOrders->isEmpty()) {
                // return $this->response(200, ['message' => 'No data found for the specified month']);
                return $this->response(200, [
                    'notification' => [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => ['Data Invoice tidak ditemukan untuk bulan dan tahun yang dipilih.'],
                    ],
                ]);
            }

            // Check for duplicate invoice codes
            $existingInvoices = FinanceSimulationPrice::whereIn('do_id', $packingOrders->pluck('id'))->pluck('code')->toArray();

            if (!empty($existingInvoices)) {
                return $this->response(400, [
                    'notification' => [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Error',
                        'content' => 'The following invoice codes already exist: ' . implode(', ', $existingInvoices),
                    ],
                ]);
            }

            // Fetch product finance data indexed by product ID
            $productFinance = ProductFinance::all()->keyBy('id');

            $simulations = [];
            $details = [];
            $timestamp = now();

            foreach ($packingOrders as $order) {
                // Create simulation entry
                $simulations[] = [
                    'code' => $order->invoice_code,
                    'do_id' => $order->id,
                    'status' => FinanceSimulationPrice::STATUS['ACTIVE'],
                    'created_at' => $order->invoice_date,
                    'updated_at' => $timestamp,
                ];

                // Get items for the current packing order
                $orderItems = PackingOrderItem::where('do_id', $order->id)->get();

                foreach ($orderItems as $item) {
                    if (isset($productFinance[$item->product_packaging_id])) {
                        $finance = $productFinance[$item->product_packaging_id];

                        $details[] = [
                            'finance_simulation_id' => $order->invoice_code, // Placeholder, updated later
                            'product_packaging_id' => $item->product_packaging_id,
                            'price_buying' => $finance->buying_price_usd_unit,
                            'price_selling' => $finance->selling_price_usd_unit,
                            'qty' => $item->qty,
                            'subtotal_harga_beli' => $finance->buying_price_usd_unit * $item->qty,
                            'subtotal_harga_jual' => $finance->selling_price_usd_unit * $item->qty,
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ];
                    }
                }
            }

            // Insert simulations and retrieve their IDs
            FinanceSimulationPrice::insert($simulations);
            $insertedSimulations = FinanceSimulationPrice::whereIn('code', collect($simulations)->pluck('code'))->get();

            // Map simulation codes to their IDs
            $simulationMap = $insertedSimulations->pluck('id', 'code');

            // Update details with the correct simulation IDs
            foreach ($details as &$detail) {
                $detail['finance_simulation_id'] = $simulationMap[$detail['finance_simulation_id']] ?? null;

                // Ensure no missing mapping
                if (is_null($detail['finance_simulation_id'])) {
                    throw new \Exception('Simulation ID not found for code: ' . $detail['finance_simulation_id']);
                }
            }

            // Bulk insert simulation details
            FinanceSimulationPriceDetail::insert($details);

            DB::commit();

            return $this->response(200, [
                'notification' => [
                    'alert' => 'notify',
                    'type' => 'success',
                    'content' => 'Success',
                ],
                'redirect_to' => route('superuser.accounting.finance_simulation.index'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->response(500, [
                'notification' => [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => 'An error occurred: ' . $e->getMessage(),
                ],
            ]);
        }
    }

    public function removeData(Request $request)
    {
        // Authorization check
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0) {
                return redirect()->route('superuser.index')->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        try {
            DB::beginTransaction();

            // Truncate tables
            FinanceSimulationPrice::truncate();
            FinanceSimulationPriceDetail::truncate();

            DB::commit();

            return redirect()->back()->with('message', 'Berhasil remove data!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Gagal menghapus data! Error: ' . $e->getMessage());
        }
    }

    public function page_report(Request $request)
    {
        // Authorization check
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user) || $this->access->can_read == 0) {
                return redirect()->route('superuser.index')->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
            }
        }
    
        // Filter bulan dan tahun dari request
        $selectedBulan = $request->get('bulan', null);
        $selectedTahun = $request->get('tahun', null);
    
        // Ambil semua bulan dan tahun yang tersedia di database
        $availableMonths = FinanceSimulationPrice::query()
            ->selectRaw('MONTH(created_at) as bulan, YEAR(created_at) as tahun')
            ->groupBy('bulan', 'tahun')
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'asc')
            ->get();
    
        $bulan = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];
    
        $query = FinanceSimulationPrice::query()
            ->where('status', FinanceSimulationPrice::STATUS['ACTIVE']);

        if ($selectedBulan && $selectedTahun) {
            $query->whereMonth('created_at', $selectedBulan)
                ->whereYear('created_at', $selectedTahun);
            $simulation = $query->get();
        } else {
            $simulation = collect(); // Mengembalikan koleksi kosong jika bulan/tahun tidak dipilih
        }
    
        $simulation = $query->get();
    
        $data = [
            'simulation' => $simulation,
            'availableMonths' => $availableMonths,
            'bulan' => $bulan,
            'selectedBulan' => $selectedBulan,
            'selectedTahun' => $selectedTahun,
        ];
    
        return view($this->view . "report", $data);
    }
}