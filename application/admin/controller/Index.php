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
use app\admin\model\ExtractLogModel;
use app\admin\model\NodeModel;
use app\admin\model\RoleModel;
use app\admin\model\UserDetailModel;
use think\Db;
use think\Session;
class Index extends Base
{

    public function index()
    {
        // 获取权限菜单
        $node = new NodeModel();
        $this->assign([
            'menu' => $node->getMenu(session('rule'))
        ]);

        return $this->fetch('/index');
    }

    /**
     * 后台默认首页
     * @return mixed
     */
    public function indexPage()
    {
        $role_model = new RoleModel();
        $admin_id = session('id');
        $admin_info = model('admin/UserModel')->getAdminDetail($admin_id);
        $admin_info['role_name'] = $role_model->getOneRole($admin_info['role_id'])['role_name'];
        $return_data = [];
        $return_data['admin_info'] = $admin_info;
        return view('index',$return_data);
    }

    /*
     * 提现
     */
    public function extract()
    {
        $param = input('post.');
        $result = $this->validate($param, 'ExtractValidate');
        if (true !== $result) {
            // 验证失败 输出错误信息
            return msg(-1, '', $result);
        }
        //查询用户的二级密码
        $user_detail = new UserDetailModel();
        $user_details = $user_detail->where(['uid'=>$param['user_id']])->field('password,balance')->find();
        //匹配密码是否一致
        if($user_details['password'] != md5($param['password'])){
            return msg(-1, '', '二级密码错误');
        }
        //用户余额是否足够
        $balance = $user_details['balance'] - $param['money'];
        if($balance < 0){
            return msg(-1, '', '余额不足');
        }
        $param['number'] = create_guid();
        $param['input_time'] = time();
        //开启事务
        Db::startTrans();
        try{
            $extract_insert = Db::name('extract_log')->insert($param);
            $user_detail_update = Db::name('user_detail')->where(['uid'=>$param['user_id']])->setField(['funds'=>$param['money'],'balance'=>$balance]);
            if($extract_insert && $user_detail_update){
                // 提交事务
                Db::commit();
                return msg(1, '', '提交成功，等待审核！');
            }else{
                // 回滚事务
                Db::rollback();
                return msg(-1, '', '提交失败');
            }
        }catch(\Exception $e){
            // 回滚事务
            Db::rollback();
            return msg(-2, '', '提交失败');
        }
    }

    /*
     * 会员续费
     */
    public function recharge()
    {
        $param = input('post.');
        if($param['time'] == ''){
            return msg(-1, '', '请选择时间');
        }
        $user_detail = new UserDetailModel();
        //查询用户余额
        $user_details = $user_detail->where(['uid'=>$param['uid']])->field('balance,duetime')->find();
        switch ($param['time']){
            case 1 :
                $balance = (int)$user_details['balance'] - 3000;
                break;
            case 2:
                $balance = (int)$user_details['balance'] - 18000;
                break;
            case 3:
                $balance = (int)$user_details['balance'] - 30000;
                break;
            default:
                return msg(-1, '', '续费失败');
        }
        //判断余额是否足够
        if($balance < 0){
            return msg(-1, '', '余额不足');
        }
        switch ($param['time']){
            case 1 :
                if(empty($user_details['duetime'])){
                    $times = strtotime(date("Y-m-d H:i:s", strtotime("+1 month")));
                }else{
                    $times = strtotime(date('Y-m-d', strtotime(date('Y-m-d',$user_details['duetime']).' +1 month') ));
                }
                break;
            case 2:
                if(empty($user_details['duetime'])){
                    $times = strtotime(date("Y-m-d H:i:s", strtotime("+6 month")));
                }else{
                    $times = strtotime(date('Y-m-d', strtotime(date('Y-m-d',$user_details['duetime']).' +6 month') ));
                }
                break;
            case 3:
                if(empty($user_details['duetime'])){
                    $times = strtotime(date("Y-m-d H:i:s", strtotime("+1 year")));
                }else{
                    $times = strtotime(date('Y-m-d', strtotime(date('Y-m-d',$user_details['duetime']).' +1 year') ));
                }
                break;
            default:
                return msg(-1, '', '续费失败');
        }
        //修改用户余额和到期时间
        $user_detail_update = $user_detail->where(['uid'=>$param['uid']])->setField(['duetime' => $times,'balance' => $balance]);
        if ($user_detail_update) {
            return msg(1, '', '续费成功');
        } else {
            return msg(-1, '', '续费失败');
        }
    }
}
