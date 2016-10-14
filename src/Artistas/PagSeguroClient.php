<?php

namespace Artistas\PagSeguro;

class PagSeguroClient extends PagSeguroConfig
{
    /**
     * Executa as transações curl.
     *
     * @param array  $parameters
     * @param string $url        Padrão $this->url['transactions']
     *
     * @throws \Artistas\PagSeguro\PagSeguroException
     *
     * @return bool|mixed|\SimpleXMLElement
     */
    public function sendTransaction(array $parameters, $url = null, $post = true)
    {        
        if ($url === null) {
            $url = $this->url['transactions'];
        }

        $parameters = formatParameters($parameters);        

        if (!$post) {
            $url .= '?'.$parameters;
            $parameters = null;
        }        

        return executeCurl($parameters, $url);
    }

    public function formatParameters($parameters) {
        $data = '';

        foreach ($parameters as $key => $value) {
            $data .= $key.'='.$value.'&';
        }

        return rtrim($data, '&');
    }

    public function executeCurl($parameters, $url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['application/x-www-form-urlencoded; charset=ISO-8859-1']);

        if ($parameters !== null) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $parameters);
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, !$this->sandbox);

        $result = curl_exec($curl);
        curl_close($curl);

        return formatResult($result, $curl);
    }

    public function formatResult($result, $curl) {
        if ($result === false) {
            $this->log->error('Erro ao enviar a transação', ['Retorno:' => $result]);
            throw new PagSeguroException(curl_error($curl), curl_errno($curl));
        }
        if ($result === 'Unauthorized' || $result === 'Forbidden') {
            $this->log->error('Erro ao enviar a transação', ['Retorno:' => $result]);
            throw new PagSeguroException($result.': Não foi possível estabelecer uma conexão com o PagSeguro.', 1);
        }
        if ($result === 'Not Found') {
            $this->log->error('Notificação/Transação não encontrada', ['Retorno:' => $result]);
            throw new PagSeguroException($result.': Não foi possível encontrar a notificação/transação no PagSeguro.', 1);
        }

        $result = simplexml_load_string($result);

        if (isset($result->error) && isset($result->error->message)) {
            throw new PagSeguroException($result->error->message, 1);
        }

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

        $this->session->put('pagseguro.session', (string) $result->id);

        return (string) $result->id;
    }

    /**
     * Pega a sessão ou gera uma nova.
     *
     * @return string
     */
    public function getSession()
    {
        return $this->session->has('pagseguro.session') ? $this->session->get('pagseguro.session') : $this->startSession();
    }
}
