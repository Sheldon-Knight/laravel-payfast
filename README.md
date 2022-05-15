# Laravel Payfast Integration(Dev Mode)  


Laravel Payfast Integrations an easy to use library for integrating PayFast payments into your laravel projects.
This includes Custom Integration.

Laravel Payfast is strictly use at own risk.

## Requirements

PHP 7.2.5 and later.

## Documentation

See the [Developer Docs](https://developers.payfast.co.za/docs)

## Composer

You can install the library via [Composer](http://getcomposer.org/). Run the following command:

```bash
composer require sheldonknight/laravelpayfast
```

## Getting Started

### Getting Started

### Config
publish default configuration file.

    php artisan vendor:publish

IMPORTANT: You will need to edit App\Http\Middleware\VerifyCsrfToken by adding the route, which handles the ITN response to the $excepted array. Validation is done via the ITN response.

```php
<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
      '/itn' // Your Itn Route
    ];
}

```

```env
PAYFAST_MERCHANT_ID = 
PAYFAST_MERCHANT_KEY = 
PAYFAST_PASSPHRASE = 
PAYFAST_TESTING = true/false
```

### Create Form Fields

Build a checkout form and receive payments securely from the PayFast payment platform.

See the [Developer Docs](https://developers.payfast.co.za/docs#quickstart)

```php
// controller
    try {     
        $payfast = new PayFastPayment();

        $data = [
            'amount' => '100.00',
            'item_name' => 'Order#123'
        ];

        $htmlForm = $payfast->createFormFields($data, ['value' => 'PAY NOW', 'class' => 'btn btn-primary btn-sm']);
    } catch(Exception $e) {
        echo 'There was an exception: '.$e->getMessage();
    }

    return view('welcome',compact('htmlForm'));

   
```
if you havent set up your env you can pass array
```php
  $payfast = new PayFastPayment([
                'merchantId' => '10000100',
                'merchantKey' => '46f0cd694581a',
                'passPhrase' => '',
                'testMode' => true
            ]);       
```

In your view

```php
   {!! $htmlForm !!}
```
Outputs:


```html    
<form action="https://sandbox.payfast.co.za/eng/process" method="post">
    <input name="merchant_id" type="hidden" value="10000100">
    <input name="merchant_key" type="hidden" value="46f0cd694581a">
    <input name="return_url" type="hidden" value="http://PayfastTest.test/success">
    <input name="cancel_url" type="hidden" value="http://PayfastTest.test/cancel">
    <input name="notify_url" type="hidden" value="http://PayfastTest.test/itn">
    <input name="amount" type="hidden" value="100.00">
    <input name="item_name" type="hidden" value="Order#123">
    <input name="signature" type="hidden" value="51a97ee711960d3605f1386f5c0f70f6">
    <input type="submit" value="PAY NOW" class="btn btn-primary btn-sm">
</form>
```

### Check Valid Notification

```php
  header( 'HTTP/1.0 200 OK' );
  flush();

    $data = [

            'm_payment_id' => '1234',
            'pf_payment_id' => '1221576',
            'payment_status' =>'COMPLETE',
            'item_name' =>'Order#123',
            'item_description' =>'',
            'amount_gross' =>'10.00',
            'amount_fee' =>'-0.23',
            'amount_net' =>'9.77',
            'custom_str1' =>'',
            'custom_str2' =>'',
            'custom_str3' =>'',
            'custom_str4' =>'',
            'custom_str5' =>'',
            'custom_int1' =>'',
            'custom_int2' =>'',
            'custom_int3' =>'',
            'custom_int4' =>'',
            'custom_int5' =>'',
            'name_first' =>'First Name',
            'name_last' =>'Last Name',
            'email_address' =>'test@test.com',
            'merchant_id' =>'10000100',
            'signature' =>'93db1c63dc397361ab6b05e36fd73125'
        ];

        $notification = $payfast->notification->isValidNotification($_POST, ['amount_gross' => "10.00"]);

        if($notification === true) {
        // All checks have passed, the payment is successful       
        } else {
        // Some checks have failed, check payment manually and log for investigation -> PayFastPayment::$errorMsg       
        }
        } catch(Exception $e) {
            // Handle exception
            // dd('There was an exception: '.$e->getMessage());
        };
```

### todos
Project is not done yet stil has a few things to do(Dev Mode)

here is some more things i would like to bring for future use..

1.  Onsite Payments
2.  Payfast API( Update card ,Transaction History,Credit card transaction query,Refunds)

and many thanks to billowapp and Payfast 

 




☺
