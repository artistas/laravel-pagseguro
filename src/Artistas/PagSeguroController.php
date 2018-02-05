<?php

namespace Artistas\PagSeguro;

class PagSeguroController
{
    /** @var \Artistas\PagSeguro\PagSeguroClient */
    private $pagseguro;

    /**
     * Instancia as dependências.
     */
    public function __construct()
    {
        $this->pagseguro = app('pagseguro');
    }

    /**
     * Gera um token de sessão para realizar transações.
     *
     * @return string
     */
    public function session()
    {
        return $this->pagseguro->startSession();
    }

    /**
     * Inclui o arquivo javascript necessário para gerar o token no browser.
     *
     * @return \Illuminate\Http\Response
     */
    public function javascript()
    {
        $scriptContent = file_get_contents($this->pagseguro->getUrl()['javascript']);

        return response()->make($scriptContent, '200')
            ->header('Content-Type', 'text/javascript');
    }
}
