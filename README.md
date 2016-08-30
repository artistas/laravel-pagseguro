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

Publique a configuração
```
php artisan vendor:publish
```

Adicione os parametros no seu arquivo .env
```
PAGSEGURO_SANDBOX=true
PAGSEGURO_EMAIL=
PAGSEGURO_TOKEN=
```

O resto da documentação virá junto com a próxima versão, que vai disponibilizar um formulário e javascript padrão (próximos dias).
