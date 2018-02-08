<?php

Route::namespace('Artistas\PagSeguro')->group(function() {
    Route::get('/pagseguro/session', 'PagSeguroController@session');
    Route::get('/pagseguro/javascript', 'PagSeguroController@javascript');
});
