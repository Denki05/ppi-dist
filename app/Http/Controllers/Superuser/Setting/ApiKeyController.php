<?php

namespace App\Http\Controllers\Superuser\Setting;

use App\Http\Controllers\Controller;
use App\Entities\Setting\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Entities\Setting\Menu;
use App\Entities\Setting\UserMenu;
use Auth;

class ApiKeyController extends Controller
{
    public function __construct(){
        $this->view = "superuser.setting.api_keys.";
        $this->route = "superuser.setting.api_keys";
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

    public function index()
    {
        // $data['keys'] = ApiKey::get();

        $data = [
            'keys'  => ApiKey::all(),
        ];
        
        return view($this->view."index", $data);
    }

    public function create()
    {
        return view($this->view."create");
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
        ]);

        ApiKey::create([
            'name' => $request->name,
            'key' => Str::uuid(),
            'is_active' => true,
        ]);

        return redirect()->route('superuser.setting.api_keys.index')->with('success', 'API key created.');
    }

    public function edit(ApiKey $api_key)
    {
        return view('admin.api_keys.edit', compact('api_key'));
    }

    public function update(Request $request, ApiKey $api_key)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $api_key->update($request->only('name', 'is_active'));

        return redirect()->route('admin.api_keys.index')->with('success', 'API key updated.');
    }

    public function destroy($id)
    {
        $api_key = ApiKey::findOrFail($id);
        $api_key->delete();

        return redirect()->route('superuser.setting.api_keys.index')->with('success', 'API key deleted successfully.');
    }
}