<?php

namespace easyyuan\crontab\execute;


class CurlJobExecute extends JobExecute
{

    /**
     * run
     * @param array $data
     * @return bool
     */
    public function run(array $data): bool
    {
        $client   = new \GuzzleHttp\Client();
        $response = $client->get( $data['command'] );
        return $response->getStatusCode() === 200;
    }

    /**
     * validate
     * @param string $command
     * @return bool
     */
    public static function validate(string $command): bool
    {
        $preg = '/^(http|https):\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\’:+!]*([^<>\”])*$/';
        return (bool)preg_match( $preg,$command );
    }
}