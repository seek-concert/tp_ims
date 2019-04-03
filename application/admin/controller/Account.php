<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/3
 * Time: 18:13
 */

namespace app\admin\controller;


use app\admin\model\LoginLogModel;
use app\admin\model\UserDetailModel;
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
            if($detail['password'] != $param['used']){
                return msg(-1, '', '原密码错误！');
            }
            //修改二级密码
            $flag = $userdetail
                ->where([
                    'uid' => $param['uid']
                ])
                ->setField('password',$param['password']);
            if($flag){
                return msg(1, url('account/cipher'), '修改成功');
            }else{
                return msg(-1, '', '修改失败');
            }
        }
        $id = session('id');
        $this->assign([
            'id' => $id
        ]);
        return $this->fetch();
    }

}