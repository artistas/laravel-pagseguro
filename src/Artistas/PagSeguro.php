<?php

namespace Artistas\PagSeguro;

class PagSeguro extends PagSeguroClient
{
    /**
     * Informações do comprador.
     *
     * @var array
     */
    private $senderInfo = [];

    /**
     * Informações do portador do cartão de crédito.
     *
     * @var array
     */
    private $creditCardHolder = [];

    /**
     * Endereço do comprador.
     *
     * @var array
     */
    private $shippingAddress = [];

    /**
     * Endereço de cobrança do comprador.
     *
     * @var array
     */
    private $billingAddress = [];

    /**
     * Itens da compra.
     *
     * @var array
     */
    private $items = [];

    /**
     * Valor adicional para a compra.
     *
     * @var float
     */
    private $extraAmount;

    /**
     * Identificador da compra.
     *
     * @var string
     */
    private $reference;

    /**
     * Frete.
     *
     * @var array
     */
    private $shippingInfo = [];

    /**
     * Define o tipo do comprador.
     *
     * @param string $senderType
     *
     * @return $this
     */
    public function setSenderType($senderType)
    {
        $this->senderType = $senderType;

        return $this;
    }

    /**
     * Define os dados do comprador.
     *
     * @param array $senderInfo
     *
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

        @$formattedSenderInfo = [
          'senderName'     => trim(preg_replace('/\s+/', ' ', $senderInfo['senderName'])),
          'senderAreaCode' => substr($formattedSenderPhone, 0, 2),
          'senderPhone'    => substr($formattedSenderPhone, 2),
          'senderEmail'    => $formattedSenderEmail,
          'senderHash'     => $senderInfo['senderHash'],
          'senderCNPJ'     => preg_replace('/\D/', '', $senderInfo['senderCNPJ']),
          'senderCPF'      => preg_replace('/\D/', '', $senderInfo['senderCPF']),
        ];

        $this->validateSenderInfo($formattedSenderInfo);
        $this->senderInfo = $formattedSenderInfo;

        return $this;
    }

    /**
     * Valida os dados contidos na array de informações do comprador.
     *
     * @param array $formattedSenderInfo
     *
     * @throws \Artistas\PagSeguro\PagSeguroException
     */
    private function validateSenderInfo($formattedSenderInfo)
    {
        $rules = [
          'senderName'     => 'required|max:50',
          'senderAreaCode' => 'required|digits:2',
          'senderPhone'    => 'required|digits_between:8,9',
          'senderEmail'    => 'required|email|max:60',
          'senderHash'     => 'required',
          'senderCNPJ'     => 'required_if:senderCPF,|digits:14',
          'senderCPF'      => 'required_if:senderCNPJ,|digits:11',
        ];

        $validator = $this->validator->make($formattedSenderInfo, $rules);
        if ($validator->fails()) {
            throw new PagSeguroException($validator->messages()->first());
        }
    }

    /**
     * Define os dados do portador do cartão de crédito.
     *
     * @param array $creditCardHolder
     *
     * @return $this
     */
    public function setCreditCardHolder(array $creditCardHolder)
    {
        if (isset($creditCardHolder['creditCardHolderPhone'])) {
            $formattedcreditCardHolderPhone = preg_replace('/\D/', '', $creditCardHolder['creditCardHolderPhone']);
        }

        @$formattedcreditCardHolder = [
          'creditCardHolderName'          => $creditCardHolder['creditCardHolderName'] ? trim(preg_replace('/\s+/', ' ', $creditCardHolder['creditCardHolderName'])) : $this->senderInfo['senderName'],
          'creditCardHolderAreaCode'      => $formattedcreditCardHolderPhone ? substr($formattedcreditCardHolderPhone, 0, 2) : $this->senderInfo['senderAreaCode'],
          'creditCardHolderPhone'         => $formattedcreditCardHolderPhone ? substr($formattedcreditCardHolderPhone, 2) : $this->senderInfo['senderPhone'],
          'creditCardHolderCPF'           => $creditCardHolder['creditCardHolderCPF'] ? preg_replace('/\D/', '', $creditCardHolder['creditCardHolderCPF']) : $this->senderInfo['senderCPF'],
          'creditCardHolderBirthDate'     => trim(preg_replace('/\s+/', ' ', $creditCardHolder['creditCardHolderBirthDate'])),
        ];

        $this->validateCreditCardHolder($formattedcreditCardHolder);
        $this->creditCardHolder = $formattedcreditCardHolder;

        return $this;
    }

    /**
     * Valida os dados contidos na array de informações do portador do cartão de crédito.
     *
     * @param array $formattedcreditCardHolder
     *
     * @throws \Artistas\PagSeguro\PagSeguroException
     */
    private function validateCreditCardHolder($formattedcreditCardHolder)
    {
        $rules = [
          'creditCardHolderName'         => 'required|max:50',
          'creditCardHolderAreaCode'     => 'required|digits:2',
          'creditCardHolderPhone'        => 'required|digits_between:8,9',
          'creditCardHolderCPF'          => 'required|digits:11',
          'creditCardHolderBirthDate'    => 'required',
        ];

        $validator = $this->validator->make($formattedcreditCardHolder, $rules);
        if ($validator->fails()) {
            throw new PagSeguroException($validator->messages()->first());
        }
    }

    /**
     * Define o endereço do comprador.
     *
     * @param array $shippingAddress
     *
     * @return $this
     */
    public function setShippingAddress(array $shippingAddress)
    {
        @$formattedShippingAddress = [
          'shippingAddressStreet'     => trim(preg_replace('/\s+/', ' ', $shippingAddress['shippingAddressStreet'])),
          'shippingAddressNumber'     => trim(preg_replace('/\s+/', ' ', $shippingAddress['shippingAddressNumber'])),
          'shippingAddressComplement' => trim(preg_replace('/\s+/', ' ', $shippingAddress['shippingAddressComplement'])),
          'shippingAddressDistrict'   => trim(preg_replace('/\s+/', ' ', $shippingAddress['shippingAddressDistrict'])),
          'shippingAddressPostalCode' => preg_replace('/\D/', '', $shippingAddress['shippingAddressPostalCode']),
          'shippingAddressCity'       => trim(preg_replace('/\s+/', ' ', $shippingAddress['shippingAddressCity'])),
          'shippingAddressState'      => strtoupper($shippingAddress['shippingAddressState']),
          'shippingAddressCountry'    => 'BRA',
        ];

        $this->validateShippingAddress($formattedShippingAddress);
        $this->shippingAddress = $formattedShippingAddress;

        return $this;
    }

    /**
     * Valida os dados contidos na array de endereço do comprador.
     *
     * @param array $formattedShippingAddress
     *
     * @throws \Artistas\PagSeguro\PagSeguroException
     */
    private function validateShippingAddress($formattedShippingAddress)
    {
        $rules = [
          'shippingAddressStreet'     => 'required|max:80',
          'shippingAddressNumber'     => 'required|max:20',
          'shippingAddressComplement' => 'max:40',
          'shippingAddressDistrict'   => 'required|max:60',
          'shippingAddressPostalCode' => 'required|digits:8',
          'shippingAddressCity'       => 'required|min:2|max:60',
          'shippingAddressState'      => 'required|min:2|max:2',
        ];

        $validator = $this->validator->make($formattedShippingAddress, $rules);

        if ($validator->fails()) {
            throw new PagSeguroException($validator->messages()->first());
        }
    }

    /**
     * Define o endereço do comprador.
     *
     * @param array $billingAddress
     *
     * @return $this
     */
    public function setBillingAddress(array $billingAddress)
    {
        @$formattedBillingAddress = [
          'billingAddressStreet'     => $billingAddress['billingAddressStreet'] ? trim(preg_replace('/\s+/', ' ', $billingAddress['billingAddressStreet'])) : $this->shippingAddress['shippingAddressStreet'],
          'billingAddressNumber'     => $billingAddress['billingAddressNumber'] ? trim(preg_replace('/\s+/', ' ', $billingAddress['billingAddressNumber'])) : $this->shippingAddress['shippingAddressNumber'],
          'billingAddressComplement' => $billingAddress['billingAddressComplement'] ? trim(preg_replace('/\s+/', ' ', $billingAddress['billingAddressComplement'])) : $this->shippingAddress['shippingAddressComplement'],
          'billingAddressDistrict'   => $billingAddress['billingAddressDistrict'] ? trim(preg_replace('/\s+/', ' ', $billingAddress['billingAddressDistrict'])) : $this->shippingAddress['shippingAddressDistrict'],
          'billingAddressPostalCode' => $billingAddress['billingAddressPostalCode'] ? preg_replace('/\D/', '', $billingAddress['billingAddressPostalCode']) : $this->shippingAddress['shippingAddressPostalCode'],
          'billingAddressCity'       => $billingAddress['billingAddressCity'] ? trim(preg_replace('/\s+/', ' ', $billingAddress['billingAddressCity'])) : $this->shippingAddress['shippingAddressCity'],
          'billingAddressState'      => $billingAddress['billingAddressState'] ? strtoupper($billingAddress['billingAddressState']) : $this->shippingAddress['shippingAddressState'],
          'billingAddressCountry'    => 'BRA',
        ];

        $this->validateBillingAddress($formattedBillingAddress);
        $this->billingAddress = $formattedBillingAddress;

        return $this;
    }

    /**
     * Valida os dados contidos na array de endereço do comprador.
     *
     * @param array $formattedBillingAddress
     *
     * @throws \Artistas\PagSeguro\PagSeguroException
     */
    private function validateBillingAddress($formattedBillingAddress)
    {
        $rules = [
          'billingAddressStreet'     => 'required|max:80',
          'billingAddressNumber'     => 'required|max:20',
          'billingAddressComplement' => 'max:40',
          'billingAddressDistrict'   => 'required|max:60',
          'billingAddressPostalCode' => 'required|digits:8',
          'billingAddressCity'       => 'required|min:2|max:60',
          'billingAddressState'      => 'required|min:2|max:2',
        ];

        $validator = $this->validator->make($formattedBillingAddress, $rules);

        if ($validator->fails()) {
            throw new PagSeguroException($validator->messages()->first());
        }
    }

    /**
     * Define os itens da compra.
     *
     * @param array $items
     *
     * @return $this
     */
    public function setItems(array $items)
    {
        $i = 1;
        foreach ($items as $item) {
            @$formattedItems['items'][$i++] = [
              'itemId'          => trim(preg_replace('/\s+/', ' ', $item['itemId'])),
              'itemDescription' => trim(preg_replace('/\s+/', ' ', $item['itemDescription'])),
              'itemAmount'      => number_format($item['itemAmount'], 2, '.', ''),
              'itemQuantity'    => preg_replace('/\D/', '', $item['itemQuantity']),
            ];
        }

        $this->validateItems($formattedItems);
        $this->items = $formattedItems;

        return $this;
    }

    /**
     * Valida os dados contidos na array de itens.
     *
     * @param array $formattedItems
     *
     * @throws \Artistas\PagSeguro\PagSeguroException
     */
    private function validateItems($formattedItems)
    {
        $laravel = app();
        $version = $laravel::VERSION;

        if (substr($version, 0, 3) >= '5.2') {
            $rules = [
              'items.*.itemId'              => 'required|max:100',
              'items.*.itemDescription'     => 'required|max:100',
              'items.*.itemAmount'          => 'required|numeric|between:0.00,9999999.00',
              'items.*.itemQuantity'        => 'required|integer|between:1,999',
            ];
        } else {
            $rules = [];
            foreach ($formattedItems['items'] as $key => $item) {
                $rules = array_merge($rules, [
                  'items.'.$key.'.itemId'              => 'required|max:100',
                  'items.'.$key.'.itemDescription'     => 'required|max:100',
                  'items.'.$key.'.itemAmount'          => 'required|numeric|between:0.00,9999999.00',
                  'items.'.$key.'.itemQuantity'        => 'required|integer|between:1,999',
                ]);
            }
        }

        $validator = $this->validator->make($formattedItems, $rules);

        if ($validator->fails()) {
            throw new PagSeguroException($validator->messages()->first());
        }
    }

    /**
     * Define um valor adicional para a compra.
     *
     * @param float $extraAmount
     *
     * @return $this
     */
    public function setExtraAmount($extraAmount)
    {
        $this->extraAmount = number_format($extraAmount, 2, '.', '');

        return $this;
    }

    /**
     * Define um id de referência da compra no pagseguro.
     *
     * @param string $reference
     *
     * @return $this
     */
    public function setReference($reference)
    {
        $this->reference = trim(preg_replace('/\s+/', ' ', $reference));

        return $this;
    }

    /**
     * Define o valor e o tipo do frete cobrado.
     *
     * @param array $shippingInfo
     *
     * @return $this
     */
    public function setShippingInfo(array $shippingInfo)
    {
        @$formattedShippingInfo = [
          'shippingType'     => preg_replace('/\D/', '', $shippingInfo['shippingType']),
          'shippingCost'     => number_format($shippingInfo['shippingCost'], 2, '.', ''),
        ];

        $this->validateShippingInfo($formattedShippingInfo);
        $this->shippingInfo = $formattedShippingInfo;

        return $this;
    }

    /**
     * Valida os dados contidos no array de frete.
     *
     * @param array $formattedShippingInfo
     *
     * @throws \Artistas\PagSeguro\PagSeguroException
     */
    private function validateShippingInfo($formattedShippingInfo)
    {
        $rules = [
          'shippingType'          => 'required|integer|between:1,3',
          'shippingCost'          => 'required|numeric|between:0.00,9999999.00',
        ];

        $validator = $this->validator->make($formattedShippingInfo, $rules);

        if ($validator->fails()) {
            throw new PagSeguroException($validator->messages()->first());
        }
    }

    /**
     * Envia a transação de checkout.
     *
     * @param array $paymentOptions
     *
     * @return mixed
     */
    public function send(array $paymentSettings)
    {
        @$formattedPaymentSettings = [
          'paymentMethod'                 => $paymentSettings['paymentMethod'],
          'bankName'                      => $paymentSettings['bankName'],
          'creditCardToken'               => $paymentSettings['creditCardToken'],
          'installmentQuantity'           => preg_replace('/\D/', '', $paymentSettings['installmentQuantity']),
          'installmentValue'              => number_format($paymentSettings['installmentValue'], 2, '.', ''),
          'noInterestInstallmentQuantity' => preg_replace('/\D/', '', $paymentSettings['noInterestInstallmentQuantity']),
        ];

        $this->validatePaymentSettings($formattedPaymentSettings);

        $items = collect($this->items['items'])->flatMap(function ($values, $parentKey) {
            $laravel = app();
            $version = $laravel::VERSION;

            if (substr($version, 0, 3) >= '5.3') {
                return collect($values)->mapWithKeys(function ($value, $key) use ($parentKey) {
                    return [$key.$parentKey => $value];
                });
            }

            return collect($values)->flatMap(function ($value, $key) use ($parentKey) {
                return [$key.$parentKey => $value];
            });
        })->toArray();

        $config = [
          'email'         => $this->email,
          'token'         => $this->token,
          'paymentMode'   => 'default',
          'receiverEmail' => $this->email,
          'currency'      => 'BRL',
          'reference'     => $this->reference,
          'extraAmount'   => $this->extraAmount,
        ];

        $data = array_filter(array_merge($config, $formattedPaymentSettings, $this->senderInfo, $this->shippingAddress, $items, $this->creditCardHolder, $this->billingAddress, $this->shippingInfo));

        return $this->sendTransaction($data);
    }

    /**
     * Valida os dados de pagamento.
     *
     * @param array $formattedPaymentSettings
     *
     * @throws \Artistas\PagSeguro\PagSeguroException
     */
    private function validatePaymentSettings($formattedPaymentSettings)
    {
        $rules = [
          'paymentMethod'                          => 'required',
          'bankName'                               => 'required_if:paymentMethod,eft',
          'creditCardToken'                        => 'required_if:paymentMethod,creditCard',
          'installmentQuantity'                    => 'required_if:paymentMethod,creditCard|integer|between:1,18',
          'installmentValue'                       => 'required_if:paymentMethod,creditCard|numeric|between:0.00,9999999.00',
          'noInterestInstallmentQuantity'          => 'integer|between:1,3',
        ];

        $validator = $this->validator->make($formattedPaymentSettings, $rules);

        if ($validator->fails()) {
            throw new PagSeguroException($validator->messages()->first());
        }

        $this->validateSenderInfo($this->senderInfo);
        $this->validateShippingAddress($this->shippingAddress);
        $this->validateItems($this->items);

        if ($formattedPaymentSettings['paymentMethod'] === 'creditCard') {
            $this->validateCreditCardHolder($this->creditCardHolder);
            $this->validateBillingAddress($this->billingAddress);
        }

        if (!empty($this->shippingInfo)) {
            $this->validateShippingInfo($this->shippingInfo);
        }
    }
}
