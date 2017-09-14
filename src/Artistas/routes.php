<?php

Route::get('/pagseguro/session', function () {
    return \PagSeguro::startSession();
});

Route::get('/pagseguro/javascript', function () {
    return response()->make(file_get_contents(\PagSeguro::getUrl()['javascript']), '200')->header('Content-Type', 'text/javascript');
});
