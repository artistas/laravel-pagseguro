<?php

namespace Artistas\PagSeguro;

class PagSeguro extends PagSeguroClient
{

  /**
  * Define o tipo de comprador
  * @var string
  */
  private $senderType;
  /**
  * Informações do comprador
  * @var array
  */
  private $senderInfo;
  /**
  * Endereço do comprador
  * @var array
  */
  private $senderAddress;

  /**
  * Define o tipo do comprador
  * @param string $senderType
  * @return $this
  */
  public function setSenderType($senderType) {
    $this->senderType = $senderType;

    return $this;
  }

  /**
  * Define os dados do comprador
  * @param array $senderInfo
  * @return $this
  */
  public function setSenderInfo(array $senderInfo)
  {
    if ($this->sandbox) {
      $formattedSenderEmail = 'teste@sandbox.pagseguro.com.br';
    } else {
      $formattedSenderEmail = $senderInfo['senderEmail'];
    }

    $formattedSenderPhone = preg_replace('/\D/', '', $senderInfo['senderPhone']);

    $formattedSenderInfo = [
      'senderName' = trim(preg_replace('/\s+/', ' ',$senderInfo['senderName'])),
      'senderAreaCode' = substr($formattedSenderPhone, 0, 2),
      'senderPhone' = substr($formattedSenderPhone, 2),
      'senderEmail' => $formattedSenderEmail
    ];

    if ($this->senderType === 'J') {
      $formattedSenderInfo['senderCNPJ'] = preg_replace('/\D/', '', $senderInfo['senderCNPJ']);
    } else {
      $formattedSenderInfo['senderCPF'] = preg_replace('/\D/', '', $senderInfo['senderCPF']);
    }

    if ($this->validateSenderInfo($formattedSenderInfo)) {
        $this->senderInfo = $formattedSenderInfo;
    }

    return $this;
  }

  /**
  * Valida os dados contidos na array de informações do comprador
  * @param  array $senderAddress
  * @return array
  * @throws \Artistas\PagSeguro\PagSeguroException
  */
  protected function validateSenderInfo($formattedSenderInfo)
  {
    $rules = [
      'senderName' => 'required|max:50',
      'senderAreaCode' => 'required|digits:2',
      'senderPhone' => 'required|digits_between:8,9',
      'senderEmail' => 'required|email|max:60',
    ];

    if ($this->senderType === 'J') {
      $rules['senderCNPJ'] = 'required|digits:14';
    } else {
      $rules['senderCPF'] = 'required|digits:11';
    }

    $validator = $this->validator->make($formattedSenderInfo, $rules);
    if ($validator->fails()) {
      throw new PagSeguroException($validator->messages()->first());
    }

    return true;
  }

  /**
  * Define o endereço do comprador
  * @param array $senderAddress
  * @return $this
  */
  public function setSenderAddress(array $senderAddress)
  {
    $formattedSenderAddress = [
      'shippingAddressStreet' => trim(preg_replace('/\s+/', ' ',$senderAddress['shippingAddressStreet'])),
      'shippingAddressNumber' => trim(preg_replace('/\s+/', ' ',$senderAddress['shippingAddressNumber'])),
      'shippingAddressComplement' => trim(preg_replace('/\s+/', ' ',$senderAddress['shippingAddressComplement'])),
      'shippingAddressDistrict' => trim(preg_replace('/\s+/', ' ',$senderAddress['shippingAddressDistrict'])),
      'shippingAddressPostalCode' => preg_replace('/\D/', '', $senderAddress['shippingAddressPostalCode']),
      'shippingAddressCity' => trim(preg_replace('/\s+/', ' ',$senderAddress['shippingAddressCity'])),
      'shippingAddressState' => strtoupper($senderAddress['shippingAddressState']),
      'shippingAddressCountry' => 'BRA',
    ]
0
    if($this->validateSenderAddress($formattedSenderAddress)) {
        $this->senderAddress = $formattedSenderAddress;
    }

    return $this;
  }

  /**
  * Valida os dados contidos na array de endereço do comprador
  * @param  array $senderAddress
  * @return array
  * @throws \Artistas\PagSeguro\PagSeguroException
  */
  protected function validateSenderAddress($formattedSenderAddress)
  {
    $rules = [
      'shippingAddressStreet' => 'required|max:80',
      'shippingAddressNumber' => 'required|max:20',
      'shippingAddressComplement' => 'max:40',
      'shippingAddressDistrict' => 'required|max:60',
      'shippingAddressPostalCode' => 'required|digits:8',
      'shippingAddressCity' => 'required|min:2|max:60',
      'shippingAddressState' => 'required|min:2|max:2',
    ];

    $validator = $this->validator->make($formattedSenderAddress, $rules);

    if ($validator->fails()) {
      throw new PagSeguroException($validator->messages()->first());
    }

    return true;
  }
}
