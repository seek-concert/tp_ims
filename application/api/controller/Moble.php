<?php
/*========================【14码接口】===========================*/

namespace app\api\controller;

use app\api\model\UserModel;
use think\Controller;

class Moble extends Controller
{
    //协议类型
    private $is_https = true;

    //初始化
    public function __construct()
    {
        parent::__construct();
        $is_https = is_https();
        if (false === $is_https) {
            $this->is_https = false;
        }
    }

    //登陆
    public function login()
    {
        //检测协议
        if (false == $this->is_https) {
            return msg(-1, '当前接口暂不支持此协议');
        }
        //数据检测
        $rule = [
            ['username', 'require', '请填写账号!'],
            ['password', 'require', '请填写密码!']
        ];
        $result = $this->validate(input(''), $rule);
        if (true !== $result) {
            return msg(1, $result);
        }
        //账号密码过滤
        $user = stripTags(input('username/s'));
        $pwd = stripTags(input('password/s'));
        //数据检测
        $user_info = UserModel::field(['id', 'user_name', 'password', 'status', 'token'])->where(['user_name' => $user])->find();
        if (!$user_info) {
            return msg(1, '该账号不存在');
        }
        if ($user_info['status'] == 2) {
            return msg(1, '该账号已停用');
        }
        if (md5($pwd) !== $user_info['password']) {
            return msg(1, '密码错误，请重新输入');
        }
        //生成token
        $token = create_guid();
        try {
            $rs = model('UserModel')->save(['token' => $token], ['id' => $user_info['id']]);
            if (!$rs) {
                return msg(1, '网络异常，token生成失败');
            }
        } catch (\Exception $e) {
            return msg(1, '网络异常，token生成失败!');
        }

        return msg(0, '登陆成功', $token);
    }


    /*
    *
    *添加设备信息
    */
    public function add()
    {
        //检测协议
        if (false == $this->is_https) {
            return msg(-1, '当前接口暂不支持此协议');
        }

        //数据检测
        $rule = [
            ['username', 'require', '请填写账号!'],
            ['password', 'require', '请填写密码!']
        ];
    }
    
    /*
    *
    *查询设备信息
    */
    public function query()
    {
        //检测协议
        if (false == $this->is_https) {
            return msg(-1, '当前接口暂不支持此协议');
        }
    }
}