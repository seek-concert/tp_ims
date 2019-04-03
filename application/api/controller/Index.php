<?php
namespace app\api\controller;
use think\Controller;

class Index extends Controller
{
    //协议类型
    private $is_https = true;

    //初始化
    public function __construct()
    {
        parent::__construct();
        $is_https = is_https();
        if(false===$is_https){
            $this->is_https=false;
        }
    }
    /*========================【库存接口】===========================*/
    //登陆
    public function login($user='',$pwd='')
    {
        if(false==$this->is_https){
            return msg(-1,'当前接口暂不支持此协议');
        }
        if(!$user){
            return msg(1,'请输入账号','');
        }
        if(!$pwd){
            return msg(1,'请输入密码','');
        }
        $username = stripTags(input('post.username/s'));
        $password = stripTags(input('post.password/s'));

        if($username&&$password){
            return msg(1, '登陆失败');
        }


        return msg(0, 'login success', 'token');
    }





}
