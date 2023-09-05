<?php

namespace Correios\Services\Tracking;

use Correios\Exceptions\ApiRequestException;
use Correios\Services\AbstractRequest;
use Correios\Services\Authorization\Authentication;

class Tracking extends AbstractRequest
{
    private $token;

    public function __construct(Authentication $authentication)
    {
        $this->authentication = $authentication;
        $this->setMethod('GET');
        $this->setEnvironment($this->authentication->getEnvironment());
        $this->buildHeaders();
    }

    private function buildEndpoint(string $trackingCode, string $filtered): void
    {
        $endpoint = 'srorastro/v1/objetos/' . $trackingCode;
        if ($filtered) {
            $endpoint .= '?resultado=U';
        }

        $this->setEndpoint($endpoint);
    }

    public function get(string $trackingCode, string $filtered = ''): array
    {
        try {
            $this->buildEndpoint($trackingCode, $filtered);
            $this->sendRequest();

            return [
                'code' => $this->getResponseCode(),
                'data' => $this->getResponseBody(),
            ];

        } catch (ApiRequestException $e) {
            $this->errors[$e->getCode()] = $e->getMessage();
            return [];
        }
    }

    private function buildHeaders(): void
    {
        $this->setHeaders([
            'Authorization' => 'Basic ' . $this->token,
        ]);
    }

}
