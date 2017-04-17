<?php

namespace VoiceTube\TaiwanPaymentGateway\Common;

abstract class AbstractGateway extends AbstractUtility
{

    public function __construct(array $config = [])
    {
        if (!empty($config)) {
            $this->setArrayConfig($config);
        }
    }

    public function clearOrder()
    {
        $this->order = [];
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function argumentChecker()
    {
        /**
         * Argument Check
         */
        if (!isset($this->hashIV)) {
            throw new \InvalidArgumentException('HashIV not set');
        }
        if (!isset($this->hashKey)) {
            throw new \InvalidArgumentException('HashKey not set');
        }
        if (!isset($this->merchantId)) {
            throw new \InvalidArgumentException('MerchantID not set');
        }

        if (!isset($this->returnUrl)) {
            throw new \InvalidArgumentException('ReturnURL not set');
        }
        if (empty($this->actionUrl)) {
            throw new \InvalidArgumentException('ActionURL not set');
        }
    }
}
