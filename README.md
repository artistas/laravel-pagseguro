<p align="center"><img src="https://stc.pagseguro.uol.com.br/pagseguro/i/logos/logo_pagseguro200x41.png"></p>

<p align="center">
<a href="https://www.codacy.com/app/fernando-bandeira/laravel-pagseguro?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=artistas/laravel-pagseguro&amp;utm_campaign=Badge_Grade"><img src="https://api.codacy.com/project/badge/Grade/2b049d7be509420c810493c828eb943d" alt="Codacy Badge"></a>
<a href="https://packagist.org/packages/artistas/laravel-pagseguro"><img src="https://poser.pugx.org/artistas/laravel-pagseguro/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/artistas/laravel-pagseguro"><img src="https://poser.pugx.org/artistas/laravel-pagseguro/d/monthly" alt="Monthly Downloads"></a>
<a href="https://packagist.org/packages/artistas/laravel-pagseguro"><img src="https://poser.pugx.org/artistas/laravel-pagseguro/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://laravel.com"><img src="https://img.shields.io/badge/laravel-5.*-ff69b4.svg?style=flat-square" alt="License"></a>
</p>

Visite a [Wiki](https://github.com/artistas/laravel-pagseguro/wiki) para verificar os detalhes de como utilizar esta Package.

Qualquer problema, dúvida ou sugestão sinta-se livre para abrir uma issue ou enviar um PR.

```php
use PagSeguro; //Utilize a Facade

try {
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
}
catch(\Artistas\PagSeguro\PagSeguroException $e) {
    $e->getCode(); //codigo do erro
    $e->getMessage(); //mensagem do erro
}
```

#### Créditos
Criador: [fernandobandeira](https://github.com/fernandobandeira)

[Contribuidores](https://github.com/artistas/laravel-pagseguro/graphs/contributors)

PagSeguro Recorrente (Inicial): [vanessasoutoc](https://github.com/vanessasoutoc)

