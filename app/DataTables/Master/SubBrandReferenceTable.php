<?php

namespace App\DataTables\Master;

use App\DataTables\Table;
use App\Entities\Master\SubBrandReference;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SubBrandReferenceTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    public function query(Request $request)
    {
        $model = SubBrandReference::where('master_sub_brand_references.status', 1)
                            ->where(function ($query) use ($request) {
                                if ($request->filter_searah != 'all') {
                                    $query->where('master_sub_brand_references.name', $request->filter_searah);
                                } else {
                                    $query;
                                }
                            })
                            ->leftJoin('master_brand_references', 'master_sub_brand_references.brand_reference_id', '=', 'master_brand_references.id')
                            ->selectRaw('
                                master_sub_brand_references.id AS id, 
                                master_sub_brand_references.name AS searah_name, 
                                master_sub_brand_references.code AS searah_code, 
                                master_sub_brand_references.status AS status, 
                                master_sub_brand_references.link AS searah_link, 
                                master_brand_references.id AS brand_id, 
                                master_brand_references.name AS brand_name, 
                                master_sub_brand_references.created_at AS created_date,
                                master_sub_brand_references.image_botol AS image_botol
                            ');

        return $model;
    }

    /**
     * Build DataTable class.
     */
    public function build(Request $request)
    {
        $table = Table::of($this->query($request));
        
        $table->addIndexColumn();

        $table->setRowClass(function (SubBrandReference $model) {
            return $model->status == $model::STATUS['DELETED'] ? 'table-danger' : '';
        });
        
        $table->editColumn('status', function (SubBrandReference $model) {
            return $model->status();
        });

        $table->editColumn('created_date', function (SubBrandReference $model) {
            return [
              'display' => Carbon::parse($model->created_date)->format('j F Y H:i:s'),
              'timestamp' => $model->created_date
            ];
        });

        $table->addColumn('action', function (SubBrandReference $model) {
            $view = route('superuser.master.sub_brand_reference.show', $model);
            $edit = route('superuser.master.sub_brand_reference.edit', $model);
            $destroy = route('superuser.master.sub_brand_reference.destroy', $model);
            $upload_image = route('superuser.master.sub_brand_reference.edit_image', $model);
            
            if($model->status == $model::STATUS['ACTIVE']){
                if($model->image_botol == null){
                    return "
                        <a href=\"{$upload_image}\">
                            <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"Upload Image\">
                                <i class=\"fa fa-upload\"></i>
                            </button>
                        </a>
                        <a href=\"{$view}\">
                                <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"View\">
                                    <i class=\"fa fa-eye\"></i>
                                </button>
                            </a>
                            <a href=\"{$edit}\">
                                <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-warning\" title=\"Edit\">
                                    <i class=\"fa fa-pencil\"></i>
                                </button>
                            </a>
                            <a href=\"javascript:deleteConfirmation('{$destroy}')\">
                                <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-danger\" title=\"Delete\">
                                    <i class=\"fa fa-trash\"></i>
                                </button>
                            </a>
                    ";
                }elseif($model->image_botol == null){
                    return 
                            "
                            <a href=\"{$view}\">
                                <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"View\">
                                    <i class=\"fa fa-eye\"></i>
                                </button>
                            </a>
                            <a href=\"{$edit}\">
                                <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-warning\" title=\"Edit\">
                                    <i class=\"fa fa-pencil\"></i>
                                </button>
                            </a>
                            <a href=\"javascript:deleteConfirmation('{$destroy}')\">
                                <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-danger\" title=\"Delete\">
                                    <i class=\"fa fa-trash\"></i>
                                </button>
                            </a>
                            ";
                }
            }elseif($model->status == $model::STATUS['DELETED']){
                return "
                    <a href=\"{$view}\">
                        <button type=\"button\" class=\"btn btn-sm btn-circle btn-alt-secondary\" title=\"View\">
                            <i class=\"fa fa-eye\"></i>
                        </button>
                    </a>
                ";
            }
            
        });

        return $table->make(true);
    }
}