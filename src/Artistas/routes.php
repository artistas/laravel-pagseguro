<?php

Route::get('/pagseguro/session', function () {
    return \PagSeguro::startSession();
});

Route::get('/pagseguro/javascript', function () {
    return file_get_contents(\PagSeguro::getUrl()['javascript']);
});
