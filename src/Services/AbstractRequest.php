<?php

namespace Correios\Services;

use Correios\Exceptions\ApiRequestException;
use Correios\Services\Authorization\Authentication;
use stdClass;

abstract class AbstractRequest
{
    private array $body         = [];
    private array $headers      = [];
    private string $environment = 'production';
    protected array $errors     = [];
    protected int $responseCode = 0;
    protected object $responseBody;
    private string $method;
    private string $endpoint;
    protected Authentication $authentication;

    protected function sendRequest(): void
    {
        $url  = $this->getRequestUrl($this->endpoint);
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->getHeaders());
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        if ($this->method === 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($this->body));
        }

        $response = json_decode(curl_exec($curl), false);

        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        $data = is_object($response) ? $response : (object) $response;

        $this->responseBody = $data;
        $this->responseCode = $code;

        if ($code >= 400) {
            throw new ApiRequestException($data);
        }
    }

    protected function setAuthentication(Authentication $authentication): void
    {
        $this->$authentication = $authentication;
    }

    protected function getRequestUrl(string $endpoint): string
    {
        $isTestMode = $this->getEnvironment() === 'sandbox';

        if (isset($this->authentication)) {
            $isTestMode = $this->authentication->getEnvironment() === 'sandbox';
        }

        return settings()->getEnvironmentUrl($isTestMode) . "/$endpoint";
    }

    protected function getEnvironment(): string
    {
        return $this->environment;
    }

    protected function setEnvironment(string $environment): void
    {
        $this->environment = $environment;
    }

    protected function setBody(array $body): void
    {
        $this->body = $body;
    }

    protected function getBody(): array
    {
        return $this->body;
    }

    protected function setHeaders(array $headers): void
    {
        $this->headers = array_merge($headers, $this->headers);
    }

    protected function getHeaders(): array
    {
        $headers = [];

        if (isset($this->authentication)) {
            $this->headers['Authorization'] = 'Bearer ' . $this->authentication->getToken();
            $this->headers['Content-Type']  = 'application/json';
        }

        foreach ($this->headers as $key => $header) {
            $headers[] = "$key:$header";
        }

        return $headers;
    }

    protected function setMethod(string $method): void
    {
        $this->method = $method;
    }

    protected function setEndpoint(string $endpoint): void
    {
        $this->endpoint = $endpoint;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getResponseCode(): int
    {
        return $this->responseCode;
    }

    public function getResponseBody(): object
    {
        return $this->responseBody ?? new stdClass;
    }

}
