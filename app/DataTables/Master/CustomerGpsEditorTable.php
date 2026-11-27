<?php

namespace App\DataTables\Master;

use App\DataTables\Table;
use App\Entities\Master\CustomerOtherAddress;
use Illuminate\Http\Request;
use DB;

class CustomerGpsEditorTable extends Table
{
    private function query(Request $request)
    {
        $model = DB::table('master_customer_other_addresses')
            ->leftJoin('master_customers', 'master_customer_other_addresses.customer_id', '=', 'master_customers.id')
            ->selectRaw('
                master_customer_other_addresses.id AS id,
                master_customer_other_addresses.customer_id AS customer_id,
                master_customer_other_addresses.name AS member_name,
                master_customer_other_addresses.address AS member_address,
                master_customer_other_addresses.text_kota AS member_kota,
                master_customer_other_addresses.gps_latitude AS gps_latitude,
                master_customer_other_addresses.gps_longitude AS gps_longitude,
                master_customers.name AS store_name,
                master_customers.text_kota AS store_kota
            ')
            ->where('master_customer_other_addresses.status', CustomerOtherAddress::STATUS['ACTIVE']);

        if ($request->filled('q')) {
            $keyword = '%' . trim($request->q) . '%';
            $model->where(function ($query) use ($keyword) {
                $query->where('master_customer_other_addresses.name', 'like', $keyword)
                    ->orWhere('master_customers.name', 'like', $keyword)
                    ->orWhere('master_customer_other_addresses.text_kota', 'like', $keyword)
                    ->orWhere('master_customer_other_addresses.address', 'like', $keyword);
            });
        }

        if ($request->input('only_empty') == 1) {
            $model->where(function ($query) {
                $query->whereNull('master_customer_other_addresses.gps_latitude')
                    ->orWhereIn('master_customer_other_addresses.gps_latitude', ['', '-', '--'])
                    ->orWhereNull('master_customer_other_addresses.gps_longitude')
                    ->orWhereIn('master_customer_other_addresses.gps_longitude', ['', '-', '--']);
            });
        }

        return $model;
    }

    public function build(Request $request)
    {
        $table = Table::of($this->query($request));

        $table->addIndexColumn();

        $table->editColumn('member_address', function ($model) {
            return $model->member_address;
        });

        $table->addColumn('gps_latitude', function ($model) {
            $value = $this->normalizeCoordinate($model->gps_latitude);
            $value = htmlspecialchars($value, ENT_QUOTES);

            return '<input type="text" class="form-control form-control-sm gps-input gps-lat"'
                . ' value="' . $value . '" data-original="' . $value . '"'
                . ' data-member-id="' . htmlspecialchars((string) $model->id, ENT_QUOTES) . '" placeholder="-8.123456" autocomplete="off">';
        });

        $table->addColumn('gps_longitude', function ($model) {
            $value = $this->normalizeCoordinate($model->gps_longitude);
            $value = htmlspecialchars($value, ENT_QUOTES);

            return '<input type="text" class="form-control form-control-sm gps-input gps-lng"'
                . ' value="' . $value . '" data-original="' . $value . '"'
                . ' data-member-id="' . htmlspecialchars((string) $model->id, ENT_QUOTES) . '" placeholder="116.123456" autocomplete="off">';
        });

        $table->addColumn('map', function ($model) {
            $latitude = $this->normalizeCoordinate($model->gps_latitude);
            $longitude = $this->normalizeCoordinate($model->gps_longitude);

            if ($latitude === '' || $longitude === '' || !is_numeric($latitude) || !is_numeric($longitude)) {
                $query = urlencode(trim(($model->member_address ?: '') . ' ' . ($model->member_kota ?: '')));
                $label = 'Cari di Maps';
            } else {
                $query = urlencode($latitude . ',' . $longitude);
                $label = 'Lihat Maps';
            }

            return '<a href="https://www.google.com/maps/search/?api=1&query=' . $query . '"'
                . ' target="_blank" rel="noopener" class="btn btn-sm btn-outline-info">'
                . '<i class="fa fa-map-marker-alt mr-5"></i>' . $label . '</a>';
        });

        $table->addColumn('action', function ($model) {
            return '<button type="button" class="btn btn-sm btn-alt-success btn-save-row" data-member-id="' . $model->id . '">'
                . '<i class="fa fa-save mr-5"></i>Simpan</button>';
        });

        $table->rawColumns(['gps_latitude', 'gps_longitude', 'map', 'action']);

        return $table->make(true);
    }

    private function normalizeCoordinate($value)
    {
        $value = trim((string) $value);

        if ($value === '-' || $value === '--') {
            return '';
        }

        return $value;
    }
}
