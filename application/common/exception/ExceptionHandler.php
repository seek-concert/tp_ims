<?php
namespace app\common\exception;

use Exception;
use think\exception\Handle;
use think\Request;

class ExceptionHandler extends Handle {

    private $code;
    private $msg;
    private $errorCode;

    public function render(Exception $e) {
        if($e instanceof BaseException){
            $this->code = $e->code;
            $this->msg = $e->msg;
            $this->errorCode = $e->errorCode;
        } else {
            $this->code = 500;
            $this->msg = '服务器内部异常';
            $this->errorCode = 999;
        }
        $request = Request::instance();

        $result=[
            'msg' => $this->msg,
            'error_code' => $this->errorCode,
            'request_url' => $request->url()
        ];

        return json($result,$this->code);
    }
}