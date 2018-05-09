<?php

namespace Artistas\PagSeguro;

use Illuminate\Support\Facades\Facade;

class PagSeguroBoletoFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'pagseguro_boleto';
    }
}
