<?php

namespace App\Http\Controllers\Superuser\Master;

use Illuminate\Http\Request;
use App\Entities\Master\BrandLokal;
use App\DataTables\Master\BrandLokalTable;
use App\Http\Controllers\Controller;
use App\Entities\Setting\UserMenu;
use Validator;
use Auth;
use DB;

class BrandLokalController extends Controller
{
    public function __construct(){
        $this->route = "superuser.master.brand_lokal";
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

    public function json(Request $request, BrandLokalTable $datatable)
    {
        return $datatable->build();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access)){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        return view('superuser.master.brand_lokal.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        return view('superuser.master.brand_lokal.create');
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
                // 'code' => 'required|string|unique:master_brand_references,code',
                'brand_name' => 'required|string',
                'category' => 'required|string',
                'type' => 'required|string',
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

                $brand_lokal = new BrandLokal;

                $brand_lokal->brand_name = $request->brand_name;
                $brand_lokal->category = $request->category;
                $brand_lokal->type = $request->type;
                $brand_lokal->packaging = $request->packaging;
                $brand_lokal->status = BrandLokal::STATUS['ACTIVE'];

                if ($brand_lokal->save()) {
                    DB::commit();

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.brand_lokal.index');

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
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['brand_lokal'] = BrandLokal::findOrFail($id);

        return view('superuser.master.brand_lokal.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['brand_lokal'] = BrandLokal::findOrFail($id);

        return view('superuser.master.brand_lokal.edit', $data);
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
            $brand_lokal = BrandLokal::find($id);

            if ($brand_lokal == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                // 'code' => 'required|string|unique:master_brand_references,code,' . $brand_reference->id,
                'brand_name' => 'required|string',
                'category' => 'required|string',
                'type' => 'required|string',
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

                $brand_lokal->brand_name = $request->brand_name;
                $brand_lokal->category = $request->category;
                $brand_lokal->type = $request->type;
                $brand_lokal->packaging = $request->packaging;

                if ($brand_lokal->save()) {
                    DB::commit();

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.brand_lokal.index');

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
    public function destroy($id)
    {
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0){
                abort(405);
            }
        }
        if ($request->ajax()) {
            $brand_lokal = BrandLokal::find($id);

            if ($brand_lokal === null) {
                abort(404);
            }

            $brand_lokal->status = BrandLokal::STATUS['DELETED'];

            if ($brand_lokal->save()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }
}
