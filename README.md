# Laravel PagSeguro 
### Checkout Transparente
[![StyleCI](https://styleci.io/repos/66557385/shield)](https://styleci.io/repos/66557385)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/2b049d7be509420c810493c828eb943d)](https://www.codacy.com/app/fernando-bandeira/laravel-pagseguro?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=artistas/laravel-pagseguro&amp;utm_campaign=Badge_Grade)
[![Laravel](https://img.shields.io/badge/laravel-5.*-ff69b4.svg?style=flat-square)](https://laravel.com)

Visite a [Wiki](https://github.com/artistas/laravel-pagseguro/wiki) para verificar os detalhes de como utilizar esta Package.

Esta biblioteca disponibiliza um formulário padrão para ser utilizado, o mesmo é feito utilizando o Bootstrap e não é obrigatório, podendo ser customizado para trabalhar com outros frameworks css.

Verifique o [CodePen](http://codepen.io/fernandobandeira/full/QKLYzZ/) do mesmo.

![Formulário padrão](https://s18.postimg.org/ozpmy6sc9/formulario.jpg "Formulário incluso na biblioteca")

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

Com apenas este código já é possível realizar um pagamento via boleto. Com excessão dos preços que devem ser passados da forma especificada, os dados não precisam ter uma formatação específica, desde que tenham todas as informações necessárias.

A Biblioteca possui algumas validações por padrão para não precisar esperar o retorno do PagSeguro caso algum dado não esteja informado corretamente, o retorno dos erros pode ser capturado com um ```try/catch``` na excessão ```PagSeguroException``` mais detalhes estão descritos na [Wiki](https://github.com/artistas/laravel-pagseguro/wiki).
