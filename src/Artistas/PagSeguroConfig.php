<?php

namespace Artistas\PagSeguro;

use Illuminate\Log\Writer as Log;
use Illuminate\Validation\Factory as Validator;

class PagSeguroConfig
{
    /**
     * Log instance.
     *
     * @var object
     */
    protected $log;

    /**
     * Validator instance.
     *
     * @var object
     */
    protected $validator;

    /**
     * Modo sandbox.
     *
     * @var bool
     */
    protected $sandbox;

    /**
     * Token da conta PagSeguro.
     *
     * @var string
     */
    protected $token;

    /**
     * Email da conta PagSeguro.
     *
     * @var string
     */
    protected $email;

    /**
     * Url de Notificação para o PagSeguro.
     *
     * @var string
     */
    protected $notificationURL;

    /**
     * Armazena as url's para conexão com o PagSeguro.
     *
     * @var array
     */
    protected $url = [];

    /**
     * @param $log
     * @param $validator
     */
    public function __construct(Log $log, Validator $validator)
    {
        $this->log = $log;
        $this->validator = $validator;
        $this->setEnvironment();
        $this->setUrl();
    }

    /**
     * Define o ambiente de trabalho.
     */
    private function setEnvironment()
    {
        $this->sandbox = env('PAGSEGURO_SANDBOX', true);
        $this->email = env('PAGSEGURO_EMAIL', '');
        $this->token = env('PAGSEGURO_TOKEN', '');
        $this->notificationURL = env('PAGSEGURO_NOTIFICATION', '');
    }

    /**
     * Define as Urls que serão utilizadas de acordo com o ambiente.
     */
    private function setUrl()
    {
        $sandbox = $this->sandbox ? 'sandbox.' : '';

        $url = [
            'preApprovalRequest'            => 'https://ws.'.$sandbox.'pagseguro.uol.com.br/v2/pre-approvals/request',
            'preApproval'                   => 'https://ws.'.$sandbox.'pagseguro.uol.com.br/pre-approvals',
            'preApprovalCancel'             => 'https://ws.'.$sandbox.'pagseguro.uol.com.br/v2/pre-approvals/cancel/',
            'session'                       => 'https://ws.'.$sandbox.'pagseguro.uol.com.br/v2/sessions',
            'transactions'                  => 'https://ws.'.$sandbox.'pagseguro.uol.com.br/v2/transactions',
            'notifications'                 => 'https://ws.'.$sandbox.'pagseguro.uol.com.br/v3/transactions/notifications/',
            'javascript'                    => 'https://stc.'.$sandbox.'pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.directpayment.js',
        ];

        $this->url = $url;
    }

    /**
     * Retorna o array de url's.
     */
    public function getUrl()
    {
        return $this->url;
    }
}
