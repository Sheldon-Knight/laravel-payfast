<?php

return
[
        'testMode'  => env('PAYFAST_TESTING',true), // set false in production 
        'merchantId'  => env('PAYFAST_MERCHANT_ID', ''),  // TEST Credentials. Replace with your merchant ID from Payfast.
        'merchantKey' => env('PAYFAST_MERCHANT_KEY', ''),// TEST Credentials. Replace with your merchant key from Payfast.
        'passPhrase' => env('PAYFAST_MERCHANT_PASSPHRASE'), // set this in your payfast merchant settings
  

    'urls' => [
        'return_url'   => env('APP_URL') . '/success',   // Redirect URL on Success.
        'cancel_url'   => env('APP_URL') . '/cancel',    // Redirect URL on Cancellation.
        'notify_url'   => env('APP_URL') . '/itn',       // ITN URL.
        ],  

];