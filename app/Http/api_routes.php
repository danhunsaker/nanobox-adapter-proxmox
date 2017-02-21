<?php

Route::get('/meta', ['as' => 'api.v1.meta', 'uses' => 'MetaController@meta']);
Route::get('/catalog', ['as' => 'api.v1.catalog', 'uses' => 'MetaController@catalog']);
Route::post('/verify', ['as' => 'api.v1.verify', 'uses' => 'MetaController@verify', 'middleware' => 'authdata:present']);

Route::group(['middleware' => 'authdata:valid'], function () {
    Route::resource('/keys', 'KeyController', ['only' => ['store', 'show', 'destroy']]);
    Route::patch('/servers/{servers}/reboot', ['as' => 'api.v1.servers.reboot', 'uses' => 'ServerController@reboot']);
    Route::patch('/servers/{servers}/rename', ['as' => 'api.v1.servers.rename', 'uses' => 'ServerController@rename']);
    Route::resource('/servers', 'ServerController', ['only' => ['store', 'show', 'destroy']]);
});
