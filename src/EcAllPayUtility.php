<?php
/**
 * Created by PhpStorm.
 * User: merik
 * Date: 17/04/2017
 * Time: 12:39 PM
 */

namespace VoiceTube\TaiwanPaymentGateway;

class EcAllPayUtility extends Common\AbstractGateway
{
    /**
     * @param bool $autoSubmit
     * @return string
     */
    public function genForm($autoSubmit)
    {

        $this->autoSubmit = !!$autoSubmit;

        if (!isset($this->order['ChoosePayment'])) {
            throw new \InvalidArgumentException('Payment method not set');
        }

        if ($this->order['ChoosePayment'] == 'BARCODE'
            || $this->order['ChoosePayment'] == 'ATM'
            || $this->order['ChoosePayment'] == 'CVS'
        ) {
            if (empty($this->paymentInfoUrl)) {
                throw new \InvalidArgumentException('PaymentInfoURL not set');
            }
        }

        $this->order['CheckMacValue'] = $this->genCheckValue();

        $formId = sprintf("PG_ALLPAY_FORM_GO_%s", sha1(time()));

        $html = sprintf(
            "<form style='display: none' id='%s' method='post' action='%s'>",
            $formId,
            "{$this->actionUrl}{$this->version}"
        );
        foreach ($this->order as $key => $value) {
            $html .= sprintf("<input type='text' name='%s' value='%s'>", $key, $value);
        }
        $html .= "</form>";

        if ($this->autoSubmit) {
            $html .= sprintf("<script>document.getElementById('%s').submit();</script>", $formId);
        }

        return $html;
    }
}
