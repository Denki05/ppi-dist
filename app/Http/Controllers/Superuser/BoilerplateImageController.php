<?php

namespace App\Http\Controllers\Superuser;

use App\Entities\Boilerplate;
use App\Entities\BoilerplateImage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BoilerplateImageController extends Controller
{
    public function delete(Request $request, $id)
    {
        if ($request->ajax()) {
            $boilerplate_image = BoilerplateImage::find($id);

            if ($boilerplate_image === null) {
                abort(404);
            }

            if ($boilerplate_image->delete()) {
                return $this->response(200);
            }
        }
    }

    public function restore($parent_id, $id)
    {
        $boilerplate = Boilerplate::findOrFail($parent_id);

        $boilerplate_image = BoilerplateImage::where('boilerplate_id', $parent_id)->where('id', $id)->onlyTrashed()->firstOrFail();

        $boilerplate_image->restore();

        return redirect()->route('superuser.boilerplate.edit', $parent_id);
    }

    public function restore_all($parent_id)
    {
        $boilerplate = Boilerplate::findOrFail($parent_id);
        $boilerplate->images()->restore();

        return redirect()->route('superuser.boilerplate.edit', $parent_id);
    }

    public function destroy($parent_id, $id)
    {
        $boilerplate = Boilerplate::findOrFail($parent_id);

        $boilerplate_image = BoilerplateImage::where('boilerplate_id', $parent_id)->where('id', $id)->onlyTrashed()->firstOrFail();

        if (is_file_exists(BoilerplateImage::$directory_image.$boilerplate_image->image)) {
            remove_file(BoilerplateImage::$directory_image.$boilerplate_image->image);
        }

        $boilerplate_image->forceDelete();

        return redirect()->route('superuser.boilerplate.edit', $parent_id);
    }

    public function destroy_all($parent_id)
    {
        $boilerplate = Boilerplate::findOrFail($parent_id);

        $images = $boilerplate->images()->onlyTrashed()->get()->pluck('image');

        foreach ($images as $img) {
            if (is_file_exists(BoilerplateImage::$directory_image.$img)) {
                remove_file(BoilerplateImage::$directory_image.$img);
            }
        }

        $boilerplate->images()->onlyTrashed()->forceDelete();

        return redirect()->route('superuser.boilerplate.edit', $parent_id);
    }
}
