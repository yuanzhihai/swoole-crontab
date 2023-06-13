<?php

namespace easyyuan\crontab;

use easyyuan\crontab\controller\Controller;
use easyyuan\crontab\job\JobProcess;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;

/**
 * Class HttpServer
 * @package easyyuan\crontab\server
 * @property-read Server $_server
 * @property-read Config $_config
 */
class HttpServer
{
    /**
     * @var Server
     */
    protected $_server;

    /**
     * @var Config
     */
    protected $_config;

    /**
     * HttpServer constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {

        $this->initConfig( $config );

        $this->_server = new Server( $this->_config->host,$this->_config->port,SWOOLE_PROCESS,$this->_config->sock_type );

        $this->_server->set( $this->_config->settings );

        $this->_server->addProcess( ( new JobProcess( $this->_server ) )->getProcess() );

        $this->_server->on( 'Start',[$this,'onStart'] );

        $this->_server->on( 'WorkerStart',[$this,'onWorkerStart'] );

        $this->_server->on( 'request',[$this,'onRequest'] );

    }

    /**
     * initConfig
     * @param array $config
     */
    protected function initConfig($config = [])
    {
        $this->_config = Config::getInstance( $config );
    }

    /**
     * onStart
     * @param Server $server
     */
    public function onStart(Server $server)
    {
        swoole_set_process_name( 'SwooleCrontabServer' );
    }

    /**
     * onWorkerStart
     * @param Server $server
     * @param int $worker_id
     */
    public function onWorkerStart(Server $server,int $worker_id)
    {
        swoole_set_process_name( 'SwooleCrontabWork_'.$worker_id );
    }

    /**
     * onRequest
     * @param Request $request
     * @param Response $response
     */
    public function onRequest(Request $request,Response $response)
    {
        $response->header( 'Content-Type','application/json; charset=utf-8' );
        $response->status( 200 );
        if ($request->server['request_method'] != 'POST') {
            $response->end( json_encode( [
                'code'    => 405,
                'message' => 'method not allow!'
            ] ) );
        } else {
            $url = trim( $request->server['request_uri'] ?? '/','/' );
            if (empty( $this->_config->routes[$url] )) {
                $response->end( json_encode( [
                    'code'    => 404,
                    'message' => 'action not exist.'
                ] ) );
            } else {
                try {
                    [$controller,$action] = $this->_config->routes[$url];
                    $class = ( new $controller( $request,$response ) );
                    if (!( $class instanceof Controller )) {
                        throw new \Exception( 500,$class.' must be extends '.Controller::class );
                    }
                    $data = $class->{$action}();
                    $response->end( json_encode( $data ) );
                } catch ( \Throwable $e ) {
                    $response->end( json_encode( [
                        'code'    => 500,
                        'message' => $e->getMessage(),
                    ] ) );
                }
            }
        }
    }

    /**
     * getServer
     * @return Server
     */
    public function getServer()
    {
        return $this->_server;
    }

    /**
     * on
     * @param $event_name
     * @param callable $callback
     */
    public function on($event_name,callable $callback)
    {
        $this->_server->on( $event_name,$callback );
    }

    /**
     * start
     */
    public function start()
    {
        print_success( 'Swoole Crontab is Running.' );
        $this->_server->start();
    }
}