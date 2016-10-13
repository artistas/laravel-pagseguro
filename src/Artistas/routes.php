<?php

Route::get('/pagseguro/session', function () {
    return \PagSeguro::getSession();
});

Route::get('/pagseguro/session/reset', function () {
    return \PagSeguro::startSession();
});
