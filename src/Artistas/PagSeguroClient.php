<?php

namespace Artistas\PagSeguro;

class PagSeguroClient extends PagSeguroConfig
{
    /**
     * Envia a transação.
     *
     * @param array  $parameters
     * @param string $url        Padrão $this->url['transactions']
     * @param bool   $post
     *
     * @throws \Artistas\PagSeguro\PagSeguroException
     *
     * @return \SimpleXMLElement
     */
    protected function sendTransaction(array $parameters, $url = null, $post = true)
    {
        if ($url === null) {
            $url = $this->url['transactions'];
        }

        $parameters = $this->formatParameters($parameters);

        if (!$post) {
            $url .= '?'.$parameters;
            $parameters = null;
        }

        return $this->executeCurl($parameters, $url);
    }

    /**
     * Formata os parametros.
     *
     * @param array $parameters
     *
     * @return string
     */
    private function formatParameters($parameters)
    {
        $data = '';

        foreach ($parameters as $key => $value) {
            $data .= $key.'='.$value.'&';
        }

        return rtrim($data, '&');
    }

    /**
     * Executa o Curl.
     *
     * @param array  $parameters
     * @param string $url
     *
     * @return \SimpleXMLElement
     */
    private function executeCurl($parameters, $url)
    {
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
        $result = $this->formatResult($result, $curl);
        curl_close($curl);

        return $result;
    }

    /**
     * Formata o resultado e trata erros.
     *
     * @param array  $result
     * @param object $curl
     *
     * @throws \Artistas\PagSeguro\PagSeguroException
     *
     * @return \SimpleXMLElement
     */
    private function formatResult($result, $curl)
    {
        $getInfo = curl_getinfo($curl);

        if (isset($getInfo['http_code']) && $getInfo['http_code'] == '503') {
            $this->log->error('Serviço em manutenção.', ['Retorno:' => $result]);
            throw new PagSeguroException('Serviço em manutenção.', 1000);
        }
        if ($result === false) {
            $this->log->error('Erro ao enviar a transação', ['Retorno:' => $result]);
            throw new PagSeguroException(curl_error($curl), curl_errno($curl));
        }
        if ($result === 'Unauthorized' || $result === 'Forbidden') {
            $this->log->error('Erro ao enviar a transação', ['Retorno:' => $result]);
            throw new PagSeguroException($result.': Não foi possível estabelecer uma conexão com o PagSeguro.', 1001);
        }
        if ($result === 'Not Found') {
            $this->log->error('Notificação/Transação não encontrada', ['Retorno:' => $result]);
            throw new PagSeguroException($result.': Não foi possível encontrar a notificação/transação no PagSeguro.', 1002);
        }

        $result = simplexml_load_string($result);

        if (isset($result->error) && isset($result->error->message)) {
            $this->log->error($result->error->message, ['Retorno:' => $result]);
            throw new PagSeguroException($result->error->message, (int) $result->error->code);
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

    /**
     * Verifica a existência de um valor.
     *
     * @param mixed  $value
     * @param string $key
     *
     * @return null|mixed
     */
    protected function checkValue($value, $key = null)
    {
        if ($value !== null) {
            if ($key !== null) {
                return isset($value[$key]) ? $value[$key] : null;
            }

            return $value;
        }
    }

    /**
     * Verifica a existência de um valor.
     *
     * @param mixed  $value
     * @param string $key
     * @param string $regex
     * @param string $replace
     *
     * @return null|mixed
     */
    protected function sanitize($value, $key = null, $regex = '/\s+/', $replace = ' ')
    {
        $value = $this->checkValue($value, $key);

        return $value === null ? $value : trim(preg_replace($regex, $replace, $value));
    }

    /**
     * Verifica a existência de um valor.
     *
     * @param mixed  $value
     * @param string $key
     *
     * @return null|mixed
     */
    protected function sanitizeNumber($value, $key = null)
    {
        return $this->sanitize($value, $key, '/\D/', '');
    }

    /**
     * Verifica a existência de um valor.
     *
     * @param mixed  $value
     * @param string $key
     *
     * @return null|number
     */
    protected function sanitizeMoney($value, $key = null)
    {
        $value = $this->checkValue($value, $key);

        return $value === null ? $value : number_format($value, 2, '.', '');
    }

    /**
     * Verifica a existência de um valor.
     *
     * @param mixed  $value
     * @param mixed  $fValue
     * @param string $fKey
     *
     * @return null|mixed
     */
    protected function fallbackValue($value, $fValue, $fKey)
    {
        return $value !== null ? $value : $this->checkValue($fValue, $fKey);
    }
}
