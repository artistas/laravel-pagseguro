<?php

namespace PHPampa\PagSeguro;

class PagSeguroClient extends PagSeguroConfig
{
	/**
     * Inicia o Session do PagSeguro
     * @return mixed
     * @throws \PHPampa\PagSeguro\PagSeguroException
     */
    public function startSession()
    {
        $credentials = 'email='.$this->email.'&token='.$this->token;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url['session']);
        curl_setopt($ch, CURLOPT_POST, 2);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $credentials);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if($this->sandbox) {            
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); 
        }
        $result = curl_exec($ch);
        
        if ($result === false)
            throw new PagSeguroException(curl_error($ch), curl_errno($ch));        
        if ($result === 'Unauthorized' || $result === 'Forbidden') {
            throw new PagSeguroException($result . ': Não foi possível estabelecer uma conexão com o PagSeguro.', 1);
        }

        $result = simplexml_load_string(curl_exec($ch));
        curl_close($ch);
        $result = json_decode(json_encode($result));

        $this->session->put('pagseguro.session', $result->id);

        return $result->id;
    }

    public function getSessionId()
    {
        if ($this->session->has('pagseguro.session')) {
            return $this->session->get('pagseguro.session');
        } else {
            return $this->startSession();
        }
    }
}
