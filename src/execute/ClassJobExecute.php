<?php

namespace easyyuan\crontab\execute;

class ClassJobExecute extends JobExecute
{
    /**
     * run
     * @param array $data
     * @return bool
     */
    public function run(array $data): bool
    {
        $callback = json_decode( $data['command'],true );
        [$class,$method] = $callback;
        $parameters = $callback[2] ?? null;
        if ($class && $method && class_exists( $class ) && method_exists( $class,$method )) {
            $instance = new $class();
            if ($parameters && is_array( $parameters )) {
                $instance->{$method}( ...$parameters );
            } else {
                $instance->{$method}();
            }
            return true;
        }
        return false;
    }

    /**
     * validate
     * @param string $command
     * @return bool
     */
    public static function validate(string $command): bool
    {
        $callback = json_decode( $command,true );
        [$class,$method] = $callback;
        if ($class && $method && class_exists( $class ) && method_exists( $class,$method )) {
            return true;
        }
        return false;
    }
}