<?php

namespace App\Http\Controllers\Superuser\Master;

use App\Entities\Master\Mitra;
use App\Entities\Master\MitraDetail;
use App\Repositories\CodeRepo;
use App\Http\Controllers\Controller;
use App\Entities\Master\CustomerOtherAddress;
use App\Exports\Master\MitraSettingImportTemplate;
use App\Imports\Master\MitraSettingImport;
use Illuminate\Http\Request;
use DB;
use Excel;
use App\Entities\Setting\UserMenu;
use Validator;
use Auth;

class MitraController extends Controller
{
    public function __construct(){
        $this->route = "superuser.master.mitra";
        $this->user_menu = new UserMenu;
        $this->access = null;
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            $access = $this->user_menu;
            $access = $access->where('user_id',$user->id)
                             ->whereHas('menu',function($query2){
                                $query2->where('route_name',$this->route);
                             })
                             ->first();
            $this->access = $access;
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access)){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['mitra'] = Mitra::get();

        return view('superuser.master.mitra.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        return view('superuser.master.mitra.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'address' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $validator->errors()->all(),
                ];
  
                return $this->response(400, $response);
            }

            if ($validator->passes()) {
                DB::beginTransaction();

                $mitra = new Mitra;

                $mitra->code = CodeRepo::generateMitra();
                $mitra->name = $request->name;
                $mitra->alamat = $request->address;
                $mitra->created_by = Auth::id();

                $mitra->status = Mitra::STATUS['ACTIVE'];

                if ($mitra->save()) {
                    DB::commit();

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.mitra.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['mitra'] = Mitra::with(['mitra_setting'])->findOrFail($id);

        $data['bulan_list'] = [
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        // Ambil bulan berjalan
        $bulanSekarang = date('n');

        // Cari mitra_setting yang sesuai dengan bulan sekarang
        $mitraSetting = $data['mitra']->mitra_setting->where('bulan', $bulanSekarang)->first();

        // Ambil nama bulan dan data lainnya jika ditemukan
        $data['bulan'] = $mitraSetting ? ($data['bulan_list'][$mitraSetting->bulan] ?? 'Belum Di Setting!') : 'Belum Di Setting!';
        $data['batas_bawah'] = $mitraSetting ? (float) $mitraSetting->batas_bawah : 0;
        $data['batas_atas'] = $mitraSetting ? (float) $mitraSetting->batas_atas : 0;
        $data['saldo'] = $mitraSetting ? (float) $mitraSetting->saldo : 0;

        return view('superuser.master.mitra.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['mitra'] = Mitra::findOrFail($id);

        return view('superuser.master.mitra.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $mitra = Mitra::find($id);

            if ($mitra == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'address' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => $validator->errors()->all(),
                ];
  
                return $this->response(400, $response);
            }

            if ($validator->passes()) {
                DB::beginTransaction();

                $mitra->name = $request->name;
                $mitra->alamat = $request->address;
                $mitra->updated_by = Auth::id();

                if ($mitra->save()) {
                    DB::commit();

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.mitra.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request ,$id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0){
                abort(405);
            }
        }
        
        if ($request->ajax()) {
            $mitra = Mitra::find($id);

            if ($mitra === null) {
                abort(404);
            }

            $mitra->status = Mitra::STATUS['DELETED'];

            if ($mitra->save()) {
                $response['redirect_to'] = route('superuser.master.mitra.index');
                return $this->response(200, $response);
            }
        }
    }

    public function add_customer(Request $request, $id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                abort(405);
            }
        }

        $mitra = Mitra::findOrFail($id);

        $customers = CustomerOtherAddress::where('status', CustomerOtherAddress::STATUS['ACTIVE'])->get();

        $data = [
            'mitra' => $mitra,
            'customers' => $customers,
        ];

        return view('superuser.master.mitra.add_customer', $data);
    }

    public function store_customer(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'customer'   => 'required|array', // Pastikan ini array untuk multiple select
                'customer.*' => 'exists:master_customer_other_addresses,id', // Pastikan tiap item valid
                'mitra_id'   => 'required|exists:master_mitra,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'notification' => [
                        'alert'  => 'block',
                        'type'   => 'alert-danger',
                        'header' => 'Error',
                        'content'=> $validator->errors()->all(),
                    ]
                ], 400);
            }

            DB::beginTransaction(); // Memulai transaksi database

            try {
                $mitra_id = $request->mitra_id;
                $selected_customers = $request->customer;

                // Cek apakah ada customer yang sudah terkait dengan mitra lain
                $already_assigned = MitraDetail::whereIn('customer_other_address_id', $selected_customers)
                    ->where('mitra_id', '!=', $mitra_id) // Pastikan bukan milik mitra yang sedang diedit
                    ->exists();

                if ($already_assigned) {
                    $response['notification'] = [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'header' => 'Warning',
                        'content'=> 'Beberapa Customers yang dipilih sudah diinputkan ke mitra lain.',
                    ];

                    return $this->response(400, $response);
                }

                // Ambil daftar customer lama yang terkait dengan mitra ini
                $existing_customers = MitraDetail::where('mitra_id', $mitra_id)
                    ->pluck('customer_other_address_id')
                    ->toArray();

                // Hapus pelanggan yang tidak dipilih lagi
                MitraDetail::where('mitra_id', $mitra_id)
                    ->whereNotIn('customer_other_address_id', $selected_customers)
                    ->delete();

                // Tambahkan atau perbarui pelanggan yang dipilih
                foreach ($selected_customers as $customer_id) {
                    MitraDetail::updateOrCreate(
                        [
                            'mitra_id'                  => $mitra_id,
                            'customer_other_address_id' => $customer_id,
                        ],
                        [
                            'created_by' => Auth::id(),
                            'status'     => MitraDetail::STATUS['ACTIVE'],
                        ]
                    );
                }

                DB::commit(); // Simpan perubahan jika tidak ada error

                $response['notification'] = [
                    'alert' => 'notify',
                    'type' => 'success',
                    'content' => 'Success',
                ];

                $response['redirect_to'] = route('superuser.master.mitra.index');

                return $this->response(200, $response);

            } catch (\Exception $e) {
                DB::rollBack(); // Batalkan perubahan jika terjadi error

                $response['notification'] = [
                    'alert' => 'block',
                    'type' => 'alert-danger',
                    'header' => 'Error',
                    'content' => "Internal Server Error",
                ];

                return $this->response(400, $response);
            }
        }
    }

    public function getCustomers($id)
    {
        $customers = Mitra::where('master_mitra.id', $id)
            ->leftJoin('master_mitra_detail', 'master_mitra.id', '=', 'master_mitra_detail.mitra_id')
            ->leftJoin('master_customer_other_addresses', 
                \DB::raw('CAST(master_mitra_detail.customer_other_address_id AS CHAR)'),
                '=',
                \DB::raw('CAST(master_customer_other_addresses.id AS CHAR)')
            )
            ->select('master_customer_other_addresses.id', 'master_customer_other_addresses.name', 'master_customer_other_addresses.text_kota')
            ->get();

        return response()->json(['customers' => $customers]);
    }

    public function setting_saldo_import(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $validator = Validator::make($request->all(), [
            'import_file' => 'required|file|mimes:xls,xlsx|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors()->all());
        }

        if ($validator->passes()) {
            $import = new MitraSettingImport();
            Excel::import($import, $request->import_file);

            return redirect()->back()->with(['collect_success' => $import->success, 'collect_error' => $import->error]);
        }
    }

    public function template_setting_mitra()
    {
        $filename = 'master-mitra-setting-template.xlsx';
        return Excel::download(new MitraSettingImportTemplate, $filename);
    }
}
