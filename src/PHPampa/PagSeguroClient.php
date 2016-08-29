<?php

namespace PHPampa\PagSeguro;

class PagSeguroClient extends PagSeguroConfig
{
    /**
     * Executa as transações curl.
     *
     * @param array  $parameters
     * @param string $url        Padrão $this->url['transactions']
     *
     * @throws \PHPampa\PagSeguro\PagSeguroException
     *
     * @return bool|mixed|\SimpleXMLElement
     */
    public function sendTransaction(array $parameters, $url = null)
    {
        if ($url === null) {
            $url = $this->url['transactions'];
        }

        $data = '';
        foreach ($parameters as $key => $value) {
            $data .= $key.'='.$value.'&';
        }
        $data = rtrim($data, '&');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['application/x-www-form-urlencoded; charset=ISO-8859-1']);
        curl_setopt($ch, CURLOPT_POST, count($parameters));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if ($this->sandbox) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        }

        $result = curl_exec($ch);

        if ($result === false) {
            $this->log->error('Erro ao enviar a transação', ['Retorno:' => $result]);
            throw new PagSeguroException(curl_error($ch), curl_errno($ch));
        }
        if ($result === 'Unauthorized' || $result === 'Forbidden') {
            $this->log->error('Erro ao enviar a transação', ['Retorno:' => $result]);
            throw new PagSeguroException($result.': Não foi possível estabelecer uma conexão com o PagSeguro.', 1);
        }

        $result = simplexml_load_string($result);
        curl_close($ch);

        return $result;
    }

    /**
     * Inicia a Session do PagSeguro.
     *
     * @return string
     */
    public function startSession()
    {
        $result = $this->sendTransaction([
          'email' => $this->email,
          'token' => $this->token,
        ], $this->url['session']);

        $this->session->put('pagseguro.session', $result->id);

        return $result->id;
    }

    /**
     * Pega a sessão ou gera uma nova.
     *
     * @return string
     */
    public function getSession()
    {
        if ($this->session->has('pagseguro.session')) {
            return $this->session->get('pagseguro.session');
        } else {
            return $this->startSession();
        }
    }
}
