<?php

namespace Artistas\PagSeguro;

use Illuminate\Config\Repository as Config;
use Illuminate\Log\Writer as Log;
use Illuminate\Session\SessionManager as Session;
use Illuminate\Validation\Factory as Validator;

class PagSeguroConfig
{
    /**
     * Session instance.
     *
     * @var object
     */
    protected $session;

    /**
     * Config instance.
     *
     * @var object
     */
    protected $config;

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
     * @param $session
     * @param $config
     * @param $log
     * @param $validator
     */
    public function __construct(Session $session, Config $config, Log $log, Validator $validator)
    {
        $this->session = $session;
        $this->config = $config;
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
        $this->sandbox = $this->config->get('pagseguro.sandbox');
        $this->token = $this->config->get('pagseguro.token');
        $this->email = $this->config->get('pagseguro.email');
        $this->notificationURL = $this->config->get('pagseguro.notificationURL');
    }

    /**
     * Define as Urls que serão utilizadas de acordo com o ambiente.
     */
    private function setUrl()
    {
        $sandbox = $this->sandbox ? 'sandbox.' : '';

        $url = [
            'session'       => 'https://ws.'.$sandbox.'pagseguro.uol.com.br/v2/sessions',
            'transactions'  => 'https://ws.'.$sandbox.'pagseguro.uol.com.br/v2/transactions',
            'notifications' => 'https://ws.'.$sandbox.'pagseguro.uol.com.br/v3/transactions/notifications/',
            'javascript'    => 'https://stc.'.$sandbox.'pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.directpayment.js',
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
