<?php

namespace App\Http\Controllers\Superuser\Account;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Entities\Setting\Menu;
use App\Entities\Account\User;
use App\Entities\Setting\UserMenu;
use DB;
use Auth;

class UserController extends Controller
{
	public function __construct(){
		$this->view = "superuser.account.user.";
	}
    public function index(Request $request)
    {
    	$search = $request->input('search');
    	$table = User::orderBy('id','DESC')
    				  ->where(function($query2) use($search){
    				  	if(!empty($search)){
    				  		$query2->where('name','like','%'.$search.'%');
    				  		$query2->orWhere('email','like','%'.$search.'%');
    				  		$query2->orWhere('division','like','%'.$search.'%');
    				  		$query2->orWhere('username','like','%'.$search.'%');
    				  	}
    				  })
    				  ->where('is_superuser',0)
    				  ->paginate(10);
    	$data = [
    		'table' => $table
    	];
        return view($this->view."index",$data);
    }
    public function create()
    {
    	$menu = Menu::orderBy('name','ASC')->get();

    	$data = [
    		'menu' => $menu
    	];
        return view($this->view."create",$data);
    }
    public function edit($id)
    {
    	$result = User::where('id',$id)
    					->where('is_superuser',0)
    					->first();

    	if(empty($result)){
    		return redirect()->route('superuser.account.user.index')->with('error','Data tidak ditemukan');
    	}

    	$menu = Menu::orderBy('name','ASC')->get();

    	$data = [
    		'menu' => $menu,
    		'result' => $result
    	];
        return view($this->view."edit",$data);
    }
    public function store(Request $request)
    {
        $data_json = [];
        $post = $request->all();
        if($request->method() == "POST"){
        	DB::beginTransaction();
            try{
                if(empty($post["name"])){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Nama user tidak boleh kosong";
                    goto ResultData;
                }

                if(empty($post["division"])){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Divisi user tidak boleh kosong";
                    goto ResultData;
                }

                if(empty($post["email"])){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Email user tidak boleh kosong";
                    goto ResultData;
                }
                if(empty($post["username"])){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Username user tidak boleh kosong";
                    goto ResultData;
                }
                if(empty($post["password"])){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Password user tidak boleh kosong";
                    goto ResultData;
                }
                if(empty($post["password_confirm"])){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Password Confirm user tidak boleh kosong";
                    goto ResultData;
                }

                if($post["password"] !== $post["password_confirm"]){
                	$data_json["IsError"]  = TRUE;
                	$data_json["Message"] = "Password tidak sama";
                	goto ResultData;
                }

                $length_psw = $post["password"];

                if(strlen($length_psw) < 8){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Password minimal 8 karakter";
                    goto ResultData;
                }

                if(strlen($length_psw) > 16){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Password maksimal 16 karakter";
                    goto ResultData;
                }

                $check_email = User::where('email',$post["email"])->first();

                if($check_email){
                	$data_json["IsError"]  = TRUE;
                	$data_json["Message"] = "Duplicat email.Email sudah digunakan";
                	goto ResultData;
                }

                $check_username = User::where('username',$post["username"])->first();

                if($check_username){
                	$data_json["IsError"]  = TRUE;
                	$data_json["Message"] = "Duplicat username.Username sudah digunakan";
                	goto ResultData;
                }

                $data_user = [
                	'name' => trim(htmlentities($post["name"])),
                	'email' => trim(htmlentities($post["email"])),
                	'username' => trim(htmlentities($post["username"])),
                	'password' => bcrypt(trim(htmlentities($post["password"]))),
                	'division' => trim(htmlentities($post["division"])),
                	'is_superuser' =>0,
                	'is_active' => 1,
                	'created_by' => Auth::id(),
                ];

                $insert_user = User::create($data_user);

                foreach ($post["repeater"] as $key => $value) {
                		$insert_user_menu = UserMenu::create([
                			'user_id' => $insert_user->id,
                			'menu_id' => $value["menu_id"],
                			'can_read' => (empty($value["can_read"])) ? 0 : 1,
                			'can_create' => (empty($value["can_create"])) ? 0 : 1,
                			'can_update' => (empty($value["can_update"])) ? 0 : 1,
                			'can_delete' => (empty($value["can_delete"])) ? 0 : 1,
                			'can_print' => (empty($value["can_print"])) ? 0 : 1,
                			'can_approve' => (empty($value["can_approve"])) ? 0 : 1,
                			'created_by' => Auth::id(),
                		]);
                }

                DB::commit();

                $data_json["IsError"]  = FALSE;
                $data_json["Message"] = "User berhasil ditambahkan";
                goto ResultData;

            }catch(\Throwable $e){
            	DB::rollback();
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
    public function update(Request $request)
    {
        $data_json = [];
        $post = $request->all();
        if($request->method() == "POST"){
        	DB::beginTransaction();
            try{
            	if(empty($post["user_id"])){
            	    $data_json["IsError"]  = TRUE;
            	    $data_json["Message"] = "ID user tidak boleh kosong";
            	    goto ResultData;
            	}
                if(empty($post["name"])){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Nama user tidak boleh kosong";
                    goto ResultData;
                }

                if(empty($post["division"])){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Divisi user tidak boleh kosong";
                    goto ResultData;
                }

                if(empty($post["email"])){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Email user tidak boleh kosong";
                    goto ResultData;
                }
                if(empty($post["username"])){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Username user tidak boleh kosong";
                    goto ResultData;
                }
                $result = User::where('id',$post["user_id"])
                					->first();
                $password = $result->password;
                if(!empty($post["password"])){
                	if(empty($post["password_confirm"])){
                	    $data_json["IsError"]  = TRUE;
                	    $data_json["Message"] = "Password Confirm user tidak boleh kosong";
                	    goto ResultData;
                	}

                	if($post["password"] !== $post["password_confirm"]){
                		$data_json["IsError"]  = TRUE;
                		$data_json["Message"] = "Password tidak sama";
                		goto ResultData;
                	}

                    $length_psw = $post["password"];

                    if(strlen($length_psw) < 8){
                        $data_json["IsError"]  = TRUE;
                        $data_json["Message"] = "Password minimal 8 karakter";
                        goto ResultData;
                    }

                    if(strlen($length_psw) > 16){
                        $data_json["IsError"]  = TRUE;
                        $data_json["Message"] = "Password maksimal 16 karakter";
                        goto ResultData;
                    }

                	$password = bcrypt($post["password"]);

                }

               
                $check_email = User::where('email',$post["email"])
                					->where('id','!=',$post["user_id"])
                					->first();

                if($check_email){
                	$data_json["IsError"]  = TRUE;
                	$data_json["Message"] = "Duplicat email.Email sudah digunakan";
                	goto ResultData;
                }

                $check_username = User::where('username',$post["username"])
                						->where('id','!=',$post["user_id"])
                						->first();

                if($check_username){
                	$data_json["IsError"]  = TRUE;
                	$data_json["Message"] = "Duplicat username.Username sudah digunakan";
                	goto ResultData;
                }

                $data_user = [
                	'name' => trim(htmlentities($post["name"])),
                	'email' => trim(htmlentities($post["email"])),
                	'username' => trim(htmlentities($post["username"])),
                	'password' => $password,
                	'division' => trim(htmlentities($post["division"])),
                	'updated_by' => Auth::id(),
                ];

                $update_user = User::where('id',$post["user_id"])->update($data_user);

                foreach ($post["repeater"] as $key => $value) {
                		$update_user_menu = UserMenu::updateOrCreate([
                			'user_id' => $post["user_id"],
                			'menu_id' => $value["menu_id"]
                		],[
                			'user_id' => $post["user_id"],
                			'menu_id' => $value["menu_id"],
                			'can_read' => (empty($value["can_read"])) ? 0 : 1,
                			'can_create' => (empty($value["can_create"])) ? 0 : 1,
                			'can_update' => (empty($value["can_update"])) ? 0 : 1,
                			'can_delete' => (empty($value["can_delete"])) ? 0 : 1,
                			'can_print' => (empty($value["can_print"])) ? 0 : 1,
                			'can_approve' => (empty($value["can_approve"])) ? 0 : 1,
                			'created_by' => Auth::id(),
                			'updated_by' => Auth::id(),
                		]);
                }

                DB::commit();

                $data_json["IsError"]  = FALSE;
                $data_json["Message"] = "User berhasil diubah";
                goto ResultData;

            }catch(\Throwable $e){
            	DB::rollback();
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

    public function destroy(Request $request)
    {
        DB::beginTransaction();
        try{
            $request->validate([
                'id' => 'required'
            ]);
            $post = $request->all();
            $result = User::where('id',$post["id"])->first();

            $update = User::where('id',$post["id"])->update([
            	'is_active' => 0,
            	'deleted_by' => Auth::id()
            ]);
            
            DB::commit();
            return redirect()->back()->with('success','User berhasil dinon-aktifkan');
            
        }catch(\Throwable $e){
            DB::rollback();
            return redirect()->back()->with('error',$e->getMessage());
        }
    }
    public function restore(Request $request)
    {
        DB::beginTransaction();
        try{
            $request->validate([
                'id' => 'required'
            ]);
            $post = $request->all();
            $result = User::where('id',$post["id"])->first();

            $update = User::where('id',$post["id"])->update([
            	'is_active' => 1,
            	'updated_by' => Auth::id()
            ]);
            
            DB::commit();
            return redirect()->back()->with('success','User berhasil diaktifkan');
            
        }catch(\Throwable $e){
            DB::rollback();
            return redirect()->back()->with('error',$e->getMessage());
        }
    }
}
