<?php
namespace PHPampa\PagSeguro;
use Illuminate\Support\Facades\Facade;
class PagSeguroFacade extends Facade {
    protected static function getFacadeAccessor() { return 'pagseguro'; }
}