<?php

namespace App\Http\Controllers\Superuser\Gudang;

use App\DataTables\Gudang\ReceivingTable;
use App\Entities\Gudang\Receiving;
use App\Exports\Gudang\ReceivingDetailImportTemplate;
use App\Exports\Gudang\ReceivingExport;
use App\Imports\Gudang\ReceivingDetailImport;
use App\Services\Receiving\ReceivingService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Entities\Setting\UserMenu;
use Auth;
use Excel;
use Validator;

class ReceivingController extends Controller
{
    protected $service;

    public function __construct(ReceivingService $service){
        $this->service = $service;
        $this->view = "superuser.gudang.receiving.";
        $this->route = "superuser.gudang.receiving";
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

    public function json(Request $request, ReceivingTable $datatable)
    {
        return $datatable->build();
    }

    public function index()
    {
        if ($deny = $this->denyUnless('can_read')) return $deny;

        $form = $this->service->formData();

        return view($this->view."index", ['warehouses' => $form['warehouses']]);
    }

    public function create()
    {
        if ($deny = $this->denyUnless('can_create')) return $deny;

        $form = $this->service->formData();

        $data['warehouses'] = $form['warehouses'];

        return view($this->view."create", $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $result = $this->service->createReceiving($request->all(), Auth::id());

            if ($result["status"] === "validation") {
                return $this->fail($result["errors"]);
            }

            return $this->done(route('superuser.gudang.receiving.step', ['id' => $result["receiving"]->id]));
        }
    }

    public function edit($id)
    {
        if ($deny = $this->denyUnless('can_update')) return $deny;

        $data['receiving'] = Receiving::find($id);

        return view($this->view."edit", $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $result = $this->service->updateReceiving($id, $request->all());

            if ($result["status"] === "not_found") {
                abort(404);
            }

            if ($result["status"] === "validation") {
                return $this->fail($result["errors"]);
            }

            return $this->done(route('superuser.gudang.receiving.step', ['id' => $result["receiving"]->id]));
        }
    }

    public function step($id)
    {
        if ($deny = $this->denyUnless('can_read')) return $deny;

        $data['receiving'] = Receiving::findOrFail($id);

        return view('superuser.gudang.receiving.step', $data);
    }

    public function publish(Request $request, $id)
    {
        $result = $this->service->publishReceiving($id);

        if (!empty($result["error"])) {
            return redirect()->back()->with('error', $result["error"]);
        }

        return redirect()
            ->route('superuser.gudang.receiving.step', $result["step_id"])
            ->with('message', $result["message"]);
    }

    public function acc_ri(Request $request, $id)
    {
        $result = $this->service->accReceiving($id, Auth::id());

        if ($result["status"] === "not_ready") {
            return $this->response(400, [
                'notification' => [
                    'alert'   => 'block',
                    'type'    => 'alert-danger',
                    'content' => $result["message"]
                ]
            ]);
        }

        if ($result["status"] === "error") {
            return $this->response(500, [
                'notification' => [
                    'alert'   => 'block',
                    'type'    => 'alert-danger',
                    'content' => $result["message"]
                ]
            ]);
        }

        return $this->response(200, [
            'notification' => [
                'alert'   => 'notify',
                'type'    => 'success',
                'content' => $result["message"]
            ],
            'redirect_to' => route('superuser.gudang.receiving.index')
        ]);
    }

    public function show($id)
    {
        if ($deny = $this->denyUnless('can_read')) return $deny;

        $data['receiving'] = Receiving::findOrFail($id);

        return view('superuser.gudang.receiving.show', $data);
    }

    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            if(Auth::user()->is_superuser == 0){
                if(empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0){
                    abort(405);
                }
            }

            $result = $this->service->deleteReceiving($id);

            if ($result["status"] === "not_found") {
                abort(404);
            }

            $response['redirect_to'] = route('superuser.gudang.receiving.index');
            return $this->response(200, $response);
        }
    }

    public function import_template()
    {
        $filename = 'receiving-detail-import-template.xlsx';
        return Excel::download(new ReceivingDetailImportTemplate, $filename);
    }

    public function import(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'import_file' => 'required|file|mimes:xls,xlsx|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors()->all());
        }

        if ($validator->passes()) {
            $import = new ReceivingDetailImport($id);
            Excel::import($import, $request->import_file);

            return redirect()->back()->with(['collect_success' => $import->success, 'collect_error' => $import->error]);
        }
    }

    public function export(Request $request)
    {
        $filename = 'Receiving-' . date('d-m-Y_H-i-s') . '.xlsx';
        return Excel::download(
            new ReceivingExport($request->input('start_date'), $request->input('end_date')),
            $filename
        );
    }

    public function rollback(Request $request, $id)
    {
        if ($request->ajax()) {
            if(Auth::user()->is_superuser == 0){
                if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                    abort(405);
                }
            }

            $result = $this->service->rollbackToQc($id);

            if ($result["status"] !== "ok") {
                return $this->response(400, [
                    'notification' => [
                        'alert'   => 'block',
                        'type'    => 'alert-danger',
                        'content' => $result["message"]
                    ]
                ]);
            }

            return $this->response(200, [
                'notification' => [
                    'alert'   => 'notify',
                    'type'    => 'success',
                    'content' => $result["message"]
                ],
                'redirect_to' => route('superuser.gudang.receiving.step', $id)
            ]);
        }
    }

    public function cancel(Request $request, $id)
    {
        if ($request->ajax()) {
            if(Auth::user()->is_superuser == 0){
                if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                    abort(405);
                }
            }

            $result = $this->service->cancelReceiving($id);

            if ($result["status"] === "has_qc") {
                return $this->response(400, [
                    'notification' => [
                        'alert'   => 'block',
                        'type'    => 'alert-danger',
                        'content' => $result["message"]
                    ]
                ]);
            }

            if ($result["status"] === "error") {
                return $this->response(500, [
                    'notification' => [
                        'alert'   => 'notify',
                        'type'    => 'error',
                        'content' => $result["message"]
                    ]
                ]);
            }

            return $this->response(200, [
                'notification' => [
                    'alert'   => 'notify',
                    'type'    => 'success',
                    'content' => $result["message"]
                ],
                'redirect_to' => route('superuser.gudang.receiving.index')
            ]);
        }
    }

    /**
     * Cek hak akses menu. Return redirect bila ditolak, null bila boleh lanjut.
     */
    private function denyUnless($permission)
    {
        if (Auth::user()->is_superuser == 0) {
            if (empty($this->access) || empty($this->access->user) || $this->access->{$permission} == 0) {
                return redirect()->route('superuser.index')->with('error', 'Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        return null;
    }

    /**
     * Response gagal standar (blok merah).
     */
    private function fail($content, $code = 400)
    {
        return $this->response($code, [
            'notification' => [
                'alert' => 'block',
                'type' => 'alert-danger',
                'header' => 'Error',
                'content' => $content,
            ],
        ]);
    }

    /**
     * Response sukses standar (notifikasi + redirect).
     */
    private function done($redirect_to, $content = 'Success')
    {
        return $this->response(200, [
            'notification' => [
                'alert' => 'notify',
                'type' => 'success',
                'content' => $content,
            ],
            'redirect_to' => $redirect_to,
        ]);
    }
}
