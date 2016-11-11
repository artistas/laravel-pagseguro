<?php

Route::get('/pagseguro/session', function () {
    return \PagSeguro::getSession();
});

Route::get('/pagseguro/session/reset', function () {
    return \PagSeguro::startSession();
});

Route::get('/pagseguro/javascript', function () {
    return file_get_contents(\PagSeguro::getUrl()['javascript']);
});