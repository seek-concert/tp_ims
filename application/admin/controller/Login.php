<?php
// +----------------------------------------------------------------------
// | snake
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 http://baiyf.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: NickBai <1902822973@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\model\LoginLogModel;
use app\admin\model\RoleModel;
use app\admin\model\UserDetailModel;
use app\admin\model\UserModel;
use app\admin\model\UserType;
use think\Controller;
use org\Verify;
use think\Queue;

class Login extends Controller
{
    // 登录页面
    public function index()
    {
        return $this->fetch('/login');
    }

    // 登录操作
    public function doLogin()
    {
        $userName = input("param.user_name");
        $password = input("param.password");
        $code = input("param.code");
        $result = $this->validate(compact('userName', 'password', "code"), 'LoginValidate');
        if(true !== $result){
            return json(msg(-1, '', $result));
        }

        $verify = new Verify();
        if (!$verify->check($code)) {
            return json(msg(-2, '', '验证码错误'));
        }

        $userModel = new UserModel();
        $hasUser = $userModel->findUserByName($userName);
        if(empty($hasUser)){
            return json(msg(-3, '', '管理员不存在'));
        }

        if(md5($password) != $hasUser['password']){
            return json(msg(-4, '', '密码错误'));
        }

        if(1 != $hasUser['status']){
            return json(msg(-5, '', '该账号被禁用'));
        }

        // 获取该管理员的角色信息
        $roleModel = new RoleModel();
        $info = $roleModel->getRoleInfo($hasUser['role_id']);
        if($info['rule'] != '*'){
            $user_detail_model = new UserDetailModel();
            $user_detail = $user_detail_model->get_user_one($info['id'],'duetime');
            if($user_detail <= time()){
                return json(msg(-8, '', '会员已到期'));
            }
        }
        session('username', $hasUser['real_name']);
        session('id', $hasUser['id']);
        session('role', $info['role_name']);  // 角色名
        session('rule', $info['rule']);  // 角色节点
        session('action', $info['action']);  // 角色权限

        // 更新管理员状态
        $param = [
            'login_times' => $hasUser['login_times'] + 1,
            'last_login_ip' => request()->ip(),
            'last_login_time' => time()
        ];
        $res = $userModel->updateStatus($param, $hasUser['id']);
        if(1 != $res['code']){
            return json(msg(-6, '', $res['msg']));
        }

        //写入登录日志
        $login_log = new LoginLogModel();
        $log = $login_log->insertLoginLog([
            'real_name' => $hasUser['real_name'],
            'last_login_time' => time(),
            'last_login_ip' => request()->ip(),
            'operate' => '登陆系统',
        ]);
        if(1 != $log['code']){
            return json(msg(-7, '', $res['msg']));
        }

        // ['code' => 1, 'data' => url('index/index'), 'msg' => '登录成功']
        return json(msg(1, url('index/index'), '登录成功'));
    }

    // 验证码
    public function checkVerify()
    {
        $verify = new Verify();
        $verify->imageH = 32;
        $verify->imageW = 100;
        $verify->length = 4;
        $verify->useNoise = false;
        $verify->fontSize = 14;
        return $verify->entry();
    }

    // 退出操作
    public function loginOut()
    {
        session('username', null);
        session('id', null);
        session('role', null);  // 角色名
        session('rule', null);  // 角色节点
        session('action', null);  // 角色权限

        $this->redirect(url('index'));
    }

    //修改密码
    public function edit_password()
    {
        if (request()->isPost()) {
            $param = input('post.');
            //验证数据
            $result = $this->validate($param, 'CipherValidate');
            if (true !== $result) {
                // 验证失败 输出错误信息
                return msg(-1, '', $result);
            }
            $id = session('id');
            $param['used'] = md5($param['used']);
            $param['password'] = md5($param['password']);
            $user = new UserModel();
            //获取信息
            $users = $user
                ->where([
                    'id' => $id
                ])
                ->find();
            //验证旧密码是否正确
            if ($users['password'] != $param['used']) {
                return msg(-1, '', '原密码错误！');
            }
            //修改密码
            $flag = $user
                ->where([
                    'id' => $id
                ])
                ->setField([
                    'password' => $param['password'],
                    'update_time' => time()
                ]);
            if ($flag) {
                return msg(1, url('login/loginOut'), '修改成功');
            } else {
                return msg(-1, '', '修改失败');
            }
        }
    }

    /*
     * 测试队列action
     * */
    public function actionWithHelloJob(){
        // 1.当前任务将由哪个类来负责处理。
        // 当轮到该任务时，系统将生成一个该类的实例，并调用其 fire 方法
        $jobHandlerClassName  = 'app\admin\job\Hello@fire';
        // 2.当前任务归属的队列名称，如果为新队列，会自动创建
        $jobQueueName     = "helloJobQueue";
        // 3.当前任务所需的业务数据 . 不能为 resource 类型，其他类型最终将转化为json形式的字符串
        // ( jobData 为对象时，需要在先在此处手动序列化，否则只存储其public属性的键值对)
        $jobData          = [ 'name' => 'test'.rand(), 'password'=>rand()] ;
        // 4.将该任务推送到消息队列，等待对应的消费者去执行
        $time2wait = strtotime('2018-09-08 11:15:00') - strtotime('now');  // 定时执行
        $isPushed = Queue::later(60, $jobHandlerClassName , $jobData , $jobQueueName );
        // database 驱动时，返回值为 1|false  ;   redis 驱动时，返回值为 随机字符串|false
        if( $isPushed !== false ){
            echo date('Y-m-d H:i:s') . " a new Hello Job is Pushed to the MQ"."<br>";
        }else{
            echo 'Oops, something went wrong.';
        }
    }
}