<?php


namespace SheldonKnight\Payfast;


use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use PayFast\Exceptions\InvalidRequestException;
use PayFast\PayFastBase;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use SheldonKnight\Payfast\PayfastPayment ;

class Notification extends PayFastBase
{  
    public function isValidNotification(array $pfData, array $checks = []): bool {

        $pfData = $this->cleanNotificationData($pfData);
        $pfParamString = $this->dataToString($pfData);

        $check1 = $this->pfValidSignature($pfData, $pfParamString, PayfastPayment::$passPhrase);
        $check2 = $this->pfValidIP();
        $check3 = $this->pfValidData($pfData, $checks);
        $check4 = $this->pfValidServerConfirmation($pfParamString);

        return $check1 && $check2 && $check3 && $check4;
    }  
    private function cleanNotificationData($pfData) {
        // Strip any slashes in data
        foreach( $pfData as $key => $val ) {
            $pfData[$key] = stripslashes( $val );
        }
        return $pfData;
    }  
    private function dataToString($pfData): string {
        $pfParamString = '';
        foreach( $pfData as $key => $val ) {
            if( $key !== 'signature' ) {
                $pfParamString .= $key .'='. urlencode( $val ) .'&';
            } else {
                break;
            }
        }
        $pfParamString = substr( $pfParamString, 0, -1 );

        return $pfParamString;
    }

    private function pfValidSignature( $pfData, $pfParamString, $pfPassphrase = null ): bool {
        if(!isset($pfData['signature'])) {          
            PayFastPayment::$errorMsg[] = "Invalid signature";
            return false;
        }
        // Calculate security signature
        if($pfPassphrase === null) {
            $tempParamString = $pfParamString;
        } else {
            $tempParamString = $pfParamString.'&passphrase='.urlencode( $pfPassphrase );
        }

        $signature = md5( $tempParamString );
        if($pfData['signature'] !== $signature) {
            PayFastPayment::$errorMsg[] = "Invalid signature";
        }
        return ( $pfData['signature'] === $signature );
    }
  
    private function pfValidIP(): bool
    {
        if(!isset($_SERVER['HTTP_REFERER'])) {
            PayFastPayment::$errorMsg[] = "This notification does not come from a valid PayFast domain";
            return false;
        }
       
        $validHosts = [
            'www.payfast.co.za',
            'sandbox.payfast.co.za',
            'w1w.payfast.co.za',
            'w2w.payfast.co.za'
        ];

        $validIps = [];

        foreach ($validHosts as $pfHostname) {
            $ips = gethostbynamel($pfHostname);

            if ($ips !== false && is_array($ips)) {
                array_push($validIps, ...$ips);
            }
        }

        // Remove duplicates
        $validIps = array_unique($validIps);
        $referrerIp = gethostbyname(parse_url($_SERVER['HTTP_REFERER'])['host']);
        if (in_array($referrerIp, $validIps, true)) {
            return true;
        }
        PayFastPayment::$errorMsg[] = "This notification does not come from a valid PayFast domain";
        return false;
    }

    private function pfValidData($pfData, array $checks = []): bool
    {
        if(!empty($checks)) {
            foreach($checks as $k => $v) {
                if($k === 'amount_gross') {
                    if (!isset($pfData['amount_gross'])) {
                        PayFastPayment::$errorMsg[] = "Parameter 'amount_gross' does not exist in the post data";
                        return false;
                    }
                    if(abs((float)$v - (float)$pfData['amount_gross']) > 0.01) {
                        PayFastPayment::$errorMsg[] = "The 'amount_gross' is ".$pfData['amount_gross'].", you expected ".$v;
                        return false;
                    }
                } else {
                    if (!isset($pfData[$k])) {
                        PayFastPayment::$errorMsg[] = "Parameter '".$k."' does not exist in the post data";
                        return false;
                    }
                    if($pfData[$k] !== $v) {
                        PayFastPayment::$errorMsg[] = "The '".$k."' is ".$pfData[$k].", you expected ".$v;
                        return false;
                    }
                }
            }
        }
        return true;
    }

    private function pfValidServerConfirmation($pfParamString) {
   
        try {
            $client = new Client(['base_uri' => PayFastPayment::$baseUrl.'/']);               
            $response = $client->request('POST', 'eng/query/validate', [
                'headers'  => ['content-type' => 'application/x-www-form-urlencoded'],
                'body' => $pfParamString
            ]);           
          
            if ((string)$response->getBody() === 'VALID') {
                return true;
            }
            PayFastPayment::$errorMsg[] = 'Invalid server confirmation';
            return false;
        } catch (ClientException $e) {
            $response = $e->getResponse();
            throw new InvalidRequestException($response->getBody()->getContents(), 400);
        } catch (GuzzleException $e) {
            throw new RuntimeException($e);
        }
    }

}