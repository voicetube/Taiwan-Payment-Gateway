<?php

namespace VoiceTube\TaiwanPaymentGateway;

use VoiceTube\TaiwanPaymentGateway\Common;

class SpGatewayPaymentResponse extends Common\AbstractResponse implements Common\ResponseInterface
{
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
        if (!isset($_POST['JSONData'])) {
            return false;
        }

        $post = json_decode($_POST['JSONData'], true);

        if ($post['Status'] !== 'SUCCESS') {
            return false;
        }

        $result = json_decode($post['Result'], true);

        $result['matched'] = $this->matchCheckCode($result);

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

        $post = filter_var_array($_POST, [
            'Status'            => FILTER_SANITIZE_STRING,
            'Message'           => FILTER_SANITIZE_STRING,
            'MerchantID'        => FILTER_SANITIZE_STRING,
            'Amt'               => FILTER_VALIDATE_INT,
            'TradeNo'           => FILTER_SANITIZE_STRING,
            'MerchantOrderNo'   => FILTER_SANITIZE_STRING,
            'PaymentType'       => FILTER_SANITIZE_STRING,
            'RespondType'       => FILTER_SANITIZE_STRING,
            'CheckCode'         => FILTER_SANITIZE_STRING,
            'PayTime'           => FILTER_SANITIZE_STRING,
            'IP'                => FILTER_VALIDATE_IP,
            'EscrowBank'        => FILTER_SANITIZE_STRING,
            'TokenUseStatus'    => FILTER_VALIDATE_INT,
            'RespondCode'       => FILTER_SANITIZE_STRING,
            'Auth'              => FILTER_SANITIZE_STRING,
            'Card6No'           => FILTER_SANITIZE_STRING,
            'Card4No'           => FILTER_SANITIZE_STRING,
            'Inst'              => FILTER_VALIDATE_INT,
            'InstFirst'         => FILTER_VALIDATE_INT,
            'InstEach'          => FILTER_VALIDATE_INT,
            'ECI'               => FILTER_SANITIZE_STRING,
            'PayBankCode'       => FILTER_SANITIZE_STRING,
            'PayerAccount5Code' => FILTER_SANITIZE_STRING,
            'CodeNo'            => FILTER_SANITIZE_STRING,
            'Barcode_1'         => FILTER_SANITIZE_STRING,
            'Barcode_2'         => FILTER_SANITIZE_STRING,
            'Barcode_3'         => FILTER_SANITIZE_STRING,
            'PayStore'          => FILTER_SANITIZE_STRING
        ], false);

        if ($post['Status'] !== 'SUCCESS') {
            return false;
        }

        $post['matched'] = $this->matchCheckCode($post);

        return $post;
    }

    public function matchCheckCode(array $payload = [])
    {
        $matched_code = $payload['CheckCode'];

        $check_code = [
            "Amt"             => $payload['Amt'],
            "TradeNo"         => $payload['TradeNo'],
            "MerchantID"      => $payload['MerchantID'],
            "MerchantOrderNo" => $payload['MerchantOrderNo'],
        ];

        ksort($check_code);

        $check_code = array_merge(['HashIV' => $this->hashIV], $check_code, ['HashKey' => $this->hashKey]);

        $check_str = http_build_query($check_code);

        return $matched_code == strtoupper(hash("sha256", $check_str));
    }
}
