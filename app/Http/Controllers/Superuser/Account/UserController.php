<?php

namespace App\Http\Controllers\Superuser\Account;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Entities\Account\User;
use App\Entities\Setting\UserMenu;
use App\Services\MenuAccessService;
use App\Services\ModuleAccessService;
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
    	$tab = $request->input('tab', 'all');
    	if (!in_array($tab, ['pending', 'active', 'inactive', 'all'])) {
    		$tab = 'all';
    	}
    	$type = $request->input('type', 'all');
    	if (!in_array($type, ['all', 'staff', 'superuser'])) {
    		$type = 'all';
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
    				  		$query2->orWhere('jabatan','like','%'.$search.'%');
    				  		$query2->orWhere('username','like','%'.$search.'%');
    				  	}
    				  })
    				  ->where(function($queryType) use($type){
    				  	if ($type === 'staff') {
    				  		$queryType->where('is_superuser', 0);
    				  	} elseif ($type === 'superuser') {
    				  		$queryType->where('is_superuser', 1);
    				  	}
    				  })
    				  ->where(function($query3) use($tab){
    				  	if ($tab === 'pending') {
    				  		$query3->where('is_superuser', 0)
    				  			->where('is_active', 0)
    				  			->whereNotNull('google_id');
    				  	} elseif ($tab === 'active') {
    				  		$query3->where('is_active', 1);
    				  	} elseif ($tab === 'inactive') {
    				  		$query3->where('is_active', 0)
    				  			->where(function($queryInactive) {
    				  				$queryInactive->where('is_superuser', 1)
    				  					->orWhereNull('google_id');
    				  			});
    				  	}
    				  })
    				  ->paginate(10)
    				  ->appends(['search' => $search, 'tab' => $tab, 'type' => $type]);

    	$counts = [
    		'pending' => User::where('is_superuser',0)->where('is_active',0)->whereNotNull('google_id')->count(),
    		'active' => User::where('is_active',1)->count(),
    		'inactive' => User::where('is_active',0)
    			->where(function($query) {
    				$query->where('is_superuser', 1)->orWhereNull('google_id');
    			})->count(),
    		'superuser' => User::where('is_superuser',1)->count(),
    		'staff' => User::where('is_superuser',0)->count(),
    	];

    	$linkedByUser = [];
    	foreach ($table as $row) {
    		$linkedByUser[$row->id] = $this->linkedCustomerCount($row);
    	}

    	$data = [
    		'table' => $table,
    		'tab' => $tab,
    		'type' => $type,
    		'counts' => $counts,
    		'linkedByUser' => $linkedByUser,
    	];
        return view($this->view."index",$data);
    }

    public function approveForm($id)
    {
    	$result = User::where('id',$id)
    					->where('is_superuser',0)
    					->first();

    	if(empty($result)){
    		return redirect()->route('superuser.account.user.index')->with('error','Data tidak ditemukan');
    	}

    	$modules = config('modules.items', []);
    	$currentModules = ModuleAccessService::normalize($result->modul_akses);

    	$data = [
    		'result' => $result,
    		'modules' => $modules,
    		'currentModules' => $currentModules,
    		'divisions' => $this->activeDivisions($result->division),
    		'profiles' => $this->accessProfiles(),
    		'addons' => config('access_matrix.addons', []),
    		'currentProfile' => MenuAccessService::profileKeyForUser($result),
    		'currentAddons' => MenuAccessService::addonKeys($result),
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
                if(empty(trim((string) ($post["jabatan"] ?? '')))){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Jabatan wajib diisi";
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

                if((int) $user->is_active === 1){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "User sudah aktif. Gunakan menu Edit untuk mengubah datanya.";
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

                $plainPassword = trim((string) ($post["password"] ?? ''));
                if ($plainPassword === '') {
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Password wajib diisi sebelum user di-approve";
                    goto ResultDataApprove;
                }

                if(strlen($plainPassword) < 8 || strlen($plainPassword) > 16){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Password 8-16 karakter";
                    goto ResultDataApprove;
                }

                $modules = isset($post["modules"]) && is_array($post["modules"])
                	? array_values(array_intersect($post["modules"], ['MIS','CRM','AO','APM','TRS','DSG']))
                	: [];

                $profile = $this->submittedProfile($post);
                if ($profile === null) {
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Profil akses wajib dipilih";
                    goto ResultDataApprove;
                }
                $addons = $this->submittedAddons($post);

                $user->name = trim(htmlentities($post["name"]));
                $user->username = trim(htmlentities($post["username"]));
                $user->password = bcrypt($plainPassword);
                $user->division = trim(htmlentities($post["division"] ?? 'Marketing'));
                $user->jabatan = trim(htmlentities($post["jabatan"]));
                $user->cabang = trim(htmlentities($post["cabang"] ?? 'Jakarta'));
                $user->modul_akses = json_encode($modules);
                $user->access_profile = $profile;
                $user->access_addons = json_encode($addons);
                $user->can_access_mis = in_array('MIS', $modules, true) ? 1 : 0;
                $user->is_active = 1;
                $user->updated_by = Auth::id();
                $user->save();

                MenuAccessService::sync($user, true);

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
    	$data = [
    		'modules' => config('modules.items', []),
    		'currentModules' => [],
    		'divisions' => $this->activeDivisions(),
    		'profiles' => $this->accessProfiles(),
    		'addons' => config('access_matrix.addons', []),
    		'currentProfile' => null,
    		'currentAddons' => [],
    	];
        return view($this->view."create",$data);
    }
    public function edit(Request $request, $id)
    {
    	$result = User::where('id',$id)
    					->where('is_superuser',0)
    					->first();

    	if(empty($result)){
    		return redirect()->route('superuser.account.user.index')->with('error','Data tidak ditemukan');
    	}

    	$modules = config('modules.items', []);
    	$currentModules = ModuleAccessService::normalize($result->modul_akses);
    	if (empty($currentModules) && ($result->modul_akses === null || $result->modul_akses === '') && (int) $result->is_active === 1) {
    		$currentModules = ModuleAccessService::forUser($result);
    	}

    	$returnTab = $request->input('tab');
    	if (!in_array($returnTab, ['pending', 'active', 'inactive', 'all'], true)) {
    		$returnTab = (int) $result->is_active === 1 ? 'active' : (!empty($result->google_id) ? 'pending' : 'inactive');
    	}

    	$data = [
    		'result' => $result,
    		'modules' => $modules,
    		'currentModules' => $currentModules,
    		'divisions' => $this->activeDivisions($result->division),
    		'profiles' => $this->accessProfiles(),
    		'addons' => config('access_matrix.addons', []),
    		'currentProfile' => MenuAccessService::profileKeyForUser($result),
    		'currentAddons' => MenuAccessService::addonKeys($result),
    		'returnTab' => $returnTab,
    		'linkedCustomers' => $this->linkedCustomerCount($result),
    		'isSuperuser' => false,
    		'updateUrl' => route('superuser.account.user.update'),
    		'backUrl' => route('superuser.account.user.index', ['tab' => $returnTab]),
    		'canEditIdentity' => true,
    		'canEditModules' => true,
    		'canManageRoles' => false,
    		'passwordConfirmName' => 'password_confirm',
    		'formMethod' => 'POST',
    		'formHasFile' => false,
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

                if(empty(trim((string) ($post["jabatan"] ?? '')))){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Jabatan wajib diisi";
                    goto ResultData;
                }

                $profile = $this->submittedProfile($post);
                if ($profile === null) {
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Profil akses wajib dipilih";
                    goto ResultData;
                }
                $addons = $this->submittedAddons($post);

                $check_username = User::where('username',$post["username"])->first();

                if($check_username){
                	$data_json["IsError"]  = TRUE;
                	$data_json["Message"] = "Duplicat username.Username sudah digunakan";
                	goto ResultData;
                }

                $modules = isset($post["modules"]) && is_array($post["modules"])
                	? array_values(array_intersect($post["modules"], ['MIS','CRM','AO','APM','TRS','DSG']))
                	: [];

                $data_user = [
                	'name' => trim(htmlentities($post["name"])),
                	'email' => trim(htmlentities($post["email"])),
                	'username' => trim(htmlentities($post["username"])),
                	'password' => bcrypt(trim(htmlentities($post["password"]))),
                	'division' => trim(htmlentities($post["division"])),
                	'jabatan' => trim(htmlentities($post["jabatan"])),
                	'cabang' => trim(htmlentities($post["cabang"] ?? 'Jakarta')),
                	'modul_akses' => json_encode($modules),
                	'access_profile' => $profile,
                	'access_addons' => json_encode($addons),
                	'can_access_mis' => in_array('MIS', $modules, true) ? 1 : 0,
                	'is_superuser' => 0,
                	'is_active' => 1,
                	'created_by' => Auth::id(),
                ];

                $insert_user = User::create($data_user);

                MenuAccessService::sync($insert_user, true);

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
                if(empty(trim((string) ($post["jabatan"] ?? '')))){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Jabatan wajib diisi";
                    goto ResultData;
                }

                $profile = $this->submittedProfile($post);
                if ($profile === null) {
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Profil akses wajib dipilih";
                    goto ResultData;
                }
                $addons = $this->submittedAddons($post);

                $result = User::where('id',$post["user_id"])
                					->where('is_superuser',0)
                					->first();
                if(empty($result)){
                    $data_json["IsError"]  = TRUE;
                    $data_json["Message"] = "Data user tidak ditemukan";
                    goto ResultData;
                }
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
                	? array_values(array_intersect($post["modules"], ['MIS','CRM','AO','APM','TRS','DSG']))
                	: [];

                $data_user = [
                	'name' => trim(htmlentities($post["name"])),
                	'email' => trim(htmlentities($post["email"])),
                	'username' => trim(htmlentities($post["username"])),
                	'password' => $password,
                	'division' => trim(htmlentities($post["division"])),
                	'jabatan' => trim(htmlentities($post["jabatan"])),
                	'cabang' => trim(htmlentities($post["cabang"] ?? ($result->cabang ?: 'Jakarta'))),
                	'modul_akses' => json_encode($modules),
                	'access_profile' => $profile,
                	'access_addons' => json_encode($addons),
                	'can_access_mis' => in_array('MIS', $modules, true) ? 1 : 0,
                	'updated_by' => Auth::id(),
                ];

                // Email berubah: tautan Google lama dilepas + sesi auto-login dicabut.
                $emailChanged = strtolower(trim((string) $post["email"])) !== strtolower(trim((string) $result->email));

                if ($emailChanged) {
                    $data_user['email_verified_at'] = null;
                    $data_user['google_id'] = null;
                    $data_user['google_linked_at'] = null;
                    $data_user['remember_token'] = null;
                }

                if (!empty($post["password"])) {
                    $data_user['remember_token'] = null;
                }

                $update_user = User::where('id',$post["user_id"])->update($data_user);

                MenuAccessService::sync(User::find($post["user_id"]), true);

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
            $result = User::where('id',$post["id"])->where('is_superuser',0)->first();

            if(empty($result)){
                throw new \RuntimeException('Data user tidak ditemukan');
            }

            User::where('id',$post["id"])->update([
            	'is_active' => 0,
            	'remember_token' => null,
            	'deleted_by' => Auth::id()
            ]);
            
            DB::commit();
            UserSyncService::push(User::find($post["id"]));
            return redirect()->back()->with('success','User berhasil dinonaktifkan. Data dan tautan customer tetap aman.');
            
        }catch(\Throwable $e){
            DB::rollback();
            return redirect()->back()->with('error',$e->getMessage());
        }
    }

    public function forceDelete(Request $request)
    {
        $request->validate([
            'id' => 'required'
        ]);

        DB::beginTransaction();
        try{
            $user = User::where('id',$request->input('id'))->where('is_superuser',0)->first();

            if(empty($user)){
                throw new \RuntimeException('Data user tidak ditemukan');
            }

            if((int) $user->is_active === 1){
                throw new \RuntimeException('Nonaktifkan user terlebih dahulu sebelum menghapus permanen.');
            }

            $linkedCustomers = $this->linkedCustomerCount($user);
            if($linkedCustomers > 0){
                throw new \RuntimeException('User ini tertaut dengan '.$linkedCustomers.' customer. Gunakan Nonaktifkan agar data customer tetap aman.');
            }

            $user->is_active = 0;
            UserSyncService::push($user);

            UserMenu::where('user_id',$user->id)->delete();
            DB::table('model_has_roles')
                ->where('model_id',$user->id)
                ->whereIn('model_type', ['App\\Entities\\Account\\Superuser', 'App\\Entities\\Account\\User'])
                ->delete();
            DB::table('superuser_login_tokens')->where('superuser_id',$user->id)->delete();
            $user->delete();

            DB::commit();
            return redirect()->back()->with('success','User berhasil dihapus permanen.');
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
            $result = User::where('id',$post["id"])->where('is_superuser',0)->first();

            if(empty($result)){
                throw new \RuntimeException('Data user tidak ditemukan');
            }

            User::where('id',$post["id"])->update([
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

    protected function accessProfiles()
    {
        $profiles = config('access_matrix.profiles', []);
        $labels = config('access_matrix.profile_labels', []);
        $result = [];

        foreach ($profiles as $key => $permissions) {
            $result[$key] = $labels[$key] ?? $key;
        }

        return $result;
    }

    protected function submittedProfile($post)
    {
        $profile = trim((string) ($post['access_profile'] ?? ''));

        return isset(config('access_matrix.profiles', [])[$profile]) ? $profile : null;
    }

    protected function submittedAddons($post)
    {
        $raw = $post['access_addons'] ?? [];

        if (!is_array($raw)) {
            $raw = [];
        }

        return array_values(array_intersect(
            array_map('strval', $raw),
            array_keys(config('access_matrix.addons', []))
        ));
    }

    protected function activeDivisions($current = null)
    {
        $divisions = config('access_matrix.divisions', []);

        $used = User::where('is_superuser', 0)
            ->where('is_active', 1)
            ->whereNotNull('division')
            ->where('division', '<>', '')
            ->distinct()
            ->pluck('division')
            ->all();

        $divisions = array_merge($divisions, $used);

        if ($current && !in_array($current, $divisions, true)) {
            $divisions[] = $current;
        }

        $divisions = array_values(array_unique(array_filter($divisions)));
        sort($divisions);

        return !empty($divisions) ? $divisions : ['Marketing'];
    }

    protected function linkedCustomerCount($user)
    {
        $values = array_values(array_filter(array_unique([
            trim((string) ($user->username ?? '')),
            trim((string) ($user->name ?? '')),
        ])));

        if (empty($values)) {
            return 0;
        }

        return DB::table('master_customers')
            ->where(function($query) use ($values) {
                foreach ($values as $value) {
                    $query->orWhere('sm', $value)
                        ->orWhere('asm', $value)
                        ->orWhere('pic', $value);
                }
            })
            ->count();
    }
}
