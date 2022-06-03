<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PHPJasper\PHPJasper;

class JasperReportPdfController extends Controller
{
    public function compailePdf()
    {
        $input = public_path().'/report/pack.jrxml';

        $jasper = new PHPJasper;
        $output = $jasper->compile($input)->execute();

        // foreach($output as $parms_dec)
        //     print $parms_dec.'<pre>';

    }

    public function getReportPdf()
    {
        $input = public_path().'/report/pack.jasper';
        $output = public_path().'/report/output';
        $ext = [
            'format' => ['pdf'],
            'params' => [],
            'db_connection' => [
                'driver' => 'mysql',
                'username' => 'root',
                'password' => '',
                'host' => '127.0.0.1',
                'database' => 'ppi-dist',
                'port' => '3306'
            ]
        ];

        $jasper = new PHPJasper;

        $process = $jasper->process(
            $input,
            $output,
            $ext
        )->execute();
    }
}
