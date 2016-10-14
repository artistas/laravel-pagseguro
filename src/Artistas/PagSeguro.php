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
        $senderEmail = $this->sandbox ? 'teste@sandbox.pagseguro.com.br' : $senderInfo['senderEmail'];

        $senderPhone = $this->sanitizeNumber($senderInfo, 'senderPhone');

        $senderInfo = [
          'senderName'     => $this->sanitize($senderInfo, 'senderName'),
          'senderAreaCode' => substr($senderPhone, 0, 2),
          'senderPhone'    => substr($senderPhone, 2),
          'senderEmail'    => $senderEmail,
          'senderHash'     => $this->checkValue($senderInfo, 'senderHash'),
          'senderCNPJ'     => $this->sanitizeNumber($senderInfo, 'senderCNPJ'),
          'senderCPF'      => $this->sanitizeNumber($senderInfo, 'senderCPF'),
        ];

        $this->validateSenderInfo($senderInfo);
        $this->senderInfo = $senderInfo;

        return $this;
    }

    /**
     * Valida os dados contidos na array de informações do comprador.
     *
     * @param array $senderInfo
     */
    private function validateSenderInfo(array $senderInfo)
    {
        $rules = [
          'senderName'     => 'required|max:50',
          'senderAreaCode' => 'required|digits:2',
          'senderPhone'    => 'required|digits_between:8,9',
          'senderEmail'    => 'required|email|max:60',
          'senderHash'     => 'required',
          'senderCPF'      => 'required_without:senderCNPJ|digits:11',
          'senderCNPJ'     => 'required_without:senderCPF|digits:14',
        ];

        $this->validate($senderInfo, $rules);
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
        $cardHolderPhone = $this->sanitizeNumber($creditCardHolder, 'creditCardHolderPhone');        

        $creditCardHolder = [
          'creditCardHolderName'          => $this->fallbackValue($this->sanitize($creditCardHolder, 'creditCardHolderName'), $this->senderInfo, 'senderName'),
          'creditCardHolderAreaCode'      => $this->fallbackValue(substr($cardHolderPhone, 0, 2), $this->senderInfo, 'senderAreaCode'),
          'creditCardHolderPhone'         => $this->fallbackValue(substr($cardHolderPhone, 2), $this->senderInfo, 'senderPhone'),
          'creditCardHolderCPF'           => $this->fallbackValue($this->sanitizeNumber($creditCardHolder, 'creditCardHolderCPF'), $this->senderInfo, 'senderCPF'),
          'creditCardHolderBirthDate'     => $this->sanitize($creditCardHolder, 'creditCardHolderBirthDate'),
        ];

        $this->validateCreditCardHolder($creditCardHolder);
        $this->creditCardHolder = $creditCardHolder;

        return $this;
    }

    /**
     * Valida os dados contidos na array de informações do portador do cartão de crédito.
     *
     * @param array $creditCardHolder
     */
    private function validateCreditCardHolder(array $creditCardHolder)
    {
        $rules = [
          'creditCardHolderName'         => 'required|max:50',
          'creditCardHolderAreaCode'     => 'required|digits:2',
          'creditCardHolderPhone'        => 'required|digits_between:8,9',
          'creditCardHolderCPF'          => 'required|digits:11',
          'creditCardHolderBirthDate'    => 'required',
        ];

        $this->validate($creditCardHolder, $rules);
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
        $shippingAddress = [
          'shippingAddressStreet'     => $this->sanitize($shippingAddress, 'shippingAddressStreet'),
          'shippingAddressNumber'     => $this->sanitize($shippingAddress, 'shippingAddressNumber'),
          'shippingAddressComplement' => $this->sanitize($shippingAddress, 'shippingAddressComplement'),
          'shippingAddressDistrict'   => $this->sanitize($shippingAddress, 'shippingAddressDistrict'),
          'shippingAddressPostalCode' => $this->sanitizeNumber($shippingAddress, 'shippingAddressPostalCode'),
          'shippingAddressCity'       => $this->sanitize($shippingAddress, 'shippingAddressCity'),
          'shippingAddressState'      => strtoupper($this->checkValue($shippingAddress, 'shippingAddressState')),
          'shippingAddressCountry'    => 'BRA',
        ];

        $this->validateShippingAddress($shippingAddress);
        $this->shippingAddress = $shippingAddress;

        return $this;
    }

    /**
     * Valida os dados contidos na array de endereço do comprador.
     *
     * @param array $shippingAddress
     */
    private function validateShippingAddress(array $shippingAddress)
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

        $this->validate($shippingAddress, $rules);
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
        $billingAddress = [
          'billingAddressStreet'     => $this->fallbackValue($this->sanitize($billingAddress, 'billingAddressStreet'), $this->shippingAddress, 'shippingAddressStreet'),
          'billingAddressNumber'     => $this->fallbackValue($this->sanitize($billingAddress, 'billingAddressNumber'), $this->shippingAddress, 'shippingAddressNumber'),
          'billingAddressComplement' => $this->fallbackValue($this->sanitize($billingAddress, 'billingAddressComplement'), $this->shippingAddress, 'shippingAddressComplement'),
          'billingAddressDistrict'   => $this->fallbackValue($this->sanitize($billingAddress, 'billingAddressDistrict'), $this->shippingAddress, 'shippingAddressDistrict'),
          'billingAddressPostalCode' => $this->fallbackValue($this->sanitizeNumber($billingAddress, 'billingAddressPostalCode'), $this->shippingAddress, 'shippingAddressPostalCode'),
          'billingAddressCity'       => $this->fallbackValue($this->sanitize($billingAddress, 'billingAddressCity'), $this->shippingAddress, 'shippingAddressCity'),
          'billingAddressState'      => strtoupper($this->fallbackValue($this->checkValue($billingAddress, 'billingAddressState'), $this->shippingAddress, 'shippingAddressState')),
          'billingAddressCountry'    => 'BRA',
        ];

        $this->validateBillingAddress($billingAddress);
        $this->billingAddress = $billingAddress;

        return $this;
    }

    /**
     * Valida os dados contidos na array de endereço do comprador.
     *
     * @param array $billingAddress
     */
    private function validateBillingAddress(array $billingAddress)
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

        $this->validate($billingAddress, $rules);
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
        $cont = 1;
        foreach ($items as $item) {
            $fItems['items'][$cont++] = [
              'itemId'          => $this->sanitize($item, 'itemId'),
              'itemDescription' => $this->sanitize($item, 'itemDescription'),
              'itemAmount'      => $this->sanitizeMoney($item, 'itemAmount'),
              'itemQuantity'    => $this->sanitizeNumber($item, 'itemQuantity'),
            ];
        }

        $this->validateItems($fItems);
        $this->items = $fItems;

        return $this;
    }

    /**
     * Valida os dados contidos na array de itens.
     *
     * @param array $items
     */
    private function validateItems($items)
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
            foreach ($items['items'] as $key => $item) {
                $rules = array_merge($rules, [
                  'items.'.$key.'.itemId'              => 'required|max:100',
                  'items.'.$key.'.itemDescription'     => 'required|max:100',
                  'items.'.$key.'.itemAmount'          => 'required|numeric|between:0.00,9999999.00',
                  'items.'.$key.'.itemQuantity'        => 'required|integer|between:1,999',
                ]);
            }
        }

        $this->validate($items, $rules);
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
        $this->extraAmount = $this->sanitizeMoney($extraAmount);

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
        $this->reference = $this->sanitize($reference);

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
        $shippingInfo = [
          'shippingType'     => $this->sanitizeNumber($shippingInfo, 'shippingType'),
          'shippingCost'     => $this->sanitizeMoney($shippingInfo, 'shippingCost'),
        ];

        $this->validateShippingInfo($shippingInfo);
        $this->shippingInfo = $shippingInfo;

        return $this;
    }

    /**
     * Valida os dados contidos no array de frete.
     *
     * @param array $shippingInfo
     */
    private function validateShippingInfo(array $shippingInfo)
    {
        $rules = [
          'shippingType'          => 'required|integer|between:1,3',
          'shippingCost'          => 'required|numeric|between:0.00,9999999.00',
        ];

        $this->validate($shippingInfo, $rules);
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
        if ($this->checkValue($paymentSettings, 'paymentMethod') === 'creditCard' && empty($this->billingAddress)) {
            $this->setBillingAddress([]);
        }

        $paymentSettings = [
          'paymentMethod'                 => $this->checkValue($paymentSettings, 'paymentMethod'),
          'bankName'                      => $this->checkValue($paymentSettings, 'bankName'),
          'creditCardToken'               => $this->checkValue($paymentSettings, 'creditCardToken'),
          'installmentQuantity'           => $this->sanitizeNumber($paymentSettings, 'installmentQuantity'),
          'installmentValue'              => $this->sanitizeMoney($paymentSettings, 'installmentValue'),
          'noInterestInstallmentQuantity' => $this->sanitizeNumber($paymentSettings, 'noInterestInstallmentQuantity'),
        ];

        $this->validatePaymentSettings($paymentSettings);

        $config = [
          'email'           => $this->email,
          'token'           => $this->token,
          'paymentMode'     => 'default',
          'receiverEmail'   => $this->email,
          'currency'        => 'BRL',
          'reference'       => $this->reference,
          'extraAmount'     => $this->extraAmount,
          'notificationURL' => $this->notificationURL,
        ];

        $data = array_filter(array_merge($config, $paymentSettings, $this->senderInfo, $this->shippingAddress, $this->items, $this->creditCardHolder, $this->billingAddress, $this->shippingInfo));

        return $this->sendTransaction($data);
    }

    /**
     * Valida os dados de pagamento.
     *
     * @param array $paymentSettings          
     *
     */
    private function validatePaymentSettings(array $paymentSettings)
    {
        $rules = [
          'paymentMethod'                          => 'required',
          'bankName'                               => 'required_if:paymentMethod,eft',
          'creditCardToken'                        => 'required_if:paymentMethod,creditCard',
          'installmentQuantity'                    => 'required_if:paymentMethod,creditCard|integer|between:1,18',
          'installmentValue'                       => 'required_if:paymentMethod,creditCard|numeric|between:0.00,9999999.00',
          'noInterestInstallmentQuantity'          => 'integer|between:1,18',
        ];

        $this->validate($paymentSettings, $rules);

        $this->validateSenderInfo($this->senderInfo);
        $this->validateShippingAddress($this->shippingAddress);
        $this->validateItems($this->items);

        if ($paymentSettings['paymentMethod'] === 'creditCard') {
            $this->validateCreditCardHolder($this->creditCardHolder);
            $this->validateBillingAddress($this->billingAddress);
        }

        if (!empty($this->shippingInfo)) {
            $this->validateShippingInfo($this->shippingInfo);
        }
    }

    /**
     * Valida os dados.
     *
     * @param array $data
     * @param array $rules
     *
     * @throws \Artistas\PagSeguro\PagSeguroException
     */
    private function validate($data, $rules) {
      $validator = $this->validator->make($data, $rules);

      if ($validator->fails()) {
          throw new PagSeguroException($validator->messages()->first());
      }
    }

    /**
     * Retorna a transação da notoficação.
     *
     * @param string $notificationCode
     *
     * @return \SimpleXMLElement
     */
    public function notification($notificationCode)
    {
        return $this->sendTransaction([
          'email' => $this->email,
          'token' => $this->token,
        ], $this->url['notifications'].$notificationCode, false);
    }
}
