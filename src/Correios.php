<?php

namespace Correios;

use Correios\Services\{Address\Cep, Authorization\Authentication, Date\Date, Price\Price, Tracking\Tracking};

class Correios
{
    private Authentication $authentication;
    private string $requestNumber;
    private string $lotId;
    private string $postcard;
    private array $errors = [];

    public function __construct(string $username, string $password, string $postcard, bool $isTestMode = false, string $token = '')
    {
        $this->requestNumber = time();
        $this->postcard      = $postcard;

        $this->authenticate($username, $password, $postcard, $isTestMode, $token);
    }

    public function tracking(): Tracking
    {
        return new Tracking($this->authentication);
    }

    public function price(): Price
    {
        return new Price($this->authentication, $this->requestNumber);
    }

    public function date(): Date
    {
        return new Date($this->authentication, $this->requestNumber);
    }

    public function address(): Cep
    {
        return new Cep;
    }

    public function authentication(): Authentication
    {
        return $this->authentication;
    }

    private function authenticate(string $username, string $password, string $postcard, bool $isTestMode, string $token): void
    {
        $this->authentication = new Authentication($username, $password, $postcard, $isTestMode);
        if ($token) {
            $this->authentication->setToken($token);
            return;
        }
        $this->authentication->generateToken();
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function setRequestNumber(string $requestNumber): void
    {
        $this->requestNumber = $requestNumber;
    }

    public function setLotId(string $lotId): void
    {
        $this->lotId = $lotId;
    }
}
