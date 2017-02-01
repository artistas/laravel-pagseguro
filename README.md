# Laravel PagSeguro 
### Checkout Transparente e Pagamento Recorrente(Assinaturas)

[![Developer](https://img.shields.io/badge/Developer-fernandobandeira-red.svg)](https://github.com/fernandobandeira)
[![Latest Stable Version](https://poser.pugx.org/artistas/laravel-pagseguro/v/stable)](https://packagist.org/packages/artistas/laravel-pagseguro)
[![Total Downloads](https://poser.pugx.org/artistas/laravel-pagseguro/downloads)](https://packagist.org/packages/artistas/laravel-pagseguro)
[![StyleCI](https://styleci.io/repos/66557385/shield)](https://styleci.io/repos/66557385)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/2b049d7be509420c810493c828eb943d)](https://www.codacy.com/app/fernando-bandeira/laravel-pagseguro?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=artistas/laravel-pagseguro&amp;utm_campaign=Badge_Grade)
[![Laravel](https://img.shields.io/badge/laravel-5.*-ff69b4.svg?style=flat-square)](https://laravel.com)

Visite a [Wiki](https://github.com/artistas/laravel-pagseguro/wiki) para verificar os detalhes de como utilizar esta Package.

Uma prévia de como é simples trabalhar com esta biblioteca:

```php
use PagSeguro; //Utilize a Facade

$pagseguro = PagSeguro::setReference('2')
->setSenderInfo([
  'senderName' => 'Nome Completo', //Deve conter nome e sobrenome
  'senderPhone' => '(32) 1324-1421', //Código de área enviado junto com o telefone
  'senderEmail' => 'email@email.com',
  'senderHash' => 'Hash gerado pelo javascript',
  'senderCNPJ' => '98.966.488/0001-00' //Ou CPF se for Pessoa Física
])
->setShippingAddress([
  'shippingAddressStreet' => 'Rua/Avenida',
  'shippingAddressNumber' => 'Número',
  'shippingAddressDistrict' => 'Bairro',
  'shippingAddressPostalCode' => '12345-678',
  'shippingAddressCity' => 'Cidade',
  'shippingAddressState' => 'UF'
])
->setItems([
  [
    'itemId' => 'ID',
    'itemDescription' => 'Nome do Item',
    'itemAmount' => 12.14, //Valor unitário
    'itemQuantity' => '2', // Quantidade de itens
  ],
  [
    'itemId' => 'ID 2',
    'itemDescription' => 'Nome do Item 2',
    'itemAmount' => 12.14,
    'itemQuantity' => '2',
  ]
])
->send([
  'paymentMethod' => 'boleto'
]);
```

###Contribuições
[![Developer](https://img.shields.io/badge/Contributor-ernandos-blue.svg)](https://github.com/ernandos)

[![Developer](https://img.shields.io/badge/Contributor-vanessasouto-blue.svg)](https://github.com/vanessasoutoc)
