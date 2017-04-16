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
}
