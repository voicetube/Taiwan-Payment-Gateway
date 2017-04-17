<?php

namespace VoiceTube\TaiwanPaymentGateway\Common;

interface ResponseInterface
{
    /**
     * Process the order information from gateway server
     * @param string $type
     * @return array|boolean
     */
    public function processOrder($type = 'JSON');

    /**
     * @param array $payload
     * @return boolean
     */
    public function matchCheckCode(array $payload = []);
}
