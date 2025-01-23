<?php

namespace App\Traits;

use Exception;
use GuzzleHttp\Client;

trait ExternalServices
{
    public function viaCep($method = '', $url = '')
    {
        try {
            $client = new Client([
                'base_uri' => 'https://viacep.com.br/ws/',
            ]);

            $response = $client->request($method, $url, []);
            $response = $response->getBody()->getContents();

            return json_decode($response);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function republicaVirtual($method = '', $opt = [])
    {
        try {
            $client = new Client([
                'base_uri' => 'http://cep.republicavirtual.com.br/web_cep.php',
            ]);
            $response = $client->request($method, '', $opt);
            $response = $response->getBody()->getContents();

            return json_decode($response);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
