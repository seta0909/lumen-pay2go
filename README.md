# lumen-pay2go
pay2go lumen service

## Install
1. composer require seta0909/lumen-pay2go
2. add service provider to app.php
```
$app->configure('pay2go');
and 
$app->register(Pay2go\Pay2goServiceProvider::class);
```
3. add config/pay2go.php 
```
<?php
return [
    'merchant_id' => env('MERCHANT_ID', null),

    'merchant_key' => env('MERCHANT_KEY', null),

    'merchant_iv' => env('MERCHANT_IV', null),

    'is_prod' => env('MERCHANT_PROD', false)
];

```

## Usage

### Pay for token
```
use Pay2go\CreditCard
$checkout = app(Pay2go::class);
$request = [];

$request['MerchantOrderNo'] = 1;
$request['Amt'] = 100;
$request['ProdDesc'] = 'this is a production description';
$request['PayerEmail'] = 'email@test.com';

$response = $checkout->createOrder($request)
                     ->setVersion(1.4)
                     ->setToken($memberToken->token_value, $memberToken->token_term)
                     ->payForToken();
```


