<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/3
 * Time: 18:13
 */

namespace app\admin\controller;


use app\admin\model\LoginLogModel;
use app\admin\model\StockModel;
use app\admin\model\UserDetailModel;
use app\admin\model\UserModel;
use think\Db;

class Account extends Base
{
    /*
     * 用户登录日志
     */
    public function log()
    {
        if (request()->isAjax()) {

            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            if (!empty($param['searchText'])) {
                $where['real_name'] = ['like', '%' . $param['searchText'] . '%'];
            }
            $user = new LoginLogModel();
            $selectResult = $user->getLoginLogByWhere($where, $offset, $limit);
            // 拼装参数
            foreach ($selectResult as $key => $vo) {
                $selectResult[$key]['last_login_time'] = date('Y-m-d H:i:s', $vo['last_login_time']);
            }

            $return['total'] = $user->getAllLoginLog($where);  //总数据
            $return['rows'] = $selectResult;
            return json($return);
        }

        return $this->fetch();
    }

    /*
     * 二级密码
     */
    public function cipher()
    {
        if (request()->isPost()) {
            $param = input('post.');
            //验证数据
            $result = $this->validate($param, 'CipherValidate');
            if (true !== $result) {
                // 验证失败 输出错误信息
                return msg(-1, '', $result);
            }
            $param['used'] = md5($param['used']);
            $param['password'] = md5($param['password']);

            $userdetail = new UserDetailModel();
            //获取信息
            $detail = $userdetail
                ->where([
                    'uid' => $param['uid']
                ])
                ->find();
            //验证旧密码是否正确
            if ($detail['password'] != $param['used']) {
                return msg(-1, '', '原密码错误！');
            }
            //修改二级密码
            $flag = $userdetail
                ->where([
                    'uid' => $param['uid']
                ])
                ->setField([
                    'password' => $param['password'],
                    'update_time' => time()
                ]);
            if ($flag) {
                return msg(1, url('account/cipher'), '修改成功');
            } else {
                return msg(-1, '', '修改失败');
            }
        }
        $id = session('id');
        $this->assign([
            'id' => $id
        ]);
        return $this->fetch();
    }

    /*
     * 全部子用户
     */
    public function allsubusers()
    {
        if (request()->isAjax()) {

            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            $where['pid'] = ['>', '0'];
            if (!empty($param['searchText'])) {
                $where['real_name'] = ['like', '%' . $param['searchText'] . '%'];
            }
            $user = new UserModel();
            $selectResult = $user->getUsersByWhere($where, $offset, $limit);
            $power = config('user_power');
            // 拼装参数
            foreach ($selectResult as $key => $vo) {
                $selectResult[$key]['power'] = $power[$vo['power']];
                $selectResult[$key]['input_time'] = date('Y-m-d H:i:s', $vo['input_time']);
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id'],0,'allsubusers'));
            }

            $return['total'] = $user->getAllUsers($where);  //总数据
            $return['rows'] = $selectResult;

            return json($return);
        }
        return $this->fetch();
    }

    /*
     * 全部子用户 -- 改密
     */
    public function editallsubusers()
    {
        if (request()->isPost()) {
            $param = input('post.');
            //验证数据
            $result = $this->validate($param, 'EditallSubusersValidate');
            if (true !== $result) {
                // 验证失败 输出错误信息
                return msg(-1, '', $result);
            }
            $param['used'] = md5($param['used']);
            $param['password'] = md5($param['password']);

            $user = new UserModel();
            //获取信息
            $detail = $user
                ->where([
                    'id' => $param['id']
                ])
                ->find();
            //验证旧密码是否正确
            if ($detail['password'] != $param['used']) {
                return msg(-1, '', '原密码错误！');
            }
            //修改密码
            $flag = $user
                ->where([
                    'id' => $param['id']
                ])
                ->setField([
                    'password' => $param['password'],
                    'update_time' => time()
                ]);
            if ($flag) {
                return msg(1, url('account/allsubusers'), '修改成功');
            } else {
                return msg(-1, '', '修改失败');
            }
        }

        $id = input('param.id');
        $url = input('param.url');
        $this->assign([
            'id' => $id,
            'url' => $url
        ]);
        return $this->fetch();
    }

    /*
     * 全部子用户 -- 删除
     */
    public function delallsubusers()
    {
        $id = input('param.id');
        //开启事务
        Db::startTrans();
        try {
            $user = Db::name('user')->where('id', $id)->delete();
            $user_detail = Db::name('user_detail')->where('uid', $id)->delete();
            if ($user && $user_detail) {
                // 提交事务
                Db::commit();
                return msg(1, url('account/allsubusers'), '删除成功');
            } else {
                // 回滚事务
                Db::rollback();
                return msg(-1, '', '删除失败');
            }
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return msg(-2, '', '删除失败');
        }
    }

    /*
     * 子用户
     */
    public function subuser()
    {
        if (request()->isAjax()) {
            $id = session('id');
            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            $where['pid'] = ['=', $id];
            if (!empty($param['searchText'])) {
                $where['real_name'] = ['like', '%' . $param['searchText'] . '%'];
            }
            $user = new UserModel();
            $selectResult = $user->getUsersByWhere($where, $offset, $limit);
            $power = config('user_power');
            // 拼装参数
            foreach ($selectResult as $key => $vo) {
                $selectResult[$key]['input_time'] = date('Y-m-d H:i:s', $vo['input_time']);
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id'],$vo['power'],'subuser'));
                $selectResult[$key]['power'] = $power[$vo['power']];
            }

            $return['total'] = $user->getAllUsers($where);  //总数据
            $return['rows'] = $selectResult;

            return json($return);
        }
        return $this->fetch();
    }

    /*
     * 新增子用户
     */
    public function addsubuser()
    {
        if(request()->isPost()){

            $param = input('post.');
            //验证数据
            $result = $this->validate($param, 'UserValidate');
            if (true !== $result) {
                // 验证失败 输出错误信息
                return msg(-1, '', $result);
            }
            $id = session('id');
            //获取子用户数量
            $count = Db::name('user')->where(['pid'=>$id])->count();
            if($count >= 10){
                return msg(-1, '', '子用户已达上限');
            }
            $param['password'] = md5($param['password']);
            $param['status'] = 1;
            $param['role_id'] = 3;
            $param['pid'] = $id;
            $param['input_time'] = time();
            //开启事务
            Db::startTrans();
            try{
                //新增用户
                $user_add =  Db::name('user')->insertGetId($param);
                //用户详细信息
                $user_detail = Db::name('user_detail')
                    ->insert([
                        'uid' => $user_add,
                        'password' => $param['password'],
                        'input_time' => time()
                    ]);
                //修改上级用户child_id
                $child = Db::name('user')->where(['id'=>$id])->value('child_id');
                if(empty($child)){
                    $child = $user_add;
                }else{
                    $child = $child.','.$user_add;
                }
                $user_update = Db::name('user')->where(['id'=>$id])->setField('child_id',$child);
                if($user_add && $user_detail && $user_update){
                    // 提交事务
                    Db::commit();
                    return msg(1, url('account/subuser'), '添加用户成功');
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

        $this->assign([
            'power' => config('user_power')
        ]);
        return $this->fetch();
    }

    /*
     * 查看库存
     */
    public function stockdetail()
    {
        if (request()->isAjax()) {
            $param = input('param.');
            $limit = $param['pageSize'];

            $where = [];
            $where['user'] = ['=', $param['id']];
            $stock = new StockModel();
            $selectResult = $stock->getAllStock($param['pageNumber'], $limit, $where);

            // 拼装参数
            foreach ($selectResult as $key => $vo) {
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id'],3));
            }

            $return['total'] = $stock->getAllStockCount($where);  //总数据
            $return['rows'] = $selectResult;

            return json($return);
        }
    }

    /*
     * 撤销分配
     */
    public function return_give()
    {
        $id = input('param.id');
        $stock = new StockModel();
        $flag = $stock->returnGive($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }

    /*
     * 分配库存
     */
    public function stockallocation()
    {

    }

    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButton($id,$power=0,$url='')
    {
        if($power == 0){
            return [
                '改密' => [
                    'auth' => 'account/editallsubusers',
                    'href' => url('account/editallsubusers', ['id' => $id ,'url' => $url]),
                    'btnStyle' => 'primary',
                    'icon' => 'fa fa-paste'
                ],
                '删除' => [
                    'auth' => 'account/delallsubusers',
                    'href' => "javascript:delallsubusers(" . $id . ")",
                    'btnStyle' => 'danger',
                    'icon' => 'fa fa-trash-o'
                ]
            ];
        }else if($power == 1){
            return [
                '改密' => [
                    'auth' => 'account/editallsubusers',
                    'href' => url('account/editallsubusers', ['id' => $id ,'url' => $url]),
                    'btnStyle' => 'primary',
                    'icon' => 'fa fa-paste'
                ],
                '删除' => [
                    'auth' => 'account/delallsubusers',
                    'href' => "javascript:delallsubusers(" . $id . ")",
                    'btnStyle' => 'danger',
                    'icon' => 'fa fa-trash-o'
                ],
                '查看库存' => [
                    'auth' => 'account/stockdetail',
                    'href' => "javascript:stockdetail(" . $id . ")",
                    'btnStyle' => 'primary',
                    'icon' => 'fa fa-eye'
                ]
            ];
        }else if($power == 2){
            return [
                '改密' => [
                    'auth' => 'account/editallsubusers',
                    'href' => url('account/editallsubusers', ['id' => $id ,'url' => $url]),
                    'btnStyle' => 'primary',
                    'icon' => 'fa fa-paste'
                ],
                '删除' => [
                    'auth' => 'account/delallsubusers',
                    'href' => "javascript:delallsubusers(" . $id . ")",
                    'btnStyle' => 'danger',
                    'icon' => 'fa fa-trash-o'
                ],
                '分配库存' => [
                    'auth' => 'account/stockallocation',
                    'href' => "javascript:stockallocation(" . $id . ")",
                    'btnStyle' => 'primary',
                    'icon' => 'fa fa-institution'
                ],
                '查看库存' => [
                    'auth' => 'account/stockdetail',
                    'href' => "javascript:stockdetail(" . $id . ")",
                    'btnStyle' => 'primary',
                    'icon' => 'fa fa-eye'
                ]
            ];
        }else if($power == 3){
            return [
                '撤销分配' => [
                    'auth' => 'account/return_give',
                    'href' => "javascript:return_give(" . $id . ")",
                    'btnStyle' => 'primary',
                    'icon' => 'fa fa-paste'
                ]
            ];
        }
    }
}