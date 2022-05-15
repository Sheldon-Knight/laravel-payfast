<?php

namespace SheldonKnight\Payfast;


use Exception;
use PayFast\Auth;
use PayFast\Exceptions\InvalidRequestException;
use PayFast\ServiceMapper;

class PayfastPayment
{
    public $merchantId, $merchantKey, $returnUrl,$cancelUrl,$notifyUrl;
    public static $baseUrl,$passPhrase,$testMode;
    public static $errorMsg = [];    

    public function __construct($setup = NULL)
    {       
       
        if (isset($setup['merchantId']) or config()->has('payfast.merchantId')) {          
            $this->merchantId = $setup['merchantId'] ?? config('payfast.merchantId');           
        } else {
            throw new InvalidRequestException('Merchant ID is required', 400);
        }

        if (isset($setup['merchantKey']) or config()->has('payfast.merchantKey')) {
            $this->merchantKey = $setup['merchantKey'] ?? config('payfast.merchantKey');       
        } else {
            throw new InvalidRequestException('Merchant KEY is required', 400);
        }

        self::$passPhrase = (isset($setup['passPhrase'])) ? $setup['passPhrase'] : config('payfast.passPhrase');
        self::$testMode = (isset($setup['testMode'])) ? $setup['testMode'] : config('payfast.testMode');
        self::$baseUrl =  self::$testMode === true ? 'https://sandbox.payfast.co.za' : 'https://www.payfast.co.za';
        
        $this->returnUrl = $setup['return_url'] ?? config('payfast.urls.return_url');
        $this->cancelUrl = $setup['cancel_url'] ?? config('payfast.urls.cancel_url');
        $this->notifyUrl = $setup['notify_url'] ?? config('payfast.urls.notify_url');  
          
    }

    public function setTestMode($value = false) {
        self::$testMode = (bool) $value;
        self::$baseUrl = self::$testMode === true ? 'https://sandbox.payfast.co.za' : 'https://www.payfast.co.za';
    }

    public function createFormFields($data = [], $buttonParams = []): string
    {
     
        if (!isset($data['amount'])) {
            throw new InvalidRequestException('Required "amount" parameter missing', 400);
        }

        $data['amount'] = number_format(sprintf('%.2f', $data['amount']), 2, '.', '');

        if (!isset($data['item_name'])) {
            throw new InvalidRequestException('Required "item_name" parameter missing', 400);
        }

        $data = ['merchant_id' => $this->merchantId, 'merchant_key' => $this->merchantKey,'return_url' => $this->returnUrl,'cancel_url' => $this->cancelUrl,'notify_url' => $this->notifyUrl,] + $data;

        $signature = Auth::generateSignature($data, self::$passPhrase);
        $data['signature'] = $signature;
       

        $htmlForm = '<form action="' . self::$baseUrl . '/eng/process" method="post">';
        foreach ($data as $name => $value) {
            $htmlForm .= '<input name="' . $name . '" type="hidden" value="' . $value . '" />';
        }

        $buttonValue = 'Pay Now';
        if (!empty($buttonParams)) {
            $buttonValue = $buttonParams['value'];
        }
        $additionalOptions = '';
        foreach ($buttonParams as $k => $v) {
            $additionalOptions .= $k . '="' . $v . '" ';
        }

        $htmlForm .= '<input type="submit" value="' . $buttonValue . '" ' . $additionalOptions . '/>';

        $htmlForm .= '</form>';

       
        return $htmlForm;
    }    
   
    public function __get($property) {    
        $class = "SheldonKnight\Payfast\Notification";   
        if ($class !== null) {
            return new $class;
        } else {
            throw new Exception("Unknown method");
        }
    }


}
