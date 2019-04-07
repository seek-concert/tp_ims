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

use think\Controller;
use app\admin\model\UserModel;

class Base extends Controller
{
    public function _initialize()
    {
        if(empty(session('username'))){

            $loginUrl = url('login/index');
            if(request()->isAjax()){
                return msg(111, $loginUrl, '登录超时');
            }

            $this->redirect($loginUrl);
        }

        // 检测权限
        $control = lcfirst(request()->controller());
        $action = lcfirst(request()->action());

        if(empty(authCheck($control . '/' . $action))){
            if(request()->isAjax()){
                return msg(403, '', '您没有权限');
            }

            $this->error('403 您没有权限');
        }

        $this->assign([
            'username' => session('username'),
            'rolename' => session('role')
        ]);

    }

    /*
     * 查询相关用户id
     */
    public function get_user($id)
    {
        $user = new UserModel();
        //根据当前用户id查找有无上级
        $pid = $user->where(['id'=>$id])->value('pid');
        //$pid等于0时表示无上级id 查询所有下级 pid = $id
        if($pid == 0){
            $uid = $user->where(['pid'=>$id,'power'=>1])->column('id');
            //最后加上自身id
            $uid['id'] = $id;
        }else{
            //$pid不等于0时表示有上级id 查询到所有同级 pid = $pid
            $uid = $user->where(['pid'=>$pid,'power'=>1])->column('id');
            //最后加上上级id
            $uid['id'] = $pid;
        }
        //分割成字符串
        $uid = implode(',', $uid);
        return $uid;
    }

}