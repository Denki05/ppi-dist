<?php

namespace App\Http\Controllers\Superuser\Gudang;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DataTables\Gudang\PurchaseOrderTable;
use App\Entities\Setting\UserMenu;
use App\Entities\Gudang\PurchaseOrder;
use App\Services\PurchaseOrder\PurchaseOrderDetailService;
use App\Services\PurchaseOrder\PurchaseOrderService;
use App\Exports\Gudang\PurchaseOrderDetailImportTemplate;
use App\Exports\Gudang\PurchaseOrderExport;
use App\Imports\Gudang\PurchaseOrderDetailImport;
use Auth;
use Excel;
use PDF;
use Validator;

class PurchaseOrderController extends Controller
{
    protected $service;

    public function __construct(PurchaseOrderService $service){
        $this->service = $service;
        $this->view = "superuser.gudang.purchase_order.";
        $this->route = "superuser.gudang.purchase_order";
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

    public function json(Request $request, PurchaseOrderTable $datatable)
    {
        return $datatable->build();
    }

    public function search_sku(Request $request)
    {
        return ['results' => $this->service->searchSku($request->input('q', ''))];
    }

    public function search_kemasan(Request $request)
    {
        return ['results' => $this->service->searchKemasan($request->input('q', ''))];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($deny = $this->denyUnless('can_read')) return $deny;

        $form = $this->service->formData();

        $data['purchase_order'] = PurchaseOrder::get();
        $data['warehouse'] = $form['warehouse'];
        $data['brands'] = $form['brands'];

        return view($this->view."index", $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if ($deny = $this->denyUnless('can_create')) return $deny;

        $form = $this->service->formData();

        $data['warehouse'] = $form['warehouse'];
        $data['brands'] = $form['brands'];
        $data['sub_type'] = $form['sub_type'];

        return view($this->view."create", $data);
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
            $result = $this->service->createPo($request->all(), Auth::id());

            if ($result["status"] === "validation") {
                return $this->fail($result["errors"]);
            }

            return $this->done(route('superuser.gudang.purchase_order.step', ['id' => $result["po"]->id]));
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
        if ($deny = $this->denyUnless('can_read')) return $deny;

        $data['purchase_order'] = PurchaseOrder::with('brandLokal')->findOrFail($id);

        $tabs = (new PurchaseOrderDetailService)->packTabs();
        $data['pack_tabs'] = $tabs['pack_tabs'];
        $data['fixed_pack_ids'] = $tabs['fixed_pack_ids'];

        return view($this->view."show", $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if ($deny = $this->denyUnless('can_update')) return $deny;

        $form = $this->service->formData();

        $data['purchase_order'] = PurchaseOrder::find($id);
        $data['warehouse'] = $form['warehouse'];
        $data['brands'] = $form['brands'];

        return view($this->view."edit", $data);
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
            $result = $this->service->updatePo($id, $request->all());

            if ($result["status"] === "not_found") {
                abort(404);
            }

            if ($result["status"] === "validation") {
                return $this->fail($result["errors"]);
            }

            return $this->done(route('superuser.gudang.purchase_order.step', ['id' => $result["po"]->id]));
        }
    }

    public function step($id)
    {
        if ($deny = $this->denyUnless('can_read')) return $deny;

        $data['purchase_order'] = PurchaseOrder::with('brandLokal')->findOrFail($id);
        $data['merek'] = \App\Entities\Master\BrandLokal::get();
        $data['warehouses'] = \App\Entities\Master\Warehouse::get();

        if($data['purchase_order']->status == PurchaseOrder::STATUS['ACC'] OR $data['purchase_order']->status == PurchaseOrder::STATUS['DELETED']) {
            return abort(404);
        }

        // Multipage tabs: 1 PO = 1 brand, beda tab beda kemasan (tampilan saja).
        $tabs = (new PurchaseOrderDetailService)->packTabs();
        $data['pack_tabs'] = $tabs['pack_tabs'];
        $data['fixed_pack_ids'] = $tabs['fixed_pack_ids'];
        $data['other_packs'] = $tabs['other_packs'];
        $data['other_pack_ids'] = $tabs['other_pack_ids'];

        return view($this->view."step", $data);
    }

    public function publish(Request $request, $id)
    {
        if ($request->ajax()) {
            $result = $this->service->changeStatus($id, PurchaseOrder::STATUS['ACTIVE'], Auth::id());

            if ($result["status"] === "not_found") {
                abort(404);
            }

            return $this->done(route('superuser.gudang.purchase_order.index'));
        }
    }

    public function unpublish(Request $request, $id)
    {
        if ($request->ajax()) {
            $result = $this->service->changeStatus($id, PurchaseOrder::STATUS['DRAFT'], Auth::id());

            if ($result["status"] === "not_found") {
                abort(404);
            }

            return $this->done(route('superuser.gudang.purchase_order.index'));
        }
    }

    public function save_modify(Request $request, $id, $save_type)
    {
        if ($request->ajax()) {
            $result = $this->service->saveModify($id, $save_type, Auth::id());

            if ($result["status"] === "not_found") {
                abort(404);
            }

            if ($result["status"] === "error") {
                return $this->fail($result["message"]);
            }

            return $this->done(route('superuser.gudang.purchase_order.index'));
        }
    }

    public function acc(Request $request, $id)
    {
        if ($request->ajax()) {
            $result = $this->service->accPo($id, Auth::id());

            if ($result["status"] === "not_found") {
                abort(404);
            }

            if ($result["status"] === "error") {
                return $this->response(500, [
                    'notification' => [
                        'alert' => 'block',
                        'type' => 'alert-danger',
                        'content' => [$result["message"]]
                    ]
                ]);
            }

            return $this->response(200, [
                'redirect_to' => route('superuser.gudang.purchase_order.index')
            ]);
        }
    }

    public function destroy(Request $request, $id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_delete == 0){
                abort(405);
            }
        }

        if ($request->ajax()) {
            $result = $this->service->deletePo($id, Auth::id());

            if ($result["status"] === "not_found") {
                abort(404);
            }

            return $this->response(200, [
                'redirect_to' => route('superuser.gudang.purchase_order.index')
            ]);
        }
    }

    public function print_pdf($id)
    {
        // Access
        if ($deny = $this->denyUnless('can_print')) return $deny;

        $data = $this->service->printData($id);

        $pdf = PDF::loadView($this->view . 'print_pdf', $data)
            ->setPaper('a5', 'landscape');

        return $pdf->stream("PO-{$data['po']->code}.pdf");
    }

    public function import_template()
    {
        $filename = 'purchase-order-detail-import-template.xlsx';
        return Excel::download(new PurchaseOrderDetailImportTemplate, $filename);
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
            $import = new PurchaseOrderDetailImport($id);
            Excel::import($import, $request->import_file);

            return redirect()->back()->with(['collect_success' => $import->success, 'collect_error' => $import->error]);
        }
    }

    public function cancel_acc(Request $request, $id)
    {
        if ($request->ajax()) {
            $result = $this->service->cancelAcc($id, Auth::id());

            if ($result["status"] === "error") {
                return $this->fail($result["message"], 500);
            }

            return $this->response(200, [
                'redirect_to' => route('superuser.gudang.purchase_order.index')
            ]);
        }
    }

    public function export(Request $request)
    {
        $filename = 'Purchase-Order-' . date('d-m-Y_H-i-s') . '.xlsx';
        return Excel::download(
            new PurchaseOrderExport($request->input('start_date'), $request->input('end_date')),
            $filename
        );
    }

    public function send(Request $request, $id)
    {
        if ($request->ajax()) {
            $result = $this->service->sendPo($id, Auth::id());

            if ($result["status"] === "not_found") {
                abort(404);
            }

            return $this->done(route('superuser.gudang.purchase_order.index'));
        }
    }

    public function summary()
    {
        if ($deny = $this->denyUnless('can_read')) return $deny;

        $summary = $this->service->summaryRows();

        return view($this->view."summary", compact('summary'));
    }

    public function summary_json()
    {
        if ($deny = $this->denyUnless('can_read')) return $deny;

        return response()->json([
            'IsError' => false,
            'Data' => $this->service->summaryRows(),
        ]);
    }

    public function cancel_send(Request $request, $id)
    {
        if ($request->ajax()) {
            $result = $this->service->cancelSend($id, Auth::id());

            if ($result["status"] === "not_found") {
                abort(404);
            }

            if ($result["status"] === "has_receiving") {
                return $this->response(400, [
                    'notification' => [
                        'alert'   => 'block',
                        'type'    => 'alert-danger',
                        'content' => $result["message"],
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
                'redirect_to' => route('superuser.gudang.purchase_order.index')
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