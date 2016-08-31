# laravel-pagseguro
[![StyleCI](https://styleci.io/repos/66557385/shield)](https://styleci.io/repos/66557385)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/2b049d7be509420c810493c828eb943d)](https://www.codacy.com/app/fernando-bandeira/laravel-pagseguro?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=artistas/laravel-pagseguro&amp;utm_campaign=Badge_Grade)
[![Documentation Status](https://readthedocs.org/projects/laravel-pagseguro/badge/?version=latest)](http://laravel-pagseguro.readthedocs.io/pt/latest/?badge=latest)

Integração feita para o Laravel 5 com o PagSeguro, Checkout Transparente.

Adicione o pacote às suas dependências:
```php
composer require artistas/laravel-pagseguro
```

Adicione o seguinte service provider:
```php
Artistas\PagSeguro\PagSeguroServiceProvider::class
```

E a seguinte Facade:
```php
'PagSeguro' => Artistas\PagSeguro\PagSeguroFacade::class
```

Publique a configuração
```php
php artisan vendor:publish
```

Adicione os parametros no seu arquivo .env
```php
PAGSEGURO_SANDBOX=true
PAGSEGURO_EMAIL=
PAGSEGURO_TOKEN=
```

O resto da documentação virá junto com a próxima versão, que vai disponibilizar um formulário e javascript padrão (próximos dias).

```php
use PagSeguro;

$pagseguro = PagSeguro::setItems(
[
  [
    'itemId' => '142',
    'itemDescription' => 'Roupa 1234',
    'itemAmount' => 12.14,
    'itemQuantity' => '2',
  ],
  [
    'itemId' => '142',
    'itemDescription' => 'Roupa 1234',
    'itemAmount' => 12.14,
    'itemQuantity' => '2',
  ]
]
)->setReference('2')
->setSenderInfo([
  'senderName' => '  Teste  Teste  ',
  'senderPhone' => '(54) 8400-6464',
  'senderEmail' => 'teste@teste.com',
  'senderHash' => 'ewqewqeqw',
  'senderCNPJ' => '98.966.488/0001-00'
])
->setShippingAddress([
  'shippingAddressStreet' => '  Teste  Teste  ',
  'shippingAddressNumber' => '123',
  'shippingAddressDistrict' => 'ewqewqeqw',
  'shippingAddressPostalCode' => '12345-678',
  'shippingAddressCity' => 'Teste 123',
  'shippingAddressState' => 'RS'
])
->setCreditCardHolder([
  'creditCardHolderBirthDate' => '02/10/2014',
  'creditCardHolderCPF' => '621.084.997-09'
])
->setBillingAddress([])
->send([
  'paymentMethod' => 'boleto'
]);
```
