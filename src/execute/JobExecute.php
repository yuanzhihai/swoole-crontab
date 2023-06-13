<?php

namespace easyyuan\crontab\execute;

abstract class JobExecute
{
    abstract public function run(array $data): bool;

    /**
     * validate
     * @param string $command
     * @return bool
     */
    public static function validate(string $command): bool
    {
        return true;
    }
}