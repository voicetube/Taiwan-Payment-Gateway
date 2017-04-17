<?php

namespace VoiceTube\TaiwanPaymentGateway;

use VoiceTube\TaiwanPaymentGateway\Common;

class AllPayPaymentResponse extends Common\AbstractResponse implements Common\ResponseInterface
{
    public function processOrder($type = 'POST')
    {
        unset($type);
        return $this->processOrderPost();
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
            'MerchantID'           => FILTER_SANITIZE_STRING,
            'MerchantTradeNo'      => FILTER_SANITIZE_STRING,
            'RtnCode'              => FILTER_VALIDATE_INT,
            'TradeAmt'             => FILTER_VALIDATE_INT,
            'RtnMsg'               => FILTER_SANITIZE_STRING,
            'TradeNo'              => FILTER_SANITIZE_STRING,
            'PaymentDate'          => FILTER_SANITIZE_STRING,
            'PaymentType'          => FILTER_SANITIZE_STRING,
            'PaymentTypeChargeFee' => FILTER_VALIDATE_INT,
            'TradeDate'            => FILTER_SANITIZE_STRING,
            'SimulatePaid'         => FILTER_VALIDATE_INT,
            'CheckMacValue'        => FILTER_SANITIZE_STRING,
            'PeriodType'           => FILTER_SANITIZE_STRING,
            'Frequency'            => FILTER_VALIDATE_INT,
            'ExecTimes'            => FILTER_VALIDATE_INT,
            'amount'               => FILTER_VALIDATE_INT,
            'gwsr'                 => FILTER_VALIDATE_INT,
            'stage'                => FILTER_VALIDATE_INT,
            'stast'                => FILTER_VALIDATE_INT,
            'eci'                  => FILTER_VALIDATE_INT,
            'staed'                => FILTER_VALIDATE_INT,
            'red_dan'              => FILTER_VALIDATE_INT,
            'red_yet'              => FILTER_VALIDATE_INT,
            'red_de_amt'           => FILTER_VALIDATE_INT,
            'red_ok_amt'           => FILTER_VALIDATE_INT,
            'PeriodAmount'         => FILTER_VALIDATE_INT,
            'TotalSuccessTimes'    => FILTER_VALIDATE_INT,
            'FirstAuthAmount'      => FILTER_VALIDATE_INT,
            'TotalSuccessAmount'   => FILTER_VALIDATE_INT,
            'process_date'         => FILTER_SANITIZE_STRING,
            'auth_code'            => FILTER_SANITIZE_STRING,
            'WebATMAccBank'        => FILTER_SANITIZE_STRING,
            'WebATMAccNo'          => FILTER_SANITIZE_STRING,
            'WebATMBankName'       => FILTER_SANITIZE_STRING,
            'ATMAccBank'           => FILTER_SANITIZE_STRING,
            'ATMAccNo'             => FILTER_SANITIZE_STRING,
            'PaymentNo'            => FILTER_SANITIZE_STRING,
            'PayFrom'              => FILTER_SANITIZE_STRING,
            'card4no'              => FILTER_SANITIZE_STRING,
            'card6no'              => FILTER_SANITIZE_STRING,
        ], false);

        if (!$post['RtnCode']) {
            return false;
        }

        $post['matched'] = $this->matchCheckCode($post);

        return $post;
    }

    public function matchCheckCode(array $payload = [])
    {
        $post = $_POST;

        $checkMacValue = $post['CheckMacValue'];

        unset($post['CheckMacValue']);

        uksort($post, 'strcasecmp');

        $merArray = array_merge(['HashKey' => $this->hashKey], $post, ['HashIV' => $this->hashIV]);

        $checkMerStr = urldecode(http_build_query($merArray));

        foreach ($this->urlEncodeMapping as $key => $value) {
            $checkMerStr = str_replace($key, $value, $checkMerStr);
        }

        $checkMerStr = strtolower(urlencode($checkMerStr));

        return $checkMacValue == strtoupper(hash('sha256', $checkMerStr));
    }

    public function rspOk()
    {
        echo "1|OK";
        return true;
    }

    public function rspError($msg = 'Error')
    {
        echo "0|$msg";
        return true;
    }
}
