<?php

namespace easyyuan\crontab\controller;

use Swoole\Http\Response;
use Swoole\Http\Request;

abstract class Controller
{
    /**
     * @var Request
     */
    public $request;

    /**
     * @var Response
     */
    public $response;

    /**
     * @var array
     */
    public $postData;

    /**
     * Controller constructor.
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Request $request,Response $response)
    {
        $this->request  = $request;
        $this->response = $response;
        $this->postData = json_decode( $request->rawContent(),1 );
        $this->init();
    }

    /**
     * init
     */
    public function init()
    {

    }

    /**
     * success
     * @param array $data
     * @param int $code
     * @param string $message
     * @return array
     */
    public function success($data = [],int $code = 0,string $message = '操作成功.')
    {
        return $this->send( $code,$message,$data );
    }

    /**
     * error
     * @param string $message
     * @param array $data
     * @param int $code
     * @return array
     */
    public function error(string $message = '操作失败.',array $data = [],int $code = 1)
    {
        return $this->send( $code,$message,$data );
    }

    /**
     * send
     * @param int $code
     * @param string $message
     * @param array $data
     * @return array
     */
    public function send(int $code,string $message,array $data)
    {
        $returnData = [
            'code'    => $code,
            'message' => $message,
        ];

        if (!empty( $data )) {
            $returnData['data'] = $data;
        }

        return $returnData;
    }

    abstract public function find(): array;

    abstract public function create(): array;

    abstract public function all(): array;

    abstract public function delete(): array;

    abstract public function start(): array;

    abstract public function stop(): array;

    abstract public function count(): array;
}