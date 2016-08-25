<?php

namespace PHPampa\PagSeguro;

use Illuminate\Config\Repository as Config;
use Illuminate\Log\Writer as Log;
use Illuminate\Session\SessionManager as Session;

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
     * Armazena as url's para conexão com o PagSeguro.
     *
     * @var array
     */
    protected $url = [];

    /**
     * @param $session
     * @param $config
     * @param $log
     */
    public function __construct(Session $session, Config $config, Log $log)
    {
        $this->session = $session;
        $this->config = $config;
        $this->log = $log;
        $this->setEnvironment();
        $this->setUrl();
    }

    /**
     * define o ambiente de trabalho.
     */
    private function setEnvironment()
    {
        $this->sandbox = $this->config->get('pagseguro.sandbox');
        $this->token = $this->config->get('pagseguro.token');
        $this->email = $this->config->get('pagseguro.email');
    }

    private function setUrl()
    {
        $sandbox = '';
        if ($this->sandbox) {
            $sandbox = 'sandbox.';
        }
        $url = [
            'session'       => 'https://ws.'.$sandbox.'pagseguro.uol.com.br/v2/sessions',
            'transactions'  => 'https://ws.'.$sandbox.'pagseguro.uol.com.br/v2/transactions',
            'notifications' => 'https://ws.'.$sandbox.'pagseguro.uol.com.br/v3/transactions/notifications/',
            'javascript'    => 'https://stc.'.$sandbox.'pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.directpayment.js',
        ];
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }
}
