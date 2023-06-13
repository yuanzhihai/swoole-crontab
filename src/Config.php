<?php

namespace easyyuan\crontab;

use easyyuan\crontab\base\ConfigException;
use easyyuan\crontab\base\Instance;
use easyyuan\crontab\controller\JobController;
use easyyuan\crontab\execute\BashJobExecute;
use easyyuan\crontab\execute\ClassJobExecute;
use easyyuan\crontab\execute\CurlJobExecute;

class Config
{
    use Instance;

    public $host = '0.0.0.0';

    public $port = 9501;

    public $sock_type = SWOOLE_SOCK_TCP;

    /**
     * HttpServe配置
     * @var array
     */
    public $settings = [
        'max_request'     => 1000,
        'daemonize'       => false,
        'log_file'        => null,
        'log_date_format' => '%Y-%m-%d %H:%M:%S',//设置 Server 日志时间格式，格式参考 strftime 的 format
    ];

    /**
     * 请求路由
     * @var array
     */
    public $routes = [
        'find'   => [JobController::class,'find'], //获取一个任务
        'create' => [JobController::class,'create'],//添加一个任务
        'all'    => [JobController::class,'all'],//获取所有任务
        'delete' => [JobController::class,'delete'], //删除一个任务
        'count'  => [JobController::class,'count'], //任务总数
        'start'  => [JobController::class,'start'], //开始一个任务
        'stop'   => [JobController::class,'stop'], //停止一个任务
    ];

    /**
     * 定时任务配置
     * @var array
     */
    public $jobConfig = [
        //运行方式
        'run_types'           => [
            'Class' => ClassJobExecute::class,//Class执行类
            'Curl'  => CurlJobExecute::class,//Curl执行类
            'Bash'  => BashJobExecute::class,//Bash执行类
        ],
        //bash安全模式 默认为空或者文件路径 非白名单里的命令不允许执行
        'bash_whitelist_file' => null,
        //任务条数
        'table_size'          => 1024,
        //存储定时任务到配置文件中
        'data_file'           => null
    ];

    /**
     * @var array
     */
    public $runLogConfig = [
        'path'    => null,
        'console' => false,
    ];

    /**
     * Config constructor.
     * @param array $config
     */
    private function __construct(array $config = [])
    {
        foreach ( $config as $key => $value ) {
            if (isset( $this->{$key} )) {
                if (is_array( $this->{$key} )) {
                    $value = array_merge( $this->{$key},$value );
                }
                $this->{$key} = $value;
            }
        }
        $this->checkConfig();
    }

    /**
     * checkConfig
     */
    public function checkConfig()
    {
        try {
            if (empty( $this->settings['worker_num'] )) {
                $this->settings['worker_num'] = swoole_cpu_num() * 2;
            }

            if (!empty( $this->jobConfig['bash_whitelist_file'] )) {
                if (!is_file( $this->jobConfig['bash_whitelist_file'] )) {
                    throw new ConfigException( 'bash_whitelist_file is not file.' );
                }
                if (!is_readable( $this->jobConfig['bash_whitelist_file'] )) {
                    throw new ConfigException( 'bash_whitelist_file is not readable.' );
                }
            }

            if (empty( $this->jobConfig['data_file'] )) {
                throw new ConfigException( 'data_file must be configuration.' );
            }

            if (!is_file( $this->jobConfig['data_file'] )) {
                touch( $this->jobConfig['data_file'] );
            } else {
                if (!is_writable( $this->jobConfig['data_file'] )) {
                    throw new ConfigException( 'data_file is not writable.' );
                }
            }

            if (empty( $this->runLogConfig['path'] )) {
                $this->runLogConfig['path'] = getcwd();
            }

            if ($this->settings['daemonize']) {
                $this->runLogConfig['console'] = false;
            }

        } catch ( \Exception $e ) {
            print_ln( $e->getMessage() );
            exit();
        }

    }
}