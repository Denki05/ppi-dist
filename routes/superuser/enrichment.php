<?php

Route::group([
    'middleware' => ['role:Developer|SuperAdmin', 'auth:superuser'],
    'as' => 'enrichment.',
    'prefix' => '/enrichment',
    'namespace' => 'Enrichment'
], function () {
    Route::get('/products', 'EnrichmentController@products')->name('products');
    Route::post('/run_selected', 'EnrichmentController@runSelected')->name('run_selected');
    Route::get('/results', 'EnrichmentController@results')->name('results');
    Route::get('/compare', 'EnrichmentController@compare')->name('compare');
    Route::post('/verify', 'EnrichmentController@verify')->name('verify');
    Route::post('/reject', 'EnrichmentController@reject')->name('reject');
    Route::post('/sample_run', 'EnrichmentController@sampleRun')->name('sample_run');

    Route::group(['as' => 'settings.', 'prefix' => '/settings'], function () {
        Route::get('/', 'SettingController@index')->name('index');
        Route::post('/save', 'SettingController@save')->name('save');
    });
});
