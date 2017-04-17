<?php

namespace VoiceTube\TaiwanPaymentGateway\Common;

abstract class AbstractResponse extends AbstractUtility
{
    public function __construct(array $config = [])
    {
        if (!empty($config)) {
            $this->setArrayConfig($config);
        }
    }
}
