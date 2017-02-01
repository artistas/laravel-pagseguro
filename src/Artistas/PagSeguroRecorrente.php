<?php

namespace Artistas\PagSeguro;

class PagSeguroRecorrente extends PagSeguroClient
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
    private $senderAddress = [];

    /**
     * Endereço de cobrança do comprador.
     *
     * @var array
     */
    private $billingAddress = [];

    /**
     * Identificador da compra.
     *
     * @var string
     */
    private $reference;

    /**
     * Identificador do plano.
     *
     * @var string
     */
    private $plan;

    /**
     * Define os dados do plano.
     *
     * @param array $preApprovalRequest
     *
     * @return \SimpleXMLElement
     */
    public function sendPreApprovalRequest(array $preApprovalRequest)
    {
        $preApprovalRequest = [
            'email'                             => $this->email,
            'token'                             => $this->token,
            'preApprovalName'                   => $this->sanitize($preApprovalRequest, 'preApprovalName'),
            'preApprovalCharge'                 => $this->sanitize($preApprovalRequest, 'preApprovalCharge'),
            'preApprovalPeriod'                 => $this->sanitize($preApprovalRequest, 'preApprovalPeriod'),
            'preApprovalCancelUrl'              => $this->sanitize($preApprovalRequest, 'preApprovalCancelUrl'),
            'preApprovalAmountPerPayment'       => $this->sanitizeMoney($preApprovalRequest, 'preApprovalAmountPerPayment'),
            'preApprovalMembershipFee'          => $this->sanitizeMoney($preApprovalRequest, 'preApprovalMembershipFee'),
            'preApprovalTrialPeriodDuration'    => $this->sanitizeNumber($preApprovalRequest, 'preApprovalTrialPeriodDuration'),
            'preApprovalExpirationValue'        => $this->sanitizeNumber($preApprovalRequest, 'preApprovalExpirationValue'),
            'preApprovalExpirationUnit'         => $this->sanitize($preApprovalRequest, 'preApprovalExpirationUnit'),
            'maxUses'                           => $this->sanitizeNumber($preApprovalRequest, 'maxUses'),
        ];

        $this->validatePreApprovalRequest($preApprovalRequest);

        return (string) $this->sendTransaction($preApprovalRequest, $this->url['preApprovalRequest'])->code;
    }

    /**
     * Valida os dados contidos na array de criação de um plano.
     *
     * @param array $preApprovalRequest
     */
    private function validatePreApprovalRequest(array $preApprovalRequest)
    {
        $rules = [
            'preApprovalName'                   => 'required',
            'preApprovalCharge'                 => 'required',
            'preApprovalPeriod'                 => 'required',
            'preApprovalCancelUrl'              => 'url',
            'preApprovalAmountPerPayment'       => 'required|numeric|between:1.00,2000.00',
            'preApprovalMembershipFee'          => 'numeric|between:0.00,1000000.00',
            'preApprovalTrialPeriodDuration'    => 'integer|between:1,1000000',
            'preApprovalExpirationValue'        => 'integer|between:1,1000000',
            'maxUses'                           => 'integer|between:1,1000000',
        ];

        $this->validate($preApprovalRequest, $rules);
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
     * Define um id de um plano no pagseguro.
     *
     * @param string $plan
     *
     * @return $this
     */
    public function setPlan($plan)
    {
        $this->plan = $this->sanitize($plan);

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
        $senderEmail = $this->sandbox ? 'teste@sandbox.pagseguro.com.br' : $this->sanitize($senderInfo, 'senderEmail');

        $senderPhone = $this->sanitizeNumber($senderInfo, 'senderPhone');

        $senderInfo = [
            'name'           => $this->sanitize($senderInfo, 'senderName'),
            'email'          => $senderEmail,
            'ip'             => $this->sanitize($senderInfo, 'senderIp'),
            'hash'           => $this->checkValue($senderInfo, 'senderHash'),
            'senderAreaCode' => substr($senderPhone, 0, 2),
            'senderPhone'    => substr($senderPhone, 2),
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
          'name'           => 'required|max:50',
          'ip'             => 'ip',
          'senderAreaCode' => 'required|digits:2',
          'senderPhone'    => 'required|digits_between:8,9',
          'email'          => 'required|email|max:60',
          'hash'           => 'required',
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
          'name'                          => $this->fallbackValue($this->sanitize($creditCardHolder, 'creditCardHolderName'), $this->senderInfo, 'name'),
          'creditCardHolderAreaCode'      => $this->fallbackValue(substr($cardHolderPhone, 0, 2), $this->senderInfo, 'senderAreaCode'),
          'creditCardHolderPhone'         => $this->fallbackValue(substr($cardHolderPhone, 2), $this->senderInfo, 'senderPhone'),
          'creditCardHolderCPF'           => $this->fallbackValue($this->sanitizeNumber($creditCardHolder, 'creditCardHolderCPF'), $this->senderInfo, 'senderCPF'),
          'birthDate'                     => $this->sanitize($creditCardHolder, 'creditCardHolderBirthDate'),
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
          'name'                         => 'required|max:50',
          'creditCardHolderAreaCode'     => 'required|digits:2',
          'creditCardHolderPhone'        => 'required|digits_between:8,9',
          'creditCardHolderCPF'          => 'required|digits:11',
          'birthDate'                    => 'required',
        ];

        $this->validate($creditCardHolder, $rules);
    }

    /**
     * Define o endereço do comprador.
     *
     * @param array $senderAddress
     *
     * @return $this
     */
    public function setSenderAddress(array $senderAddress)
    {
        $senderAddress = [
          'street'     => $this->sanitize($senderAddress, 'senderAddressStreet'),
          'number'     => $this->sanitize($senderAddress, 'senderAddressNumber'),
          'complement' => $this->sanitize($senderAddress, 'senderAddressComplement'),
          'district'   => $this->sanitize($senderAddress, 'senderAddressDistrict'),
          'postalCode' => $this->sanitizeNumber($senderAddress, 'senderAddressPostalCode'),
          'city'       => $this->sanitize($senderAddress, 'senderAddressCity'),
          'state'      => strtoupper($this->checkValue($senderAddress, 'senderAddressState')),
          'country'    => 'BRA',
        ];

        $this->validateSenderAddress($senderAddress);
        $this->senderAddress = $senderAddress;

        return $this;
    }

    /**
     * Valida os dados contidos na array de endereço do comprador.
     *
     * @param array $senderAddress
     */
    private function validateSenderAddress(array $senderAddress)
    {
        $rules = [
          'street'     => 'required|max:80',
          'number'     => 'required|max:20',
          'complement' => 'max:40',
          'district'   => 'required|max:60',
          'postalCode' => 'required|digits:8',
          'city'       => 'required|min:2|max:60',
          'state'      => 'required|min:2|max:2',
        ];

        $this->validate($senderAddress, $rules);
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
          'street'     => $this->fallbackValue($this->sanitize($billingAddress, 'billingAddressStreet'), $this->senderAddress, 'street'),
          'number'     => $this->fallbackValue($this->sanitize($billingAddress, 'billingAddressNumber'), $this->senderAddress, 'number'),
          'complement' => $this->fallbackValue($this->sanitize($billingAddress, 'billingAddressComplement'), $this->senderAddress, 'complement'),
          'district'   => $this->fallbackValue($this->sanitize($billingAddress, 'billingAddressDistrict'), $this->senderAddress, 'district'),
          'postalCode' => $this->fallbackValue($this->sanitizeNumber($billingAddress, 'billingAddressPostalCode'), $this->senderAddress, 'postalCode'),
          'city'       => $this->fallbackValue($this->sanitize($billingAddress, 'billingAddressCity'), $this->senderAddress, 'city'),
          'state'      => strtoupper($this->fallbackValue($this->checkValue($billingAddress, 'billingAddressState'), $this->senderAddress, 'state')),
          'country'    => 'BRA',
        ];

        $this->validateBillingAddress($billingAddress);
        $this->billingAddress = $billingAddress;

        return $this;
    }

    /**
     * Valida os dados contidos na array de endereço de cobrança do comprador.
     *
     * @param array $billingAddress
     */
    private function validateBillingAddress(array $billingAddress)
    {
        $rules = [
          'street'     => 'required|max:80',
          'number'     => 'required|max:20',
          'complement' => 'max:40',
          'district'   => 'required|max:60',
          'postalCode' => 'required|digits:8',
          'city'       => 'required|min:2|max:60',
          'state'      => 'required|min:2|max:2',
        ];

        $this->validate($billingAddress, $rules);
    }

    /**
     * Cria um pagamento recorrente.
     *
     * @param array $paymentSettings
     *
     * @return mixed
     */
    public function sendPreApproval(array $paymentSettings)
    {
        if (empty($this->billingAddress)) {
            $this->setBillingAddress([]);
        }
        $this->validatePaymentSettings($paymentSettings);

        $data = $this->formatPreApprovalData($paymentSettings);

        return (string) $this->sendJsonTransaction($data, $this->url['preApproval'])->code;
    }

    /**
     * Valida os dados de pagamento.
     *
     * @param array $paymentSettings
     */
    private function validatePaymentSettings(array $paymentSettings)
    {
        $rules = [
          'creditCardToken' => 'required',
        ];

        $this->validate($paymentSettings, $rules);

        $this->validateSenderInfo($this->senderInfo);
        $this->validateCreditCardHolder($this->creditCardHolder);
        $this->validateSenderAddress($this->senderAddress);
        $this->validateBillingAddress($this->billingAddress);
    }

    /**
     * Formata os dados para enviar.
     *
     * @param array $paymentSettings
     *
     * @return array
     */
    private function formatPreApprovalData(array $paymentSettings)
    {
        $this->senderInfo['phone'] = [
            'areaCode' => $this->senderInfo['senderAreaCode'],
            'number'   => $this->senderInfo['senderPhone'],
        ];

        $this->creditCardHolder['phone'] = [
            'areaCode' => $this->creditCardHolder['creditCardHolderAreaCode'],
            'number'   => $this->creditCardHolder['creditCardHolderPhone'],
        ];

        if (!empty($this->senderInfo['senderCPF'])) {
            $this->senderInfo['documents'][0] = [
                'type'  => 'CPF',
                'value' => $this->senderInfo['senderCPF'],
            ];

            unset($this->senderInfo['senderCPF']);
        } else {
            $this->senderInfo['documents'][0] = [
                'type'  => 'CNPJ',
                'value' => $this->senderInfo['senderCNPJ'],
            ];

            unset($this->senderInfo['senderCNPJ']);
        }

        $this->creditCardHolder['documents'][0] = [
            'type'  => 'CPF',
            'value' => $this->creditCardHolder['creditCardHolderCPF'],
        ];

        unset($this->creditCardHolder['creditCardHolderCPF']);
        unset($this->senderInfo['senderAreaCode']);
        unset($this->senderInfo['senderPhone']);
        unset($this->creditCardHolder['creditCardHolderAreaCode']);
        unset($this->creditCardHolder['creditCardHolderPhone']);

        $data = [
            'reference'     => $this->reference,
            'plan'          => $this->plan,
            'sender'        => $this->senderInfo,
            'paymentMethod' => [
                'type'       => 'CREDITCARD',
                'creditCard' => [
                    'token'  => $paymentSettings['creditCardToken'],
                    'holder' => $this->creditCardHolder,
                ],
            ],
        ];
        $data['sender']['address'] = $this->senderAddress;
        $data['paymentMethod']['creditCard']['holder']['billingAddress'] = $this->billingAddress;

        return $data;
    }

    /**
     * Cancela um pagamento recorrente.
     *
     * @param string $preApprovalCode
     *
     * @return \SimpleXMLElement
     */
    public function cancelPreApproval($preApprovalCode)
    {
        return $this->sendTransaction([
            'email' => $this->email,
            'token' => $this->token,
        ], $this->url['preApprovalCancel'].$preApprovalCode, false);
    }
}
