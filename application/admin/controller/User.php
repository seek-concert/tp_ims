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


use app\admin\model\RoleModel;
use app\admin\model\UserModel;
use think\Db;

class User extends Base
{
    //用户列表
    public function index()
    {
        if(request()->isAjax()){

            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            if (!empty($param['searchText'])) {
                $where['user_name'] = ['like', '%' . $param['searchText'] . '%'];
            }
            $user = new UserModel();
            $selectResult = $user->getUsersByWhere($where, $offset, $limit);

            $status = config('user_status');
            // 拼装参数
            foreach($selectResult as $key=>$vo){

                $selectResult[$key]['last_login_time'] = date('Y-m-d H:i:s', $vo['last_login_time']);
                $selectResult[$key]['status'] = $status[$vo['status']];

                if( 1 == $vo['role_id'] ){
                    $selectResult[$key]['operate'] = '';
                    continue;
                }
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id']));
            }

            $return['total'] = $user->getAllUsers($where);  //总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        return $this->fetch();
    }

    // 添加用户
    public function useradd()
    {
        if(request()->isPost()){

            $param = input('post.');
            //验证数据
            $result = $this->validate($param, 'UserValidate');
            if (true !== $result) {
                // 验证失败 输出错误信息
                return msg(-1, '', $result);
            }

            $param['password'] = md5($param['password']);
            $param['input_time'] = time();
            //开启事务
            Db::startTrans();
            try{
                $user =  Db::name('user')->insertGetId($param);
                $user_detail = Db::name('user_detail')
                    ->insert([
                        'uid' => $user,
                        'password' => $param['password'],
                        'input_time' => time()
                    ]);
                if($user && $user_detail){
                    // 提交事务
                    Db::commit();
                    return msg(1, url('user/index'), '添加用户成功');
                }else{
                    // 回滚事务
                    Db::rollback();
                    return msg(-1, '', '添加用户失败');
                }
            }catch(\Exception $e){
                // 回滚事务
                Db::rollback();
                return msg(-2, '', '添加用户失败');
            }
        }

        $role = new RoleModel();
        $this->assign([
            'role' => $role->getRole(),
            'status' => config('user_status')
        ]);

        return $this->fetch();
    }

    // 编辑用户
    public function useredit()
    {
        $user = new UserModel();

        if(request()->isPost()){

            $param = input('post.');

            if(empty($param['password'])){
                unset($param['password']);
            }else{
                $param['password'] = md5($param['password']);
            }
            $param['update_time'] = time();
            $flag = $user->editUser($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        $id = input('param.id');
        $role = new RoleModel();

        $this->assign([
            'user' => $user->getOneUser($id),
            'status' => config('user_status'),
            'role' => $role->getRole()
        ]);
        return $this->fetch();
    }

    // 删除用户
    public function userDel()
    {
        $id = input('param.id');
        //开启事务
        Db::startTrans();
        try{
            $user =  Db::name('user')->where('id', $id)->delete();
            $user_detail = Db::name('user_detail')->where('uid', $id)->delete();
            if($user && $user_detail){
                // 提交事务
                Db::commit();
                return msg(1, url('user/index'), '删除成功');
            }else{
                // 回滚事务
                Db::rollback();
                return msg(-1, '', '删除失败');
            }
        }catch(\Exception $e){
            // 回滚事务
            Db::rollback();
            return msg(-2, '', '删除失败');
        }
    }

    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButton($id)
    {
        return [
            '编辑' => [
                'auth' => 'user/useredit',
                'href' => url('user/userEdit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'user/userdel',
                'href' => "javascript:userDel(" .$id .")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }
}
