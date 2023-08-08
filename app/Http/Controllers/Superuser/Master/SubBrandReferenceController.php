<?php

namespace App\Http\Controllers\Superuser\Master;

use App\DataTables\Master\SubBrandReferenceTable;
use App\Entities\Master\SubBrandReference;
use App\Entities\Master\BrandReference;
use App\Entities\Master\CustomerOtherAddress;
use App\Exports\Master\SubBrandReferenceExport;
use App\Exports\Master\SubBrandReferenceImportTemplate;
use App\Http\Controllers\Controller;
use App\Imports\Master\SubBrandReferenceImport;
use App\Repositories\CodeRepo;
use App\Repositories\MasterRepo;
use DB;
use Excel;
use Illuminate\Http\Request;
use App\Entities\Setting\UserMenu;
use Validator;
use Auth;
use App\Helper\UploadMedia;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

class SubBrandReferenceController extends Controller
{
    public function __construct(){
        $this->route = "superuser.master.sub_brand_reference";
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
    public function json(Request $request, SubBrandReferenceTable $datatable)
    {
        return $datatable->build($request);
    }

    public function index()
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access)){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $parfume_searah = SubBrandReference::all();
        $brand = BrandReference::first();

        $data = [
            'parfume_searah' => $parfume_searah,
            'brand' => $brand,
        ];

        return view('superuser.master.sub_brand_reference.index', $data);
    }

    public function create()
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_create == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }
        $data['brand_references'] = MasterRepo::brand_references();
        return view('superuser.master.sub_brand_reference.create', $data);
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                // 'code' => 'required|string|unique:master_sub_brand_references,code',
                'brand_reference' => 'required|integer',
                'name' => 'required|string',
                'link' => 'nullable|string',
                'description' => 'nullable|string',
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

                $sub_brand_reference = new SubBrandReference;

                $sub_brand_reference->brand_reference_id = $request->brand_reference;
                $sub_brand_reference->code = CodeRepo::generateSubBrandReference();
                $sub_brand_reference->name = $request->name;
                $sub_brand_reference->link = $request->link;
                $sub_brand_reference->description = $request->description;

                if (!empty($request->file('image_botol'))) {
                    $sub_brand_reference->image_botol = UploadMedia::image($request->file('image_botol'), SubBrandReference::$directory_image);
                }

                if (!empty($request->file('image_table_botol'))) {
                    $sub_brand_reference->image_table_botol = UploadMedia::image($request->file('image_table_botol'), SubBrandReference::$directory_image);
                }
                $sub_brand_reference->status = SubBrandReference::STATUS['ACTIVE'];

                if ($sub_brand_reference->save()) {
                    DB::commit();

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.sub_brand_reference.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function show($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_read == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['sub_brand_reference'] = SubBrandReference::findOrFail($id);

        return view('superuser.master.sub_brand_reference.show', $data);
    }

    public function edit($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['brand_references'] = MasterRepo::brand_references();
        $data['sub_brand_reference'] = SubBrandReference::findOrFail($id);

        return view('superuser.master.sub_brand_reference.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $sub_brand_reference = SubBrandReference::find($id);

            if ($sub_brand_reference == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                // 'code' => 'required|string|unique:master_sub_brand_references,code,' . $sub_brand_reference->id,
                'brand_reference' => 'required|integer',
                'name' => 'required|string',
                'link' => 'nullable|string',
                'description' => 'nullable|string',
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

                $sub_brand_reference->brand_reference_id = $request->brand_reference;
                // $sub_brand_reference->code = $request->code;
                $sub_brand_reference->name = $request->name;
                $sub_brand_reference->link = $request->link;
                $sub_brand_reference->description = $request->description;

                $upload_image = $request->upload_image;
                
                $dom = new \DomDocument();
                $dom->loadHtml( mb_convert_encoding($upload_image, 'HTML-ENTITIES', "UTF-8"), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

                $images = $dom->getElementsByTagName('img');

                foreach($images as $img){
                    $src = $img->getAttribute('src');
                    
                    // if the img source is 'data-url'
                    if(preg_match('/data:image/', $src)){
                        
                        // get the mimetype
                        preg_match('/data:image\/(?<mime>.*?)\;/', $src, $groups);
                        $mimetype = $groups['mime'];
                        
                        // Generating a random filename
                        $filename = $sub_brand_reference->id;
                        $filepath = "/images/master/searah/$filename.$mimetype";
            
                        // @see http://image.intervention.io/api/
                        $image = Image::make($src)
                          // resize if required
                          /* ->resize(300, 200) */
                          ->encode($mimetype, 100) 	// encode file to the specified mimetype
                          ->save(public_path($filepath));
                        
                        $new_src = asset($filepath);
                        $img->removeAttribute('src');
                        $img->setAttribute('src', $new_src);
                    } // <!--endif
                } // <!--endforeach

                $sub_brand_reference->image_botol = $filename.'.'.$mimetype;

                if ($sub_brand_reference->save()) {
                    DB::commit();

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.sub_brand_reference.index');

                    return $this->response(200, $response);
                }
            }
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
            $sub_brand_reference = SubBrandReference::find($id);

            if ($sub_brand_reference === null) {
                abort(404);
            }

            $sub_brand_reference->status = SubBrandReference::STATUS['DELETED'];

            if ($sub_brand_reference->save()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }

    public function import_template()
    {
        $filename = 'master-sub-brand-reference-import-template.xlsx';
        return Excel::download(new SubBrandReferenceImportTemplate, $filename);
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
            $import = new SubBrandReferenceImport($id);
            Excel::import($import, $request->import_file);
            
            if($import->error) {
                return redirect()->back()->withErrors($import->error);
            }
            
            return redirect()->back()->with(['message' => 'Import success']);
        }
    }

    public function export()
    {
        $filename = 'master-sub-brand-reference-' . date('d-m-Y_H-i-s') . '.xlsx';
        return Excel::download(new SubBrandReferenceExport, $filename);
    }

    public function edit_image($id)
    {
        // Access
        if(Auth::user()->is_superuser == 0){
            if(empty($this->access) || empty($this->access->user) || $this->access->can_update == 0){
                return redirect()->route('superuser.index')->with('error','Anda tidak punya akses untuk membuka menu terkait');
            }
        }

        $data['searah'] = SubBrandReference::findOrFail($id);

        return view('superuser.master.sub_brand_reference.upload', $data);
    }

    public function update_image(Request $request, $id)
    {
        if ($request->ajax()) {
            $sub_brand_reference = SubBrandReference::find($id);

            if ($sub_brand_reference == null) {
                abort(404);
            }

            $validator = Validator::make($request->all(), [
                
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

                $upload_image = $request->upload_image;
                
                $dom = new \DomDocument();
                $dom->loadHtml( mb_convert_encoding($upload_image, 'HTML-ENTITIES', "UTF-8"), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

                $images = $dom->getElementsByTagName('img');

                foreach($images as $img){
                    $src = $img->getAttribute('src');
                    
                    // if the img source is 'data-url'
                    if(preg_match('/data:image/', $src)){
                        
                        // get the mimetype
                        preg_match('/data:image\/(?<mime>.*?)\;/', $src, $groups);
                        $mimetype = $groups['mime'];
                        
                        // Generating a random filename
                        $filename = $sub_brand_reference->id;
                        $filepath = "/images/master/searah/$filename.$mimetype";
            
                        // @see http://image.intervention.io/api/
                        $image = Image::make($src)
                          // resize if required
                          /* ->resize(300, 200) */
                          ->encode($mimetype, 100) 	// encode file to the specified mimetype
                          ->save(public_path($filepath));
                        
                        $new_src = asset($filepath);
                        $img->removeAttribute('src');
                        $img->setAttribute('src', $new_src);
                    } // <!--endif
                } // <!--endforeach

                $sub_brand_reference->image_botol = $filename.'.'.$mimetype;

                if ($sub_brand_reference->save()) {
                    DB::commit();

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.master.sub_brand_reference.index');

                    return $this->response(200, $response);
                }
            }
        }
    }
}
