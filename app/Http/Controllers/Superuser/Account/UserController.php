<?php

namespace App\Http\Controllers\Superuser\Account;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Entities\Setting\Menu;
use App\Entities\Account\User;
use App\Entities\Setting\UserMenu;
use App\Services\UserSyncService;
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
    	$tab = $request->input('tab', 'pending');
    	if (!in_array($tab, ['pending', 'active', 'inactive', 'all'])) {
    		$tab = 'pending';
    	}
    	// pending  = pendaftar SSO yang belum di-approve (is_active=0, sudah ada google_id)
    	// inactive = akun lama yang dinonaktifkan (is_active=0, belum pernah link Google)
    	// active   = is_active=1
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
    				  ->where(function($query3) use($tab){
    				  	if ($tab === 'pending') {
    				  		$query3->where('is_active', 0)->whereNotNull('google_id');
    				  	} elseif ($tab === 'active') {
    				  		$query3->where('is_active', 1);
    				  	} elseif ($tab === 'inactive') {
    				  		$query3->where('is_active', 0)->whereNull('google_id');
    				  	}
    				  })
    				  ->paginate(10)
    				  ->appends(['search' => $search, 'tab' => $tab]);

    	$ids = $table->pluck('id')->toArray();
    	$rolesByUser = [];
    	if (!empty($ids)) {
    		$rows = DB::table('model_has_roles')
    			->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
    			->where('model_has_roles.model_type', 'App\\Entities\\Account\\Superuser')
    			->whereIn('model_has_roles.model_id', $ids)
    			->select('model_has_roles.model_id', 'roles.name')
    			->get();
    		foreach ($rows as $r) {
    			$rolesByUser[$r->model_id][] = $r->name;
    		}
    	}

    	$counts = [
    		'pending' => User::where('is_superuser',0)->where('is_active',0)->whereNotNull('google_id')->count(),
    		'active' => User::where('is_superuser',0)->where('is_active',1)->count(),
    		'inactive' => User::where('is_superuser',0)->where('is_active',0)->whereNull('google_id')->count(),
    	];

    	$data = [
    		'table' => $table,
    		'tab' => $tab,
    		'counts' => $counts,
    		'rolesByUser' => $rolesByUser,
    	];
        return view($this->view."index",$data);
    }

    /**
     * Form approve pendaftar SSO baru (Fase 2).
     * Tanpa checklist: pilih 1 paket jabatan + akses modul,
     * backend yang menulis baris setting_user_menu + role.
     */
    public function approveForm($id)
    {
    	$result = User::where('id',$id)
    					->where('is_superuser',0)
    					->first();

    	if(empty($result)){
    		return redirect()->route('superuser.account.user.index')->with('error','Data tidak ditemukan');
    	}

    	$packages = config('access_packages');
    	$roles = DB::table('roles')->where('guard_name','superuser')->orderBy('name')->get();
    	$modules = ['MIS', 'AO', 'APM', 'TRS', 'DSG'];

    	$data = [
    		'result' => $result,
    		'packages' => $packages,
    		'roles' => $roles,
    		'modules' => $modules,
    	];
        return view($this->view."approve",$data);
    }

    /**
     * Eksekusi approve: aktifkan + set paket + password random (tampil sekali).
     */
    public function approve(Request $request)
    {
        $data_json = [];
        $post = $request->all();
        if($request->method() == "POST"){
        	DB::beginTransaction();
            try{
            	if(empty($post["user_id"])){
            	    $data_json["IsError"]  = TRUE;
            	    $data_json["Message"] = "ID user tidak boleh kosong";
            	    goto ResultDataApprove;
            	}
                if(empty($post["name"])){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Nama user tidak boleh kosong";
                    goto ResultDataApprove;
                }
                if(empty($post["username"])){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Username user tidak boleh kosong";
                    goto ResultDataApprove;
                }
                if(empty($post["jabatan"]) || !array_key_exists($post["jabatan"], config('access_packages'))){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Jabatan (paket) wajib dipilih: SM / ASM";
                    goto ResultDataApprove;
                }

                $user = User::where('id',$post["user_id"])
                				->where('is_superuser',0)
                				->first();

                if(empty($user)){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Data tidak ditemukan";
                    goto ResultDataApprove;
                }

                $check_username = User::where('username',$post["username"])
                						->where('id','!=',$post["user_id"])
                						->first();

                if($check_username){
                	$data_json["IsError"]  = TRUE;
                	$data_json["Message"] = "Duplicat username.Username sudah digunakan";
                	goto ResultDataApprove;
                }

                $package = config('access_packages.'.$post["jabatan"]);

                // Password random 12 karakter bila dikosongkan.
                $plainPassword = trim((string) ($post["password"] ?? ''));
                if ($plainPassword === '') {
                	$plainPassword = \Illuminate\Support\Str::random(12);
                }

                if(strlen($plainPassword) < 8 || strlen($plainPassword) > 16){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Password 8-16 karakter";
                    goto ResultDataApprove;
                }

                $modules = isset($post["modules"]) && is_array($post["modules"])
                	? array_values(array_intersect($post["modules"], ['MIS','AO','APM','TRS','DSG']))
                	: [];

                $user->name = trim(htmlentities($post["name"]));
                $user->username = trim(htmlentities($post["username"]));
                $user->password = bcrypt($plainPassword);
                $user->division = trim(htmlentities($post["division"] ?? $package['division']));
                $user->cabang = trim(htmlentities($post["cabang"] ?? 'Jakarta'));
                $user->modul_akses = json_encode($modules);
                $user->is_active = 1;
                $user->updated_by = Auth::id();
                $user->save();

                // Role jabatan (Spatie).
                $superuser = \App\Entities\Account\Superuser::find($user->id);
                if ($superuser) {
                	$superuser->syncRoles([$post["jabatan"]]);
                }

                // Tulis baris setting_user_menu dari paket (pengganti centangan manual).
                $menuIds = DB::table('setting_menu')
                	->whereIn('route_name', array_keys($package['menus']))
                	->pluck('id','route_name');

                UserMenu::where('user_id', $user->id)->delete();
                foreach ($package['menus'] as $route => $flags) {
                	if (!isset($menuIds[$route])) {
                		continue;
                	}
                	UserMenu::create([
                		'user_id' => $user->id,
                		'menu_id' => $menuIds[$route],
                		'can_read' => $flags[0],
                		'can_create' => $flags[1],
                		'can_update' => $flags[2],
                		'can_delete' => $flags[3],
                		'can_approve' => $flags[4],
                		'can_print' => $flags[5],
                		'created_by' => Auth::id(),
                	]);
                }

                DB::commit();

                // Sinkronkan user yang baru di-approve ke DB trial (pusat user).
                UserSyncService::push($user);

                $data_json["IsError"]  = FALSE;
                $data_json["Message"] = "User berhasil diaktifkan";
                $data_json["Credentials"] = [
                	'username' => $user->username,
                	'password' => $plainPassword,
                ];
                goto ResultDataApprove;

            }catch(\Throwable $e){
            	DB::rollback();
                $data_json["IsError"]  = TRUE;
                $data_json["Message"] = $e->getMessage();
                goto ResultDataApprove;
            }

        }else{
            $data_json["IsError"]  = TRUE;
            $data_json["Message"] = "Invalid method";
            goto ResultDataApprove;
        }
        ResultDataApprove:
        return response()->json($data_json,200);
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
    		'result' => $result,
    		'modules' => ['MIS', 'AO', 'APM', 'TRS', 'DSG'],
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
                	'is_superuser' => 0,
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

                UserSyncService::push(User::find($insert_user->id));

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

                $modules = isset($post["modules"]) && is_array($post["modules"])
                	? array_values(array_intersect($post["modules"], ['MIS','AO','APM','TRS','DSG']))
                	: [];

                $data_user = [
                	'name' => trim(htmlentities($post["name"])),
                	'email' => trim(htmlentities($post["email"])),
                	'username' => trim(htmlentities($post["username"])),
                	'password' => $password,
                	'division' => trim(htmlentities($post["division"])),
                	'modul_akses' => json_encode($modules),
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

                UserSyncService::push(User::find($post["user_id"]));

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
            UserSyncService::push(User::find($post["id"]));
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
            UserSyncService::push(User::find($post["id"]));
            return redirect()->back()->with('success','User berhasil diaktifkan');
            
        }catch(\Throwable $e){
            DB::rollback();
            return redirect()->back()->with('error',$e->getMessage());
        }
    }
}
