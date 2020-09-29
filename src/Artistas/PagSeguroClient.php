<?php

namespace Artistas\PagSeguro;

class PagSeguroClient extends PagSeguroConfig
{
    /**
     * Envia a transação HTML.
     *
     * @param array  $parameters
     * @param string $url        Padrão $this->url['transactions']
     * @param bool   $post
     * @param array  $headers
     *
     * @throws \Artistas\PagSeguro\PagSeguroException
     *
     * @return \SimpleXMLElement
     */
    protected function sendTransaction(array $parameters, $url = null, $post = true, array $headers = ['Content-Type: application/x-www-form-urlencoded; charset=ISO-8859-1'])
    {
        if ($url === null) {
            $url = $this->url['transactions'];
        }

        $parameters = $this->array_filter_recursive($parameters);

        $data = '';
        foreach ($parameters as $key => $value) {
            $data .= $key.'='.$value.'&';
        }
        $parameters = rtrim($data, '&');

        $method = 'POST';

        if (!$post) {
            $url .= '?'.$parameters;
            $parameters = null;
            $method = 'GET';
        }

        $result = $this->executeCurl($parameters, $url, $headers, $method);

        return $this->formatResult($result);
    }

    /**
     * Envia a transação JSON.
     *
     * @param array  $parameters
     * @param string $url        Padrão $this->url['transactions']
     * @param string $method
     * @param array  $headers
     *
     * @throws \Artistas\PagSeguro\PagSeguroException
     *
     * @return \SimpleXMLElement
     */
    protected function sendJsonTransaction(array $parameters, $url = null, $method = 'POST', array $headers = ['Accept: application/vnd.pagseguro.com.br.v3+json;charset=ISO-8859-1', 'Content-Type: application/json; charset=UTF-8'])
    {
        if ($url === null) {
            $url = $this->url['transactions'];
        }
        $url .= '?email='.$this->email.'&token='.$this->token;

        $parameters = $this->array_filter_recursive($parameters);

        array_walk_recursive($parameters, function (&$value, $key) {
            $value = utf8_encode($value);
        });
        $parameters = json_encode($parameters);

        if ($method == 'GET') {
            $parameters = null;
        }

        $result = $this->executeCurl($parameters, $url, $headers, $method);

        return $this->formatResultJson($result);
    }

    /**
     * Executa o Curl.
     *
     * @param array|string $parameters
     * @param string       $url
     * @param array        $headers
     * @param $method
     *
     * @throws PagSeguroException
     *
     * @return \SimpleXMLElement
     */
    private function executeCurl($parameters, $url, array $headers, $method)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        if ($method == 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
        } elseif ($method == 'PUT') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
        }

        if ($parameters !== null) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $parameters);
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, !$this->sandbox);

        $result = curl_exec($curl);

        $getInfo = curl_getinfo($curl);
        if (isset($getInfo['http_code']) && $getInfo['http_code'] == '503') {
            $this->log->error('Serviço em manutenção.', ['Retorno:' => $result]);

            throw new PagSeguroException('Serviço em manutenção.', 1000);
        }
        if ($result === false) {
            $this->log->error('Erro ao enviar a transação', ['Retorno:' => $result]);

            throw new PagSeguroException(curl_error($curl), curl_errno($curl));
        }

        curl_close($curl);

        return $result;
    }

    /**
     * Formata o resultado e trata erros.
     *
     * @param array $result
     *
     * @throws \Artistas\PagSeguro\PagSeguroException
     *
     * @return \SimpleXMLElement
     */
    private function formatResult($result)
    {
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
     * Formata o resultado e trata erros.
     *
     * @param array $result
     *
     * @throws \Artistas\PagSeguro\PagSeguroException
     *
     * @return mixed
     */
    private function formatResultJson($result)
    {
        if ($result === 'Unauthorized' || $result === 'Forbidden') {
            $this->log->error('Erro ao enviar a transação', ['Retorno:' => $result]);

            throw new PagSeguroException($result.': Não foi possível estabelecer uma conexão com o PagSeguro.', 1001);
        }
        if ($result === 'Not Found') {
            $this->log->error('Notificação/Transação não encontrada', ['Retorno:' => $result]);

            throw new PagSeguroException($result.': Não foi possível encontrar a notificação/transação no PagSeguro.', 1002);
        }

        $result = json_decode($result);

        if (isset($result->error) && $result->error === true) {
            $errors = $result->errors;

            $message = reset($errors);
            $code = key($errors);

            $this->log->error($message, ['Retorno:' => json_encode($result)]);

            throw new PagSeguroException($message, (int) $code);
        }

        return $result;
    }

    /**
     * Inicia a Session do PagSeguro.
     *
     * @throws PagSeguroException
     *
     * @return string
     */
    public function startSession()
    {
        return (string) $this->sendTransaction([
            'email' => $this->email,
            'token' => $this->token,
        ], $this->url['session'])->id;
    }

    /**
     * Retorna a transação da notificação.
     *
     * @param string $notificationCode
     * @param string $notificationType
     *
     * @throws PagSeguroException
     *
     * @return \SimpleXMLElement
     */
    public function notification($notificationCode, $notificationType = 'transaction')
    {
        if ($notificationType == 'transaction') {
            return $this->sendTransaction([
                'email' => $this->email,
                'token' => $this->token,
            ], $this->url['notifications'].$notificationCode, false);
        } elseif ($notificationType == 'preApproval') {
            return $this->sendTransaction([
                'email' => $this->email,
                'token' => $this->token,
            ], $this->url['preApprovalNotifications'].$notificationCode, false);
        }
    }

    /**
     * Valida os dados.
     *
     * @param array $data
     * @param array $rules
     *
     * @throws \Artistas\PagSeguro\PagSeguroException
     */
    protected function validate($data, $rules)
    {
        $data = array_filter($data);

        $validator = $this->validator->make($data, $rules);

        if ($validator->fails()) {
            throw new PagSeguroException($validator->messages()->first(), 1003);
        }
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
        if ($value != null) {
            if ($key !== null) {
                return isset($value[$key]) ? $value[$key] : null;
            }

            return $value;
        }
    }

    /**
     * Limpa um valor removendo espaços duplos.
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

        return $value == null ? null : utf8_decode(trim(preg_replace($regex, $replace, $value)));
    }

    /**
     * Limpa um valor deixando apenas números.
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
     * Limpa um valor deixando no formato de moeda.
     *
     * @param mixed  $value
     * @param string $key
     *
     * @return null|number
     */
    protected function sanitizeMoney($value, $key = null)
    {
        $value = $this->checkValue($value, $key);

        return $value == null ? $value : number_format($value, 2, '.', '');
    }

    /**
     * Verifica a existência de um valor, e utiliza outro caso necessário.
     *
     * @param mixed  $value
     * @param mixed  $fValue
     * @param string $fKey
     *
     * @return null|mixed
     */
    protected function fallbackValue($value, $fValue, $fKey)
    {
        return $value != null ? $value : $this->checkValue($fValue, $fKey);
    }

    /**
     * Aplica um array_filter recursivamente em um array.
     *
     * @param array $input
     *
     * @return array
     */
    protected function array_filter_recursive(array $input)
    {
        foreach ($input as &$value) {
            if (is_array($value)) {
                $value = $this->array_filter_recursive($value);
            }
        }

        return array_filter($input);
    }
}
