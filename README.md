# laravel-pagseguro
[![StyleCI](https://styleci.io/repos/66557385/shield)](https://styleci.io/repos/66557385)

Integração feita para o Laravel 5 com o PagSeguro, Checkout Transparente.

Adicione o pacote às suas dependências:
```
composer require "artistas/laravel-pagseguro"
```

Adicione o seguinte service provider:
```
Artistas\PagSeguro\PagSeguroServiceProvider::class
```

E a seguinte Facade:
```
'PagSeguro' => Artistas\PagSeguro\PagSeguroFacade::class
```
