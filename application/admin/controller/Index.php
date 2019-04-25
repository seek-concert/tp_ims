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
use app\admin\model\StockModel;
use app\admin\model\UserDetailModel;
use app\admin\model\PrepaidLogModel;
use app\admin\model\ConsumerLogModel;
use app\admin\model\OrderModel;
use app\admin\model\ServiceMoneyModel;
use app\admin\model\UserModel;
use think\Db;

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
        $stock_model = new StockModel();
        $user_detail = new UserDetailModel();
        $user_model = new UserModel();
        $prepaid_log = new PrepaidLogModel();
        $consumer_log = new ConsumerLogModel();
        $service_money = new ServiceMoneyModel();
        $extract_model = new ExtractLogModel();
        $admin_id = session('id');
        $admin_info = model('admin/UserModel')->getAdminDetail($admin_id);
        $admin_info['role_name'] = $role_model->getOneRole($admin_info['role_id'])['role_name'];
        //  所有库存
        $id = session('id');
        $uid = $this->get_user($id);
        $return_data = [];
        //库存总量
        $all_stock = $stock_model->getAllStockCount(['input_user' => ['in', $uid]]);
        //未使用
        $not_use_stock = $stock_model->getAllStockCount(['input_user' => ['in', $uid], 'status' => 1]);
        //发布中
        $in_out_stock = $stock_model->getAllStockCount(['input_user' => ['in', $uid], 'status' => 3]);
        //已出库
        $out_stock = $stock_model->getAllStockCount(['input_user' => ['in', $uid], 'status' => 4]);
        //使用中
        $in_use_stock = $stock_model->getAllStockCount(['input_user' => ['in', $uid], 'status' => 2]);

        //累计充值
        $all_prepaid = $prepaid_log->getPrepaidLogMoney(['user_id' => $id]);
        //累计消费
        $consumer_count = $consumer_log->getConsumerLogMoney(['user_id' => $id]);
        //用户余额
        $money = $user_detail->get_user_one($id, 'balance');


        //冻结金额
        $lock_money = $user_detail->get_user_one($id, 'funds');

        //订单总数
        $all_order = $consumer_log->getAllConsumerLog(['user_id|seller_id' => $id]);
        //购买次数
        $buy_count = $consumer_log->getAllConsumerLog(['user_id' => $id]);
        //订单总数
        $succ_order = $consumer_log->getAllConsumerLog(['user_id|seller_id' => $id, 'status' => 1]);

        if ($id == 1) {
            //总充值
            $admin_all_prepaid = $prepaid_log->getPrepaidLogMoney([]);
            //等待审核
            $admin_wait_prepaid = $prepaid_log->getPrepaidLogMoney(['status' => 1]);
            //审核完成
            $admin_succ_prepaid = $prepaid_log->getPrepaidLogMoney(['status' => 2]);
            //累计消费
            $admin_pay_prepaid = $consumer_log->sum('price');
            //总原账户余额
            $admin_all_money = $user_model->sum('money');
            //总充值单数
            $admin_pay_count = $prepaid_log->getAllPrepaidLog([]);
            //等待验证单数
            $admin_wite_count = $prepaid_log->getAllPrepaidLog(['status' => 1]);
            //充值成功
            $admin_succ_count = $prepaid_log->getAllPrepaidLog(['status' => 2]);
            //出库手续费
            $admin_out_service = $service_money->where(['type' => 2])->sum('price');
            //交易市场手续费
            $admin_buy_service = $consumer_log->sum('service_price');
            //用户余额
            $admin_all_balance = $user_detail->sum('balance');
            //冻结资金
            $admin_lock_money = $user_detail->sum('funds');
            //总提现金额
            $admin_extract_moenys = $extract_model->sum('money');
            //总审核提现金额
            $admin_extract_check_moenys = $extract_model->where(['status'=>1])->sum('money');
            //总成功提现金额
            $admin_extract_success_moenys = $extract_model->where(['status'=>2])->sum('money');
            //总失败提现金额
            $admin_extract_error_moenys = $extract_model->where(['status'=>3])->sum('money');

            $return_data['admin_all_prepaid'] = $admin_all_prepaid;
            $return_data['admin_wait_prepaid'] = $admin_wait_prepaid;
            $return_data['admin_succ_prepaid'] = $admin_succ_prepaid;
            $return_data['admin_pay_prepaid'] = $admin_pay_prepaid;
            $return_data['admin_all_money'] = $admin_all_money;
            $return_data['admin_pay_count'] = $admin_pay_count;
            $return_data['admin_wite_count'] = $admin_wite_count;
            $return_data['admin_succ_count'] = $admin_succ_count;
            $return_data['admin_out_service'] = $admin_out_service;
            $return_data['admin_buy_service'] = $admin_buy_service;
            $return_data['admin_all_balance'] = $admin_all_balance;
            $return_data['admin_lock_money'] = $admin_lock_money;
            $return_data['admin_extract_moenys'] = $admin_extract_moenys;
            $return_data['admin_extract_check_moenys'] = $admin_extract_check_moenys;
            $return_data['admin_extract_success_moenys'] = $admin_extract_success_moenys;
            $return_data['admin_extract_error_moenys'] = $admin_extract_error_moenys;
        }

        $return_data['admin_info'] = $admin_info;
        $return_data['all_stock'] = $all_stock;
        $return_data['not_use_stock'] = $not_use_stock;
        $return_data['in_out_stock'] = $in_out_stock;
        $return_data['out_stock'] = $out_stock;
        $return_data['in_use_stock'] = $in_use_stock;
        $return_data['all_prepaid'] = $all_prepaid;
        $return_data['consumer_count'] = $consumer_count;
        $return_data['money'] = $money;
        $return_data['lock_money'] = $lock_money;
        $return_data['all_order'] = $all_order;
        $return_data['buy_count'] = $buy_count;
        $return_data['succ_order'] = $succ_order;
        return view('index', $return_data);
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
        $user_details = $user_detail->where(['uid' => $param['user_id']])->field('password,balance,funds')->find();
        //匹配密码是否一致
        if ($user_details['password'] != md5($param['password'])) {
            return msg(-1, '', '二级密码错误');
        }
        //用户余额是否足够
        $balance = $user_details['balance'] - $param['money'];
        $funds = $user_details['funds'] + $param['money'];
        if ($balance < 0) {
            return msg(-1, '', '余额不足');
        }
        $param['number'] = create_guid();
        $param['input_time'] = time();
        //开启事务
        Db::startTrans();
        try {
            $extract_insert = Db::name('extract_log')->insert($param);
            $user_detail_update = Db::name('user_detail')->where(['uid' => $param['user_id']])->setField(['funds' => $funds, 'balance' => $balance]);
            if ($extract_insert && $user_detail_update) {
                // 提交事务
                Db::commit();
                return msg(1, '', '提交成功，等待审核！');
            } else {
                // 回滚事务
                Db::rollback();
                return msg(-1, '', '提交失败');
            }
        } catch (\Exception $e) {
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
        if ($param['time'] == '') {
            return msg(-1, '', '请选择时间');
        }
        $user_detail = new UserDetailModel();
        //查询用户余额
        $user_details = $user_detail->where(['uid' => $param['uid']])->field('balance,duetime')->find();
        switch ($param['time']) {
            case 1 :
                $balance = (int)$user_details['balance'] - 9000;
                break;
            case 2:
                $balance = (int)$user_details['balance'] - 18000;
                break;
            case 3:
                $balance = (int)$user_details['balance'] - 27000;
                break;
            case 4:
                $balance = (int)$user_details['balance'] - 3000;
                break;
            case 5:
                $balance = (int)$user_details['balance'] - 36000;
                break;
            default:
                return msg(-1, '', '续费失败');
        }
        //判断余额是否足够
        if ($balance < 0) {
            return msg(-1, '', '余额不足');
        }
        switch ($param['time']) {
            case 1 :
                if (empty($user_details['duetime'])) {
                    $times = strtotime(date("Y-m-d", strtotime("+4 month")));
                } else {
                    $times = strtotime(date('Y-m-d', strtotime(date('Y-m-d', $user_details['duetime']) . ' +4 month')));
                }
                break;
            case 2:
                if (empty($user_details['duetime'])) {
                    $times = strtotime(date("Y-m-d", strtotime("+8 month")));
                } else {
                    $times = strtotime(date('Y-m-d', strtotime(date('Y-m-d', $user_details['duetime']) . ' +8 month')));
                }
                break;
            case 3:
                if (empty($user_details['duetime'])) {
                    $times = strtotime(date("Y-m-d", strtotime("+1 year")));
                } else {
                    $times = strtotime(date('Y-m-d', strtotime(date('Y-m-d', $user_details['duetime']) . ' +1 year')));
                }
                break;
            case 4:
                if (empty($user_details['duetime'])) {
                    $times = strtotime(date("Y-m-d", strtotime("+1 month")));
                } else {
                    $times = strtotime(date('Y-m-d', strtotime(date('Y-m-d', $user_details['duetime']) . ' +1 month')));
                }
                break;
            case 5:
                if (empty($user_details['duetime'])) {
                    $times = strtotime(date("Y-m-d", strtotime("+1 year")));
                } else {
                    $times = strtotime(date('Y-m-d', strtotime(date('Y-m-d', $user_details['duetime']) . ' +1 year')));
                }
                break;
            default:
                return msg(-1, '', '续费失败');
        }
        //修改用户余额和到期时间
        $user_detail_update = $user_detail->where(['uid' => $param['uid']])->setField(['duetime' => $times, 'balance' => $balance]);
        if ($user_detail_update) {
            return msg(1, '', '续费成功');
        } else {
            return msg(-1, '', '续费失败');
        }
    }
}
