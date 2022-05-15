<?php

namespace SheldonKnight\Payfast\Tests\Feature;


use Orchestra\Testbench\TestCase as Orchestra;
use ReflectionClass;
use SheldonKnight\Payfast\PayfastPayment;


final class NotificationTest extends Orchestra
{
    private static $payFastPayment;
    private $data;

    protected function setUp(): void
    {

        parent::setUp();

        self::$payFastPayment = new PayFastPayment([
            'merchantId' => '10000100',
            'merchantKey' => '46f0cd694581a',
            'passPhrase' => '',
            'testMode' => true
        ]);
        
        $this->data = [
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
    }
  
    
    public function invokeMethod(object $object, string $methodName, array $parameters = array())
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }


     /**
     * @test
     */
    public function testSignatureCheck()
    {
        $pfData = $this->invokeMethod(self::$payFastPayment->notification, 'cleanNotificationData', [$this->data]);

        $pfParamString = $this->invokeMethod(self::$payFastPayment->notification, 'dataToString', [$pfData]);
    
        $test = $this->invokeMethod(self::$payFastPayment->notification, 'pfValidSignature', [$pfData, $pfParamString]); 
       
        $this->assertTrue($test);
    }

     /**
     * @test
     */
    public function testInvalidSignatureCheck()
    {
        $this->data['amount_gross'] = '11.00';

        $pfData = $this->invokeMethod(self::$payFastPayment->notification, 'cleanNotificationData', [$this->data]);
        $pfParamString = $this->invokeMethod(self::$payFastPayment->notification, 'dataToString', [$pfData]);
        $test = $this->invokeMethod(self::$payFastPayment->notification, 'pfValidSignature', [$pfData, $pfParamString]);

        $this->assertFalse($test);
    }

     /**
     * @test
     */
    public function testValidPaymentDataCheck()
    {
        $pfData = $this->invokeMethod(self::$payFastPayment->notification, 'cleanNotificationData', [$this->data]);
        $test = $this->invokeMethod(self::$payFastPayment->notification, 'pfValidData', [$pfData, ['amount_gross' => '10.00']]);

        $this->assertTrue($test);
    }

      /**
     * @test
     */
    public function testInvalidPaymentDataCheck()
    {
        $this->data['amount_gross'] = '11.00';

        $pfData = $this->invokeMethod(self::$payFastPayment->notification, 'cleanNotificationData', [$this->data]);
        $test = $this->invokeMethod(self::$payFastPayment->notification, 'pfValidData', [$pfData, ['amount_gross' => '10.00']]);

        $this->assertFalse($test);
    }

    /**
     * @test
     */
    public function testServerConfirmationCheck()
    {
        $pfData = $this->invokeMethod(self::$payFastPayment->notification, 'cleanNotificationData', [$this->data]);      
        $pfParamString = $this->invokeMethod(self::$payFastPayment->notification, 'dataToString', [$pfData]); 
   
        $test = $this->invokeMethod(self::$payFastPayment->notification, 'pfValidServerConfirmation', [$pfParamString]);
  
        $this->assertTrue($test);
    }

    /**
     * @test
     */
    public function testInvalidServerConfirmationCheck()
    {
        $test = $this->invokeMethod(self::$payFastPayment->notification, 'pfValidServerConfirmation', ['testing']);

        $this->assertFalse($test);
    }

    /**
     * @test
     */
    public function testUnsuccessfulNotification(): void
    {
        $data = ['some bad data'];

        $notification = self::$payFastPayment->notification->isValidNotification($data, ['amount_gross' => '10.00']);

        $this->assertFalse($notification);
    }

}
