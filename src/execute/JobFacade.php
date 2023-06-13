<?php

namespace easyyuan\crontab\execute;

use easyyuan\crontab\job\JobTable;
use easyyuan\crontab\Config;
use easyyuan\crontab\Logger;
use Swoole\Timer;

class JobFacade
{
    /**
     * getExecute
     * @param $key
     * @param $data
     */
    public static function getExecute($key,$data)
    {
        //判断是否为关闭状态
        if (empty( $data['status'] )) {
            return;
        }

        //判断执行开始时间
        if ($data['start_time'] && $data['start_time'] > time()) {
            return;
        }

        //判断执行结束时间
        if ($data['stop_time'] && $data['stop_time'] < time()) {
            $data['status'] = 0;
            JobTable::getInstance()->set( $key,$data );
            return;
        }

        //判断任务执行文件
        $executeClass = Config::getInstance()->jobConfig['run_types'][$data['run_type']] ?? '';
        if (empty( $executeClass )) {
            return;
        }

        /** @var $jobExecute JobExecute */
        $jobExecute = new $executeClass();
        if (!( $jobExecute instanceof JobExecute )) {
            return;
        }

        //获取1分钟内执行的时间戳
        try {
            $times = FormatParser::getInstance()->parse( $data['format'] );
            if (empty( $times ))
                return;
        } catch ( \Exception $e ) {
            $data['status'] = 0;
            JobTable::getInstance()->set( $key,$data );
            Logger::getInstance()->setFileName( $key )->info( '执行定时任务['.$data['name'].'],解析[format]失败.' );
            return;
        }

        $now = time();

        foreach ( $times as $time ) {
            $t = $time - $now;
            if ($t <= 0) {
                $t = 0.001;
            }

            //加入定时任务
            Timer::after( $t * 1000,function () use ($jobExecute,$key) {
                try {
                    /* @var $data array */
                    $data = JobTable::getInstance()->get( $key );
                    if (empty( $data ) || empty( $data['status'] )) {
                        return;
                    }
                    if ($jobExecute->run( $data ) === false) {
                        Logger::getInstance()->setFileName( $key )->error( '执行定时任务['.$data['name'].'],返回结果失败.' );
                    } else {
                        Logger::getInstance()->setFileName( $key )->info( '执行定时任务['.$data['name'].'],返回结果成功.' );
                    }
                } catch ( \Exception $e ) {
                    Logger::getInstance()->setFileName( $key )->error( '执行定时任务['.$data['name'].'],异常:'.$e->getMessage() );
                }
            } );
        }
    }
}