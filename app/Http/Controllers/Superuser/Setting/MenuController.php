<?php

namespace App\Http\Controllers\Superuser\Setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Setting\Menu;
use App\Entities\Setting\UserMenu;
use Auth;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(){
        $this->view = "superuser.setting.menu.";
        $this->route = "superuser.setting.menu";
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
    public function index(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access)){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $search = $request->input('search');
        $table = Menu::orderBy('name','ASC')
                     ->where(function($query2) use($search){
                        if(!empty($search)){
                            $query2->where('name','like','%'.$search.'%');
                            $query2->orWhere('route_name','like','%'.$search.'%');
                        }
                     })
                     ->paginate(10);
        $table->withPath('menu?search='.$search);

        $data = [
            'table' => $table
        ];
        return view($this->view."index",$data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data_json = [];
        $post = $request->all();

        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                $data_json["IsError"]  = TRUE;
                $data_json["Message"] = 'Anda tidak punya akses untuk membuka menu terkait';
                goto ResultData;
            }
        }

        if($request->method() == "POST"){
            try{
                if(empty($post["name"])){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Nama menu tidak boleh kosong!";
                    goto ResultData;
                }
                if(empty($post["route_name"])){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Route name menu tidak boleh kosong!";
                    goto ResultData;
                }

                $check_route_name = Menu::where('route_name',$post["route_name"])->first();

                if($check_route_name){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Route name menu sudah tersedia!";
                    goto ResultData;
                }

                $data = [
                    'name' => trim(htmlentities($post["name"])),
                    'route_name' => trim(htmlentities($post["route_name"])),
                    'created_by' => Auth::id(),
                ];

                $insert = Menu::create($data);

                $data_json["IsError"]  = FALSE;
                $data_json["Message"] = "Menu berhasil ditambahkan";
                goto ResultData;

            }catch(\Throwable $e){
                $data_json["IsError"]  = TRUE;
                $data_json["Message"] = $e->getMessage();
                goto ResultData;
            }
        }else{
            $data_json["IsError"]  = TRUE;
            $data_json["Message"] = "Invalid method";
            goto ResultData;
        }
        ResultData:
        return response()->json($data_json,200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        
        $data_json = [];
        $post = $request->all();

        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                $data_json["IsError"]  = TRUE;
                $data_json["Message"] = 'Anda tidak punya akses untuk membuka menu terkait';
                goto ResultData;
            }
        }

        if($request->method() == "POST"){
            try{
                if(empty($post["id"])){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "ID menu tidak boleh kosong!";
                    goto ResultData;
                }
                if(empty($post["name"])){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Nama menu tidak boleh kosong!";
                    goto ResultData;
                }
                if(empty($post["route_name"])){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Route name menu tidak boleh kosong!";
                    goto ResultData;
                }

                $check_route_name = Menu::where('route_name',$post["route_name"])
                                        ->where('id','!=',$post["id"])
                                        ->first();

                if($check_route_name){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Route name menu sudah tersedia!";
                    goto ResultData;
                }

                $data = [
                    'name' => trim(htmlentities($post["name"])),
                    'route_name' => trim(htmlentities($post["route_name"])),
                    'updated_by' => Auth::id(),
                ];

                $update = Menu::where('id',$post["id"])->update($data);

                $data_json["IsError"]  = FALSE;
                $data_json["Message"] = "Menu berhasil diubah";
                goto ResultData;
                
            }catch(\Throwable $e){
                $data_json["IsError"]  = TRUE;
                $data_json["Message"] = $e->getMessage();
                goto ResultData;
            }
        }else{
            $data_json["IsError"]  = TRUE;
            $data_json["Message"] = "Invalid method";
            goto ResultData;
        }
        ResultData:
        return response()->json($data_json,200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        try{
            $request->validate([
                'id' => 'required'
            ]);
            $post = $request->all();
            $result = Menu::where('id',$post["id"])->delete();
            
            return redirect()->back()->with('success','Menu berhasil dihapus');
            
        }catch(\Throwable $e){
            return redirect()->back()->with('danger',$e->getMessage());
        }
    }
}
