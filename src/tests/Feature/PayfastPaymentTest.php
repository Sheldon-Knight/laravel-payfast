<?php

namespace SheldonKnight\Payfast\Tests\Feature;


use Orchestra\Testbench\TestCase as Orchestra;
use PayFast\Exceptions\InvalidRequestException;
use SheldonKnight\Payfast\PayfastPayment;



final class PayfastPaymentTest extends Orchestra
{
    private static $payFastPayment;
    private $data;

    protected function setUp(): void
    {

        parent::setUp();

        $this->data = [
            // Merchant details
            'return_url' => 'https://your.domain/return.php',
            'cancel_url' => 'https://your.domain/cancel.php',
            'notify_url' => 'https://your.domain/notify.php',
            // Buyer details
            'name_first' => 'First Name',
            'name_last'  => 'Last Name',
            'email_address' => 'test@test.com',
            // Transaction details
            'm_payment_id' => '1234', //Unique payment ID to pass through to notify_url
            'amount' => '10.00',
            'item_name' => 'Order#123'
        ];
        self::$payFastPayment = new PayFastPayment([
            'merchantId' => '10000100',
            'merchantKey' => '46f0cd694581a',
            'passPhrase' => '',
            'testMode' => true
        ]);
    }

    /**
     * @test
     */
    public function testFormCreation()
    {
        $htmlForm = self::$payFastPayment->createFormFields($this->data, ['value' => 'PAY ME NOW', 'class' => 'btn btn-primary btn-sm']);

        $this->expectOutputString($htmlForm);

        print($htmlForm);
    }

    /**
     * @test
     */
    public function testFormCreationException(): void
    {
        $this->expectException(InvalidRequestException::class);

        unset($this->data['item_name']);
        self::$payFastPayment->createFormFields($this->data, ['value' => 'PAY ME NOW', 'class' => 'btn btn-primary btn-sm']);
    }
}
