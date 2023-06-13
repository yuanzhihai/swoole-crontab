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
        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL,$data['command'] );
        curl_setopt( $ch,CURLOPT_HEADER,false );
        curl_setopt( $ch,CURLOPT_NOBODY,false );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER,1 );
        curl_setopt( $ch,CURLOPT_FOLLOWLOCATION,false );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER,false );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYHOST,false );
        curl_setopt( $ch,CURLOPT_TIMEOUT,10 );
        curl_exec( $ch );
        $httpCode = curl_getinfo( $ch,CURLINFO_HTTP_CODE ); //返回状态码
        curl_close( $ch );
        return $httpCode == 200;
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