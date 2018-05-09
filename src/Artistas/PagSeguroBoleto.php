<?php

namespace Artistas\PagSeguro;

class PagSeguroBoleto extends PagSeguroClient
{
    /**
     * Identificador da compra.
     *
     * @var string
     */
    private $reference;

    /**
     * Data de vencimento do boleto.
     *
     * @var string
     */
    private $firstDueDate;

    /**
     * Quantidade de boletos gerados.
     *
     * @var string
     */
    private $numberOfPayments;

    /**
     * Valor da compra.
     *
     * @var string
     */
    private $amount;

    /**
     * Instruções do boleto.
     *
     * @var string
     */
    private $instructions;

    /**
     * Descrição do item do boleto.
     *
     * @var string
     */
    private $description;

    /**
     * Informações do comprador.
     *
     * @var array
     */
    private $custumer = [];

    /**
     * Define os dados do comprador.
     *
     * @param array $customerInfo
     *
     * @throws \Artistas\PagSeguro\PagSeguroException
     *
     * @return PagSeguroBoleto
     */
    public function setCustomerInfo(array $customerInfo)
    {
        $customerEmail = $this->sanitize($customerInfo, 'email');

        $customerPhone = $this->sanitizeNumber($customerInfo, 'phone');

        $this->custumer['customer'] = [
            'name'      => $this->sanitize($customerInfo, 'name'),
            'email'     => $customerEmail,
        ];

        $this->custumer['customer']['document'] = [
            'type'   => !empty($this->sanitizeNumber($customerInfo, 'customerCPF')) ? 'CPF' : 'CNPJ',
            'value'  => !empty($this->sanitizeNumber($customerInfo, 'customerCPF')) ? $this->sanitizeNumber($customerInfo, 'customerCPF') : $this->sanitizeNumber($customerInfo, 'customerCNPJ'),
        ];

        $this->custumer['customer']['phone'] = [
            'areaCode'  => substr($customerPhone, 0, 2),
            'number'    => substr($customerPhone, 2),
        ];

        $this->validateCustomerInfo($this->custumer['customer']);

        return $this;
    }

    /**
     * Valida os dados contidos na array de informações do comprador.
     *
     * @param array $customerInfo
     *
     * @throws \Artistas\PagSeguro\PagSeguroException
     */
    private function validateCustomerInfo(array $customerInfo)
    {
        if ($customerInfo['document']['type'] == 'CPF') {
            $type = 'required|digits:11';
        } else {
            $type = 'required|digits:14';
        }

        $rules = [
            'name'              => 'required|max:50',
            'phone.areaCode'    => 'required|digits:2',
            'phone.number'      => 'required|digits_between:8,9',
            'email'             => 'required|email|max:60',
            'document.value'    => $type,
        ];

        $this->validate($customerInfo, $rules);
    }

    /**
     * Define os dados de endereço do comprador.
     *
     * @param array $customerAddress
     *
     * @throws \Artistas\PagSeguro\PagSeguroException
     *
     * @return PagSeguroBoleto
     */
    public function setCustomerAddress(array $customerAddress)
    {
        $this->custumer['address'] = [
            'street'     => $this->sanitize($customerAddress, 'street'),
            'number'     => $this->sanitize($customerAddress, 'number'),
            'complement' => $this->sanitize($customerAddress, 'complement'),
            'district'   => $this->sanitize($customerAddress, 'district'),
            'postalCode' => $this->sanitizeNumber($customerAddress, 'postalCode'),
            'city'       => $this->sanitize($customerAddress, 'city'),
            'state'      => strtoupper($this->checkValue($customerAddress, 'state')),
        ];

        $this->validateCustomerAddress($this->custumer['address']);

        return $this;
    }

    /**
     *  Valida os dados contidos na array de endereço do comprador.
     *
     * @param array $customerAddress
     *
     * @throws \Artistas\PagSeguro\PagSeguroException
     */
    private function validateCustomerAddress(array $customerAddress)
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

        $this->validate($customerAddress, $rules);
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
     * Define o valor da compra no pagseguro.
     *
     * @param string $amount
     *
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $this->sanitizeMoney($amount);

        return $this;
    }

    /**
     * Define o vendimento do boleto da compra no pagseguro.
     *
     * @param \Carbon\Carbon $firstDueDate
     *
     * @return $this
     */
    public function setFirstDueDate($firstDueDate)
    {
        $this->firstDueDate = $firstDueDate->format('Y-m-d');

        return $this;
    }

    /**
     * Define a quantidade de boletos gerados no pagseguro.
     *
     * @param string $numberOfPayments
     *
     * @return $this
     */
    public function setNumberOfPayments($numberOfPayments)
    {
        $this->numberOfPayments = $this->sanitizeNumber($numberOfPayments);

        return $this;
    }

    /**
     * Define a do item do boleto no pagseguro.
     *
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $this->sanitize($description);

        return $this;
    }

    /**
     * Define a instrução do boleto no pagseguro.
     *
     * @param string $instructions
     *
     * @return $this
     */
    public function setInstructions($instructions)
    {
        $this->instructions = $this->sanitize($instructions);

        return $this;
    }

    /**
     * Envia o boleto para o pagseguro.
     *
     * @throws \Artistas\PagSeguro\PagSeguroException
     *
     * @return \SimpleXMLElement
     */
    public function send()
    {
        if (empty($this->firstDueDate)) {
            self::setFirstDueDate(\Carbon\Carbon::now()->addDays(3));
        }

        if (empty($this->numberOfPayments)) {
            self::setNumberOfPayments(1);
        }

        $this->validatePaymentSettings();

        $config = [
            'reference'         => $this->reference,
            'amount'            => $this->amount,
            'notificationURL'   => $this->notificationURL,
            'description'       => $this->description,
            'instructions'      => $this->instructions,
            'periodicity'       => 'monthly',
            'numberOfPayments'  => $this->numberOfPayments,
            'firstDueDate'      => $this->firstDueDate,
        ];

        $custumerArray = array_merge($this->custumer['customer'], ['address' => $this->custumer['address']]);

        $data = array_filter(array_merge($config, ['customer' => $custumerArray]));

        return $this->sendJsonTransaction($data, $this->url['boletos'], 'POST', ['Accept: application/json;charset=ISO-8859-1', 'Content-type: application/json;charset=ISO-8859-1']);
    }

    /**
     * Valida os dados de pagamento.
     *
     * @throws \Artistas\PagSeguro\PagSeguroException
     */
    private function validatePaymentSettings()
    {
        $this->validateCustomerInfo($this->custumer['customer']);
        $this->validateCustomerAddress($this->custumer['address']);
    }
}
