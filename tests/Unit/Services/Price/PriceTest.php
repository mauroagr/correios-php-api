<?php

use Correios\Includes\Settings;
use Correios\Services\Price\Price;
use Correios\Services\Authorization\Authentication;
use function Pest\Faker\fake;

$settings     = new Settings();
$serviceCodes = array_keys($settings->getServiceCodes());
$serviceCode  = $serviceCodes[fake()->numberBetween(0, count($serviceCodes) - 1)];
$originCep    = fake()->regexify('[0-9]{8}');
$destinyCep   = fake()->regexify('[0-9]{8}');
$contract     = fake()->regexify('[0-9]{10}');
$dr           = fake()->numberBetween(1,99);

$authentication = new Authentication(
    fake()->userName(),
    fake()->regexify('[0-9]{10}'),
    fake()->password(),
    true
);

$price = new Price($authentication, time());

dataset('authentication', [$authentication]);
dataset('price', [$price]);
dataset('serviceCode', [$serviceCode]);
dataset('originCep', [$originCep]);
dataset('destinyCep', [$destinyCep]);
dataset('contract', [$contract]);
dataset('dr', [$dr]);

test('It should be possible to instance the Price class without generate any errors', function(Authentication $authentication) {
    $price = new Price($authentication, time());
    expect($price)
        ->toBeInstanceOf(Price::class);
})->with('authentication');

describe('get() method', function() {
    test('It should be possible to use the get() method without generate any Exception', function(Price $price, string $serviceCode, string $destinyCep) {
        expect(
            fn() => $price->get(
                [$serviceCode],
                [['weight' => fake()->randomFloat(1,1, 1000)]],
                fake()->regexify('[0-9]{7}'),
                $destinyCep
            )
        )->toThrow(\Correios\Exceptions\InvalidCepException::class);

    })->with('price', 'serviceCode', 'destinyCep');

    test('The get() method must to return an array', function(Price $price, string $serviceCode, string $originCep, string $destinyCep) {
        $response = $price->get(
            [$serviceCode],
            [['weight' => fake()->randomFloat(1,1, 1000)]],
            $originCep,
            $destinyCep
        );
        expect($response)
            ->toBeArray();

    })->with('price', 'serviceCode', 'originCep', 'destinyCep');
});

describe('getErrors() method', function() {
    test('It should be possible to access the errors property using the getErrors() method', function(Price $price) {
        expect($price->getErrors())
            ->not->toBeNull();

    })->with('price');

    test('The getErrors() method must return an array', function(Price $price) {
        expect($price->getErrors())
            ->not->toBeNull()
            ->toBeArray();

    })->with('price');
});

describe('getResponseBody() method', function() {
    test('It should be possible to access the responseBody property using the getResponseBody() method', function(Price $price) {
        expect($price->getResponseBody())
            ->not->toBeNull();

    })->with('price');

    test('The getResponseBody() method must return an instance of stdClass', function(Price $price) {
        expect($price->getResponseBody())
            ->not->toBeNull()
            ->toBeInstanceOf(stdClass::class);

    })->with('price');
});

describe('getResponseCode() method', function() {
    test('It should be possible to access the responseCode property using the getResponseCode() method', function(Price $price) {
        expect($price->getResponseCode())
            ->not->toBeNull();

    })->with('price');

    test('The getResponseCode() method must return an int number', function(Price $price) {
        expect($price->getResponseCode())
            ->not->toBeNull()
            ->toBeInt();

    })->with('price');
});