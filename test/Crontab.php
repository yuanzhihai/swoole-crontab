<?php
date_default_timezone_set( 'Asia/Shanghai' );

define( 'RUN_TIME_PATH',__DIR__.'/runtime' );

require '../vendor/autoload.php';

use Swoole\Runtime;
use easyyuan\crontab\HttpServer;

if (!is_dir( RUN_TIME_PATH )) {
    mkdir( RUN_TIME_PATH,0755,true );
}
if (!is_writable( RUN_TIME_PATH )) {
    print_ln( RUN_TIME_PATH.' is not writable.' );
    exit();
}

$command = $argv[1] ?? '';
switch ( $command ) {
    case 'start':
        run( ( $argv[2] ?? '' ) == '-d' );
        break;
    case 'stop':
        stop();
        break;
    default:
        print_error( "use `php {$argv[0]} start|start -d|stop`" );
        exit();
        break;
}

/**
 * run
 * @param bool $daemonize
 * @return void
 * @throws ErrorException
 */
function run(bool $daemonize = false)
{
    Runtime::enableCoroutine( SWOOLE_HOOK_ALL | SWOOLE_HOOK_CURL );

    ( new HttpServer( [
        'settings'     => [
            'pid_file'  => RUN_TIME_PATH.'/httpServer.pid',
            'log_file'  => RUN_TIME_PATH.'/httpServer.log',
            'daemonize' => $daemonize,
        ],
        'jobConfig'    => [
            'data_file' => RUN_TIME_PATH.'/data.bin',
        ],
        'runLogConfig' => [
            'path' => RUN_TIME_PATH.'/logs'
        ]
    ] ) )->start();
}

function stop()
{
    if (!empty( $pid = file_get_contents( RUN_TIME_PATH.'/httpServer.pid' ) )) {
        if (!\Swoole\Process::kill( $pid,0 )) {
            print_error( "server is not running." );
        } else {
            if (!\Swoole\Process::kill( $pid,9 )) {
                print_error( "stop server fail:pid({$pid}) not exist." );
            } else {
                print_success( "stop server success." );
            }
        }
    } else {
        print_error( "stop server fail:pid file does not exist" );
    }

}
