<?php

namespace App\Http\Controllers\Superuser;

use App\DataTables\BoilerplateTable;
use App\Entities\Boilerplate;
use App\Entities\BoilerplateImage;
use App\Exports\BoilerplateExport;
use App\Exports\BoilerplateImportTemplate;
use App\Helper\UploadMedia;
use App\Http\Controllers\Controller;
use App\Imports\BoilerplateImport;
use Illuminate\Http\Request;
use Excel;
use Validator;

class BoilerplateController extends Controller
{
    public function json(Request $request, BoilerplateTable $datatable)
    {
        return $datatable->with('show', $request->show)->build();
    }

    public function index()
    {
        return view('superuser.boilerplate.index');
    }

    public function create()
    {
        return view('superuser.boilerplate.create');
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
              'text'             => 'required|string',
              'textarea'         => 'required|string',
              'select2'          => 'required|string|in:html,css,javascript,php',
              'select2-multiple' => 'required|array',
              'image'            => 'required|image|mimes:jpeg,png,jpg|max:2048',
              'image-multiple'   => 'required|array',
              'image-multiple.*' => 'required|image|mimes:jpeg,png,jpg|max:2048',
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
                $boilerplate = new Boilerplate;

                $boilerplate->text = $request->input('text');
                $boilerplate->textarea = $request->input('textarea');
                $boilerplate->select = $request->input('select2');
                $boilerplate->select_multiple = $request->input('select2-multiple');
                $boilerplate->image = UploadMedia::image($request->file('image'), Boilerplate::$directory_image);

                $images = [];

                foreach($request->file('image-multiple') as $image) {
                  $img = ['image' => UploadMedia::image($image, BoilerplateImage::$directory_image)];
                  array_push($images, $img);
                }

                if ($boilerplate->save()) {
                    $boilerplate->images()->createMany($images);

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.boilerplate.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function show($id)
    {
        $data['boilerplate'] = Boilerplate::findOrFail($id);

        return view('superuser.boilerplate.show', $data);
    }

    public function edit($id)
    {
        $data['boilerplate'] = Boilerplate::findOrFail($id);
        $data['boilerplate_image_trash'] = BoilerplateImage::where('boilerplate_id', $id)->onlyTrashed()->get();
        return view('superuser.boilerplate.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
              'text'             => 'string',
              'textarea'         => 'string',
              'select2'          => 'string|in:html,css,javascript,php',
              'select2-multiple' => 'array',
              'image'            => 'image|mimes:jpeg,png,jpg|max:2048',
              'image-multiple'   => 'array',
              'image-multiple.*' => 'image|mimes:jpeg,png,jpg|max:2048',
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
                $boilerplate = Boilerplate::find($id);

                if ($boilerplate === null) {
                    abort(404);
                }

                $boilerplate->text = $request->input('text');
                $boilerplate->textarea = $request->input('textarea');
                $boilerplate->select = $request->input('select2');
                $boilerplate->select_multiple = $request->input('select2-multiple');

                if (!empty($request->file('image'))) {
                    if (is_file_exists(Boilerplate::$directory_image.$boilerplate->image)) {
                        remove_file(Boilerplate::$directory_image.$boilerplate->image);
                    }

                    $boilerplate->image = UploadMedia::image($request->file('image'), Boilerplate::$directory_image);
                }


                if (!empty($request->file('image-multiple'))) {
                    $images = [];

                    foreach($request->file('image-multiple') as $image) {
                      $img = ['image' => UploadMedia::image($image, BoilerplateImage::$directory_image)];
                      array_push($images, $img);
                    }
                }

                if ($boilerplate->save()) {
                    if (isset($images)) {
                      $boilerplate->images()->createMany($images);
                    }

                    $response['notification'] = [
                        'alert' => 'notify',
                        'type' => 'success',
                        'content' => 'Success',
                    ];

                    $response['redirect_to'] = route('superuser.boilerplate.index');

                    return $this->response(200, $response);
                }
            }
        }
    }

    public function destroy(Request $request, $id)
    {
        if ($request->ajax()) {
            $boilerplate = Boilerplate::find($id);

            if ($boilerplate === null) {
                abort(404);
            }

            if ($boilerplate->delete()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }
        }
    }

    public function destroy_permanent(Request $request, $id)
    {
        if ($request->ajax()) {
            $boilerplate = Boilerplate::onlyTrashed()->find($id);

            if ($boilerplate === null) {
                abort(404);
            }

            if (is_file_exists(Boilerplate::$directory_image.$boilerplate->image)) {
                remove_file(Boilerplate::$directory_image.$boilerplate->image);
            }

            $images = $boilerplate->images()->onlyTrashed()->get()->pluck('image');

            foreach ($images as $img) {
                if (is_file_exists(BoilerplateImage::$directory_image.$img)) {
                    remove_file(BoilerplateImage::$directory_image.$img);
                }
            }

            $boilerplate->images()->forceDelete();

            if ($boilerplate->forceDelete()) {
                $response['redirect_to'] = '#datatable';
                return $this->response(200, $response);
            }

        }
    }

    public function restore(Request $request, $id)
    {
        if ($request->ajax()) {
            $boilerplate = Boilerplate::onlyTrashed()->find($id);

            if ($boilerplate === null) {
                abort(404);
            }

            $boilerplate->restore();
            $boilerplate->images()->restore();

            $response['redirect_to'] = '#datatable';
            return $this->response(200, $response);
        }
    }

    public function import_template()
    {
        $filename = 'boilerplate-import-template.xlsx';
        return Excel::download(new BoilerplateImportTemplate, $filename);
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'import_file' => 'required|file|mimes:xls,xlsx|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors()->all());
        }

        if ($validator->passes()) {
            Excel::import(new BoilerplateImport, $request->import_file);

            return redirect()->back();
        }
    }

    public function export()
    {
        $filename = 'boilerplate-' . date('d-m-Y_H-i-s') . '.xlsx';
        return Excel::download(new BoilerplateExport, $filename);
    }
}
