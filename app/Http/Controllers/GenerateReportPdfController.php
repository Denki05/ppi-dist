<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JasperPHP;

class GenerateReportPdfController extends Controller
{
    public function index()
    {
        $input = public_path().'/report/hello_world.jrxml';
        $output = public_path().'/report/output';
        $ext = "pdf";
        $compile = JasperPHP::compile($input)->execute();
        echo "<pre>";
        print_r($compile);
        echo "<pre>";
    }

    public function process()
    {
        $input = public_path().'/report/hello_wolrd.jasper';
        $output = public_path().'/report/output';
        $ext1 = "pdf";
        $ext2 = "xlsx";
        $process = JasperPHP::process(
            $input,
            $output,
            array(
                $ext1, $ext2
            )
        )->execute();
        echo "<pre>";
        print_r($process);
        echo "<pre>";

        DD($output);
    }
}
