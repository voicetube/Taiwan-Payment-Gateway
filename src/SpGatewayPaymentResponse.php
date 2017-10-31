<?php

namespace VoiceTube\TaiwanPaymentGateway;

use VoiceTube\TaiwanPaymentGateway\Common;

class SpGatewayPaymentResponse extends Common\AbstractResponse implements Common\ResponseInterface
{

    private function strippadding($string)
    {
        $strLast = ord(substr($string, -1));
        $strLastChr = chr($strLast);
        if (preg_match("/$strLastChr{" . $strLast . "}/", $string)) {
            $string = substr($string, 0, strlen($string) - $strLast);
            return $string;
        } else {
            return false;
        }
    }

    public function processOrder($type = 'JSON')
    {
        switch ($type) {
            case 'JSON':
                return $this->processOrderJson();
                break;
            case 'POST':
            default:
                return $this->processOrderPost();
                break;
        }
    }

    /**
     * @return bool|array
     */
    public function processOrderJson()
    {

        if ($this->version >= 1.4) {
            $post = $_POST;
            if ($matched = $this->matchCheckCode($post)) {
                $result = $this->decryptAesPayment($post['TradeInfo']);
                $result['matched'] = true;
            } else {
                return false;
            }
        } else {
            if (!isset($_POST['JSONData'])) {
                return false;
            }

            $post = json_decode($_POST['JSONData'], true);

            if ($post['Status'] !== 'SUCCESS') {
                return false;
            }

            $result = json_decode($post['Result'], true);

            $result['matched'] = $this->matchCheckCode($result);
        }

        return $result;
    }

    /**
     * @return bool|array
     */
    public function processOrderPost()
    {
        if (!isset($_POST)) {
            return false;
        }
        if (empty($_POST)) {
            return false;
        }

        $post = $_POST;

        if ($post['Status'] !== 'SUCCESS') {
            return false;
        }

        $matched = $this->matchCheckCode($post);

        if ($matched === false) {
            return false;
        }

        if ($this->version >= 1.4) {
            $post = $this->decryptAesPayment($post['TradeInfo']);

            $post['Result'] = filter_var_array(
                $post['Result'],
                [
                    'Status' => FILTER_SANITIZE_STRING,
                    'Message' => FILTER_SANITIZE_STRING,
                    'MerchantID' => FILTER_SANITIZE_STRING,
                    'Amt' => FILTER_VALIDATE_INT,
                    'TradeNo' => FILTER_SANITIZE_STRING,
                    'MerchantOrderNo' => FILTER_SANITIZE_STRING,
                    'PaymentType' => FILTER_SANITIZE_STRING,
                    'RespondType' => FILTER_SANITIZE_STRING,
                    'CheckCode' => FILTER_SANITIZE_STRING,
                    'PayTime' => FILTER_SANITIZE_STRING,
                    'IP' => FILTER_VALIDATE_IP,
                    'EscrowBank' => FILTER_SANITIZE_STRING,
                    'TokenUseStatus' => FILTER_VALIDATE_INT,
                    'RespondCode' => FILTER_SANITIZE_STRING,
                    'Auth' => FILTER_SANITIZE_STRING,
                    'Card6No' => FILTER_SANITIZE_STRING,
                    'Card4No' => FILTER_SANITIZE_STRING,
                    'Inst' => FILTER_VALIDATE_INT,
                    'InstFirst' => FILTER_VALIDATE_INT,
                    'InstEach' => FILTER_VALIDATE_INT,
                    'ECI' => FILTER_SANITIZE_STRING,
                    'PayBankCode' => FILTER_SANITIZE_STRING,
                    'PayerAccount5Code' => FILTER_SANITIZE_STRING,
                    'CodeNo' => FILTER_SANITIZE_STRING,
                    'Barcode_1' => FILTER_SANITIZE_STRING,
                    'Barcode_2' => FILTER_SANITIZE_STRING,
                    'Barcode_3' => FILTER_SANITIZE_STRING,
                    'PayStore' => FILTER_SANITIZE_STRING
                ],
                false
            );
        } else {
            $post = filter_var_array(
                $post,
                [
                    'Status' => FILTER_SANITIZE_STRING,
                    'Message' => FILTER_SANITIZE_STRING,
                    'MerchantID' => FILTER_SANITIZE_STRING,
                    'Amt' => FILTER_VALIDATE_INT,
                    'TradeNo' => FILTER_SANITIZE_STRING,
                    'MerchantOrderNo' => FILTER_SANITIZE_STRING,
                    'PaymentType' => FILTER_SANITIZE_STRING,
                    'RespondType' => FILTER_SANITIZE_STRING,
                    'CheckCode' => FILTER_SANITIZE_STRING,
                    'PayTime' => FILTER_SANITIZE_STRING,
                    'IP' => FILTER_VALIDATE_IP,
                    'EscrowBank' => FILTER_SANITIZE_STRING,
                    'TokenUseStatus' => FILTER_VALIDATE_INT,
                    'RespondCode' => FILTER_SANITIZE_STRING,
                    'Auth' => FILTER_SANITIZE_STRING,
                    'Card6No' => FILTER_SANITIZE_STRING,
                    'Card4No' => FILTER_SANITIZE_STRING,
                    'Inst' => FILTER_VALIDATE_INT,
                    'InstFirst' => FILTER_VALIDATE_INT,
                    'InstEach' => FILTER_VALIDATE_INT,
                    'ECI' => FILTER_SANITIZE_STRING,
                    'PayBankCode' => FILTER_SANITIZE_STRING,
                    'PayerAccount5Code' => FILTER_SANITIZE_STRING,
                    'CodeNo' => FILTER_SANITIZE_STRING,
                    'Barcode_1' => FILTER_SANITIZE_STRING,
                    'Barcode_2' => FILTER_SANITIZE_STRING,
                    'Barcode_3' => FILTER_SANITIZE_STRING,
                    'PayStore' => FILTER_SANITIZE_STRING
                ],
                false
            );
        }

        $post['matched'] = true;

        return $post;
    }

    public function matchCheckCode(array $payload = [])
    {
        if ($this->version >= 1.4) {
            $matchedCode = $payload['TradeSha'];

            $checkStr = sprintf(
                "HashKey=%s&%s&HashIV=%s",
                $this->hashKey,
                $payload['TradeInfo'],
                $this->hashIV
            );
        } else {
            $matchedCode = $payload['CheckCode'];

            $checkCode = [
                "Amt"             => $payload['Amt'],
                "TradeNo"         => $payload['TradeNo'],
                "MerchantID"      => $payload['MerchantID'],
                "MerchantOrderNo" => $payload['MerchantOrderNo'],
            ];

            ksort($checkCode);

            $checkCode = array_merge(['HashIV' => $this->hashIV], $checkCode, ['HashKey' => $this->hashKey]);
            $checkStr = http_build_query($checkCode);
        }

        return $matchedCode == strtoupper($this->hashMaker($checkStr));
    }

    public function decryptAesPayment($encrypted, $type = 'JSON')
    {

        $encryptedRaw = hex2bin($encrypted);

        $decrypted = openssl_decrypt(
            $encryptedRaw,
            'aes-256-cbc',
            $this->hashKey,
            OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING,
            $this->hashIV
        );

        $decrypted = $this->strippadding($decrypted);

        switch ($type) {
            case 'JSON':
                $decryptedJson = json_decode($decrypted, true);

                if (json_last_error()) {
                    throw new \Exception('can not parse json');
                };

                return $decryptedJson;
                break;
            case 'POST':
                $decryptedForm = parse_str($decrypted);
                return $decryptedForm;
                break;
            default:
                throw new \Exception('wrong type');
                break;
        }
    }
}
