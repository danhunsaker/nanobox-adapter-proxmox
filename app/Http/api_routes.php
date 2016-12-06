<?php

Route::get('/meta', 'MetaController@meta');
Route::get('/catalog', 'MetaController@catalog');
Route::post('/verify', 'MetaController@verify');
