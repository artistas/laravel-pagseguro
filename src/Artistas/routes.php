<?php

Route::group(['namespace' => 'Artistas\PagSeguro'], function () {
    Route::get('/pagseguro/session', 'PagSeguroController@session');
    Route::get('/pagseguro/javascript', 'PagSeguroController@javascript');
});
