<?php

namespace Artistas\PagSeguro;

class PagSeguroController
{
    /**
     * Gera um token de sessão para realizar transações.
     *
     * @return string
     */
    public function session()
    {
        return PagSeguroFacade::startSession();
    }

    /**
     * Inclui o arquivo javascript necessário para gerar o token no browser.
     *
     * @return \Illuminate\Http\Response
     */
    public function javascript()
    {
        $scriptContent = file_get_contents(PagSeguroFacade::getUrl()['javascript']);

        return response()->make($scriptContent, '200')
            ->header('Content-Type', 'text/javascript');
    }
}
