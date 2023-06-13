<?php

namespace easyyuan\crontab\base;

class ConfigException extends \Exception
{
    /**
     * getName
     * @return string
     */
    public function getName()
    {
        return 'Error Configuration';
    }
}