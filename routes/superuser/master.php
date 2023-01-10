<?php

Route::group([
    'middleware' => ['role:Developer|SuperAdmin|Admin','auth:superuser'],
    'as' => 'master.',
    'prefix' => '/master',
    'namespace' => 'Master'
], function () {

    Route::group(['as' => 'company.', 'prefix' => '/company'], function () {
        Route::get('/', 'CompanyController@show')->name('show');
        Route::get('/edit', 'CompanyController@edit')->name('edit');
        Route::put('/update', 'CompanyController@update')->name('update');
    });

    Route::group(['as' => 'branch_office.', 'prefix' => '/branch_office'], function () {
        Route::get('/import_template', 'BranchOfficeController@import_template')->name('import_template');
        Route::post('/import', 'BranchOfficeController@import')->name('import');
        Route::get('/export', 'BranchOfficeController@export')->name('export');
    });
    Route::resource('branch_office', 'BranchOfficeController');

    Route::group(['as' => 'warehouse.', 'prefix' => '/warehouse'], function () {
        Route::get('/import_template', 'WarehouseController@import_template')->name('import_template');
        Route::post('/import', 'WarehouseController@import')->name('import');
        Route::get('/export', 'WarehouseController@export')->name('export');
    });
    Route::resource('warehouse', 'WarehouseController');

    Route::group(['as' => 'product.', 'prefix' => '/product'], function () {
       
        Route::get('/import_template', 'ProductController@import_template')->name('import_template');
        Route::post('/import', 'ProductController@import')->name('import');
        Route::get('/export', 'ProductController@export')->name('export');
        // Route::post('/addMorePost', 'ProductController@addMorePost')->name('addMorePost');
        Route::post('/getcategory', 'ProductController@getcategory')->name('getcategory');

        Route::post('/delete_multiple', 'ProductController@destroyMultiple')->name('delete_multiple');

        Route::get('/{id}/disable', 'ProductController@disable')->name('disable');
        Route::get('/{id}/enable', 'ProductController@enable')->name('enable');

        Route::get('/cetak', 'ProductController@cetak')->name('cetak');
        Route::post('/cetak/pdf', 'ProductController@cetakPdf')->name('pdf');
        
        Route::group(['as' => 'min_stock.'], function () {
            Route::get('/{id}/min_stock/create', 'ProductMinStockController@create')->name('create');
            Route::post('/{id}/min_stock', 'ProductMinStockController@store')->name('store');
            Route::get('/{id}/min_stock/{min_stock_id}/edit', 'ProductMinStockController@edit')->name('edit');
            Route::put('/{id}/min_stock/{min_stock_id}', 'ProductMinStockController@update')->name('update');
            Route::delete('/{id}/min_stock/{min_stock_id}', 'ProductMinStockController@destroy')->name('destroy');
        });
    });
    Route::resource('product', 'ProductController');

    Route::group(['as' => 'product_category.', 'prefix' => '/product_category'], function () {
        Route::get('/import_template', 'ProductCategoryController@import_template')->name('import_template');
        Route::post('/import', 'ProductCategoryController@import')->name('import');
        Route::get('/export', 'ProductCategoryController@export')->name('export');

        Route::group(['as' => 'type.'], function() {
            Route::get('/{id}/type', 'ProductCategoryTypeController@manage')->name('manage');
            Route::post('/{id}/type', 'ProductCategoryTypeController@add')->name('add');
            Route::get('/{id}/type/{type_id}/remove', 'ProductCategoryTypeController@remove')->name('remove');
        });
    });
    Route::resource('product_category', 'ProductCategoryController');

    Route::group(['as' => 'product_type.', 'prefix' => '/product_type'], function () {
        Route::get('/import_template', 'ProductTypeController@import_template')->name('import_template');
        Route::post('/import', 'ProductTypeController@import')->name('import');
        Route::get('/export', 'ProductTypeController@export')->name('export');

        Route::post('/delete_multiple', 'ProductTypeController@destroyMultiple')->name('delete_multiple');
    });
    Route::resource('product_type', 'ProductTypeController');

    Route::group(['as' => 'unit.', 'prefix' => '/unit'], function () {
        Route::get('/import_template', 'UnitController@import_template')->name('import_template');
        Route::post('/import', 'UnitController@import')->name('import');
        Route::get('/export', 'UnitController@export')->name('export');
    });
    Route::resource('unit', 'UnitController');

    Route::group(['as' => 'customer.', 'prefix' => '/customer'], function () {
        Route::get('/import_template', 'CustomerController@import_template')->name('import_template');
        Route::post('/import', 'CustomerController@import')->name('import');
        Route::get('/export', 'CustomerController@export')->name('export');
        Route::get('/export_customer', 'CustomerController@export_customer')->name('export_customer');
		Route::post('/getkabupaten', 'CustomerController@getkabupaten')->name('getkabupaten');
        Route::post('/getkecamatan', 'CustomerController@getkecamatan')->name('getkecamatan');
        Route::post('/getkelurahan', 'CustomerController@getkelurahan')->name('getkelurahan');
        Route::post('/getzipcode', 'CustomerController@getzipcode')->name('getzipcode');
        Route::post('/getcustomertype', 'CustomerController@getcustomertype')->name('getcustomertype');
        // Route::get('/{id}/history', 'CustomerController@history')->name('history');

        Route::group(['as' => 'other_address.', 'prefix' => '/customer'], function () {
            Route::get('/{id}/other_address/create', 'CustomerOtherAddressController@create')->name('create');
            Route::post('/{id}/other_address', 'CustomerOtherAddressController@store')->name('store');
            Route::get('/{id}/other_address/{address_id}/edit', 'CustomerOtherAddressController@edit')->name('edit');
            Route::put('/{id}/other_address/{address_id}', 'CustomerOtherAddressController@update')->name('update');
            Route::delete('/{id}/other_address/{address_id}', 'CustomerOtherAddressController@destroy')->name('destroy');
            Route::post('/other_address/getkabupaten', 'CustomerOtherAddressController@getkabupaten')->name('getkabupaten');
            Route::post('/other_address/getkecamatan', 'CustomerOtherAddressController@getkecamatan')->name('getkecamatan');
            Route::post('/other_address/getkelurahan', 'CustomerOtherAddressController@getkelurahan')->name('getkelurahan');
            Route::post('/other_address/getzipcode', 'CustomerOtherAddressController@getzipcode')->name('getzipcode');
        });

        Route::group(['as' => 'contact.'], function() {
            Route::get('/{id}/contact', 'CustomerContactController@manage')->name('manage');
            Route::post('/{id}/contact', 'CustomerContactController@add')->name('add');
            Route::get('/{id}/contact/{contact_id}/remove', 'CustomerContactController@remove')->name('remove');
        });
    });
    Route::resource('customer', 'CustomerController');

    Route::group(['as' => 'customer_category.', 'prefix' => '/customer_category'], function () {
        Route::get('/import_template', 'CustomerCategoryController@import_template')->name('import_template');
        Route::post('/import', 'CustomerCategoryController@import')->name('import');
        Route::get('/export', 'CustomerCategoryController@export')->name('export');

        Route::group(['as' => 'type.'], function() {
            Route::get('/{id}/type', 'CustomerCategoryTypeController@manage')->name('manage');
            Route::post('/{id}/type', 'CustomerCategoryTypeController@add')->name('add');
            Route::get('/{id}/type/{type_id}/remove', 'CustomerCategoryTypeController@remove')->name('remove');
        });
    });
    Route::resource('customer_category', 'CustomerCategoryController');

    Route::group(['as' => 'customer_type.', 'prefix' => '/customer_type'], function () {
        Route::get('/import_template', 'CustomerTypeController@import_template')->name('import_template');
        Route::post('/import', 'CustomerTypeController@import')->name('import');
        Route::get('/export', 'CustomerTypeController@export')->name('export');
    });
    Route::resource('customer_type', 'CustomerTypeController');

    Route::group(['as' => 'brand_reference.', 'prefix' => '/brand_reference'], function () {
        Route::get('/import_template', 'BrandReferenceController@import_template')->name('import_template');
        Route::post('/import', 'BrandReferenceController@import')->name('import');
        Route::get('/export', 'BrandReferenceController@export')->name('export');
    });
    Route::resource('brand_reference', 'BrandReferenceController');

    Route::group(['as' => 'sub_brand_reference.', 'prefix' => '/sub_brand_reference'], function () {
        Route::get('/import_template', 'SubBrandReferenceController@import_template')->name('import_template');
        Route::post('/import', 'SubBrandReferenceController@import')->name('import');
        Route::get('/export', 'SubBrandReferenceController@export')->name('export');
    });
    Route::resource('sub_brand_reference', 'SubBrandReferenceController');

    Route::group(['as' => 'question.', 'prefix' => '/question'], function () {
        Route::get('/import_template', 'QuestionController@import_template')->name('import_template');
        Route::post('/import', 'QuestionController@import')->name('import');
        Route::get('/export', 'QuestionController@export')->name('export');
    });
    Route::resource('question', 'QuestionController');

    Route::group(['as' => 'contact.', 'prefix' => '/contact'], function () {
        Route::get('/import_template', 'ContactController@import_template')->name('import_template');
        Route::post('/import', 'ContactController@import')->name('import');
        Route::get('/export', 'ContactController@export')->name('export');
    });
    Route::resource('contact', 'ContactController');

    Route::group(['as' => 'vendor.', 'prefix' => '/vendor'], function () {
        Route::get('/import_template', 'VendorController@import_template')->name('import_template');
        Route::post('/import', 'VendorController@import')->name('import');
        Route::get('/export', 'VendorController@export')->name('export');

        Route::group(['as' => 'contact.'], function() {
            Route::get('/{id}/contact', 'VendorContactController@manage')->name('manage');
            Route::post('/{id}/contact', 'VendorContactController@add')->name('add');
            Route::get('/{id}/contact/{contact_id}/remove', 'VendorContactController@remove')->name('remove');
        });

        Route::group(['as' => 'detail.'], function () {
            Route::get('{id}/detail/create', 'VendorDetailController@create')->name('create');
            Route::post('{id}/detail', 'VendorDetailController@store')->name('store');
            Route::get('{id}/detail/{detail_id}/edit', 'VendorDetailController@edit')->name('edit');
            Route::put('{id}/detail/{detail_id}', 'VendorDetailController@update')->name('update');
            // Route::delete('{id}/detail/{detail_id}', 'VendorDetailController@destroy')->name('destroy');
            // Route::post('/detail/bulk_delete', 'VendorDetailController@bulk_delete')->name('bulk_delete');
        });
    });
    Route::resource('vendor', 'VendorController');

    Route::group(['as' => 'ekspedisi.', 'prefix' => '/ekspedisi'], function () {
        Route::get('/import_template', 'EkspedisiController@import_template')->name('import_template');
        Route::post('/import', 'EkspedisiController@import')->name('import');
        Route::get('/export', 'EkspedisiController@export')->name('export');
    });
    Route::resource('ekspedisi', 'EkspedisiController');

    Route::group(['as' => 'dokumen.', 'prefix' => '/dokumen'], function () {
        // Route::get('/import_template', 'EkspedisiController@import_template')->name('import_template');
        // Route::post('/import', 'EkspedisiController@import')->name('import');
        // Route::get('/export', 'EkspedisiController@export')->name('export');
		Route::post('/getstore', 'DokumenController@getstore')->name('getstore');

    });
    Route::resource('dokumen', 'DokumenController');

    Route::group(['as' => 'customer_other_address.', 'prefix' => '/customer_other_address'], function () {
        Route::post('/getkabupaten', 'CustomerOtherAddressController@getkabupaten')->name('getkabupaten');
        Route::post('/getkecamatan', 'CustomerOtherAddressController@getkecamatan')->name('getkecamatan');
        Route::post('/getkelurahan', 'CustomerOtherAddressController@getkelurahan')->name('getkelurahan');
        Route::post('/getzipcode', 'CustomerOtherAddressController@getzipcode')->name('getzipcode');
    });
    Route::resource('customer_other_address', 'CustomerOtherAddressController');

    Route::group(['as' => 'customer_contact.', 'prefix' => '/customer_contact'], function () {
        Route::get('/import_template', 'CustomerContactController@import_template')->name('import_template');
        Route::post('/import', 'CustomerContactController@import')->name('import');
        Route::get('/export', 'CustomerContactController@export')->name('export');
        Route::post('/getstore', 'CustomerContactController@getstore')->name('getstore');
    });
    Route::resource('customer_contact', 'CustomerContactController');

    Route::group(['as' => 'brand_lokal.', 'prefix' => '/brand_lokal'], function () {
        // Route::get('/import_template', 'BrandReferenceController@import_template')->name('import_template');
        // Route::post('/import', 'BrandReferenceController@import')->name('import');
        // Route::get('/export', 'BrandReferenceController@export')->name('export');
    });
    Route::resource('brand_lokal', 'BrandLokalController');
});