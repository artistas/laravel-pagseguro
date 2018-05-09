<?php

namespace Artistas\PagSeguro;

use Illuminate\Validation\Factory as Validator;
use Psr\Log\LoggerInterface as Log;

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
        $this->sandbox = config('pagseguro.sandbox', env('PAGSEGURO_SANDBOX', true));
        $this->email = config('pagseguro.email', env('PAGSEGURO_EMAIL', ''));
        $this->token = config('pagseguro.token', env('PAGSEGURO_TOKEN', ''));
        $this->notificationURL = config('pagseguro.notificationURL', env('PAGSEGURO_NOTIFICATION', ''));
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
            'cancelTransaction'             => 'https://ws.'.$sandbox.'pagseguro.uol.com.br/v2/transactions/cancels',
            'preApprovalNotifications'      => 'https://ws.'.$sandbox.'pagseguro.uol.com.br/v2/pre-approvals/notifications/',
            'session'                       => 'https://ws.'.$sandbox.'pagseguro.uol.com.br/v2/sessions',
            'transactions'                  => 'https://ws.'.$sandbox.'pagseguro.uol.com.br/v2/transactions',
            'notifications'                 => 'https://ws.'.$sandbox.'pagseguro.uol.com.br/v3/transactions/notifications/',
            'javascript'                    => 'https://stc.'.$sandbox.'pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.directpayment.js',
            'boletos'                       => 'https://ws.pagseguro.uol.com.br/recurring-payment/boletos',
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
