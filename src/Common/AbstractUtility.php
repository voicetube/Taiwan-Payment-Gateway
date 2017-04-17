<?php

namespace VoiceTube\TaiwanPaymentGateway\Common;

abstract class AbstractUtility extends OrderBag
{

    protected $urlEncodeMapping;

    public function setArrayConfig($config)
    {
        foreach ($config as $key => $value) {
            $this->setConfig($key, $value);
        }

        $this->urlEncodeMapping = [
            '%2D' => '-',
            '%5F' => '_',
            '%2E' => '.',
            '%21' => '!',
            '%2A' => '*',
            '%2d' => '-',
            '%5f' => '_',
            '%2e' => '.',
            '%2a' => '*',
            '%28' => '(',
            '%29' => ')',
            '%20' => '+'];
    }

    private function isExists($key)
    {
        return property_exists(self::class, $key);
    }

    /**
     * @param $key
     * @param $value
     * @throws \InvalidArgumentException
     * @return mixed
     */
    public function setConfig($key, $value)
    {
        $key = trim($key);

        if ($this->isExists($key)) {
            $this->$key = $value;

            return $value;
        }

        throw new \InvalidArgumentException('config key not exists.');
    }

    /**
     * @param $key
     * @throws \InvalidArgumentException
     * @return mixed
     */
    public function getConfig($key)
    {
        $key = trim($key);

        if ($this->isExists($key)) {
            return $this->$key;
        }
        throw new \InvalidArgumentException('config key not exists.');
    }
}
