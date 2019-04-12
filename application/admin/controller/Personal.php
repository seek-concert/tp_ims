<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/6
 * Time: 10:28
 */

namespace app\admin\controller;


use app\admin\model\BunledModel;
use app\admin\model\ConsumerLogModel;
use app\admin\model\ExtractLogModel;
use app\admin\model\PrepaidLogModel;
use app\admin\model\ProductModel;
use app\admin\model\UserDetailModel;
use app\admin\model\UserModel;
use think\Db;

class Personal extends Base
{
    /*
     * 账号充值
     */
    public function prepaid()
    {
        if (request()->isPost()) {
            $param = input('param.');
            $prepaid = new PrepaidLogModel();
            $param['number'] = create_guid();
            $param['user_id'] = session('id');
            $param['input_time'] = time();
            $flag = $prepaid->insertLog($param);
            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }
        $user = new UserDetailModel();
        $btc = $user->where(['uid'=>1])->value('btc');
        $this->assign('btc',$btc);
        return $this->fetch();
    }

    /*
     * 充值记录
     */
    public function prepaidlog()
    {
        if (request()->isAjax()) {
            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $uid = session('id');
            $input_time_start = isset($param['input_time_start'])?strtotime($param['input_time_start']):'';
            $input_time_end = isset($param['input_time_end'])?strtotime($param['input_time_end'].'23:59:59'):'';
            $status = isset($param['status'])?$param['status']:'';
            $number = isset($param['number'])?$param['number']:'';
            $sqlmap = [];
            //查询某个时间之后
            if ($input_time_start != '' && $input_time_end == '') {
                $sqlmap['input_time'] = ['gt', $input_time_start];
            }
            //查询某个时间之前
            if ($input_time_start == '' && $input_time_end != '') {
                $sqlmap['input_time'] = ['lt', $input_time_end];
            }
            //查询某个入库时间段
            if($input_time_start != ''&& $input_time_end != ''){
                $sqlmap['input_time'] = ['between',[$input_time_start,$input_time_end]];
            }
            //查询状态
            if (!empty($status)) {
                $sqlmap['status'] = ['eq', $status];
            }
            //查询订单号
            if (!empty($number)) {
                $sqlmap['number'] = ['eq', $number];
            }
            $sqlmap['user_id'] = ['eq',$uid];
            $prepaidlog = new PrepaidLogModel();
            $user = new UserModel();
            $selectResult = $prepaidlog->getPrepaidLogByWhere($sqlmap, $offset, $limit);

            $status = config('prepaid_status');
            // 拼装参数
            foreach ($selectResult as $key => $vo) {
                if(!empty($vo['update_time'])){
                    $selectResult[$key]['update_time'] = date('Y-m-d H:i:s', $vo['update_time']);
                }
                $selectResult[$key]['user_id'] = $user->getOneRealName($vo['user_id']);
                $selectResult[$key]['status'] = $status[$vo['status']];
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id'],0,'prepaidlog'));
            }

            $return['total'] = $prepaidlog->getAllPrepaidLog($sqlmap);  //总数据
            $return['counts'] = $prepaidlog->getPrepaidLogMoney(['user_id'=>$uid,'status'=>2]);  //统计充值
            $return['rows'] = $selectResult;

            return json($return);
        }
        return $this->fetch();
    }

    /*
     * 充值验证管理
     */
    public function prepaidlog_management()
    {
        if (request()->isAjax()) {
            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $input_time_start = isset($param['input_time_start'])?strtotime($param['input_time_start']):'';
            $input_time_end = isset($param['input_time_end'])?strtotime($param['input_time_end'].'23:59:59'):'';
            $status = isset($param['status'])?$param['status']:'';
            $number = isset($param['number'])?$param['number']:'';
            $sqlmap = [];
            //查询某个时间之后
            if ($input_time_start != '' && $input_time_end == '') {
                $sqlmap['input_time'] = ['gt', $input_time_start];
            }
            //查询某个时间之前
            if ($input_time_start == '' && $input_time_end != '') {
                $sqlmap['input_time'] = ['lt', $input_time_end];
            }
            //查询某个入库时间段
            if($input_time_start != ''&& $input_time_end != ''){
                $sqlmap['input_time'] = ['between',[$input_time_start,$input_time_end]];
            }
            //查询状态
            if (!empty($status)) {
                $sqlmap['status'] = ['eq', $status];
            }
            //查询订单号
            if (!empty($number)) {
                $sqlmap['number'] = ['eq', $number];
            }

            $prepaidlog = new PrepaidLogModel();
            $user = new UserModel();
            $selectResult = $prepaidlog->getPrepaidLogByWhere($sqlmap, $offset, $limit);
            $status = config('prepaid_status');
            // 拼装参数
            foreach ($selectResult as $key => $vo) {
                if(!empty($vo['update_time'])){
                    $selectResult[$key]['update_time'] = date('Y-m-d H:i:s', $vo['update_time']);
                }
                $selectResult[$key]['user_id'] = $user->getOneRealName($vo['user_id']);
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id'],$vo['status'],'prepaidlog_management'));
                $selectResult[$key]['status'] = $status[$vo['status']];
            }

            $return['total'] = $prepaidlog->getAllPrepaidLog($sqlmap);  //总数据
            $return['counts'] = $prepaidlog->getPrepaidLogMoney(['status'=>2]);  //统计充值
            $return['rows'] = $selectResult;

            return json($return);
        }
        return $this->fetch();
    }

    /*
     * 查看充值记录详细
     */
    public function prepaidlog_detail()
    {
        $id = input('param.id');
        $url = input('param.url');
        $user = new UserModel();
        $prepaidlog = new PrepaidLogModel();
        $row = $prepaidlog->getOnePrepaidLog($id);
        //创建时间
        if(!empty($row['input_time'])){
            $row['input_time'] = date('Y-m-d H:i:s', $row['input_time']);
        }
        //审核时间
        if(!empty($row['update_time'])){
            $row['update_time'] = date('Y-m-d H:i:s', $row['update_time']);
        }
        //订单状态
        $row['status'] = config('prepaid_status')[$row['status']];
        //用户名称
        $row['user_id'] = $user->getOneRealName($row['user_id']);
        $this->assign([
            'row' => $row,
            'url' => $url
        ]);
        return $this->fetch();
    }

    /*
     * 充值验证
     */
    public function prepaidlog_verify()
    {
        if(request()->isPost()){
            $param = input('param.');
            $result = $this->validate($param, 'PrepaidLogVerifyValidate');
            if (true !== $result) {
                // 验证失败 输出错误信息
                return msg(-1, '', $result);
            }
            $param['update_time'] = time();
            if($param['status'] == 2){
                //开启事务
                Db::startTrans();
                try {
                    //获取交易记录信息
                    $prepaidlog = Db::name('prepaid_log')
                        ->where(['id'=>$param['id']])
                        ->find();
                    //修改交易记录表
                    $prepaidlog_update = Db::name('prepaid_log')
                        ->where(['id'=>$param['id']])
                        ->update($param);
                    //修改用户账户余额
                    $user_detail_update = Db::name('user_detail')
                        ->where(['uid'=>$prepaidlog['user_id']])
                        ->setInc('balance',$prepaidlog['money']);
                    if ($prepaidlog_update && $user_detail_update) {
                        // 提交事务
                        Db::commit();
                        return msg(1, url('personal/prepaidlog_management'), '验证成功');
                    } else {
                        // 回滚事务
                        Db::rollback();
                        return msg(-1, '', '验证失败');
                    }
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                    return msg(-2, '', '验证失败');
                }
            }else{
                $prepaidlog = new PrepaidLogModel();
                $flag = $prepaidlog->VerifyPrepaidLog($param);
                return json(msg($flag['code'], $flag['data'], $flag['msg']));
            }
        }
        $id = input('param.id');
        $this->assign(['id' =>$id]);
        return $this->fetch();
    }

    /*
     * 删除充值记录
     */
    public function prepaidlog_del()
    {
        $id = input('param.id');
        $prepaidlog = new PrepaidLogModel();
        $flag = $prepaidlog->delPrepaidLog($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }

    /*
     * 消费记录
     */
    public function consumerlog()
    {
        if (request()->isAjax()) {
            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $uid = session('id');
            $bname = isset($param['bname'])?$param['bname']:'';
            $sqlmap = [];
            $bunled = new BunledModel();
            //产品
            if (!empty($bname)) {
                $bid = $bunled->where(['bname'=>$bname])->value('id');
                $sqlmap['bunled_id'] = ['eq', $bid];
            }
            if($uid != 1){
                $sqlmap['user_id'] = ['eq',$uid];
            }
            $consumerlog = new ConsumerLogModel();
            $user = new UserModel();
            $product = new ProductModel();
            $selectResult = $consumerlog->getConsumerLogByWhere($sqlmap, $offset, $limit);
            $status = config('consumer_status');
            //拼装参数
            foreach ($selectResult as $key => $vo) {
                if(!empty($vo['input_time'])){
                    $selectResult[$key]['input_time'] = date('Y-m-d H:i:s', $vo['input_time']);
                }
                if($vo['status'] == 3){
                    $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id'],6));
                }
                $selectResult[$key]['bname'] = $bunled->where(['id'=>$vo['bunled_id']])->value('bname');
                $selectResult[$key]['pname'] = $product->where(['id'=>$vo['product_id']])->value('pname');
                $selectResult[$key]['status'] = $status[$vo['status']];
                $selectResult[$key]['seller_id'] = $user->getOneRealName($vo['seller_id']);
                if($uid == 1){
                    $selectResult[$key]['user_id'] = $user->getOneRealName($vo['user_id']);
                }
            }
            $return['total'] = $consumerlog->getAllConsumerLog($sqlmap);  //总数据
            $return['counts'] = $consumerlog->getConsumerLogMoney(['user_id'=>$uid,'status'=>1]);  //统计消费
            $return['rows'] = $selectResult;

            return json($return);
        }
        return $this->fetch();
    }

    /*
     * 消费记录--确认收货
     */
    public function consumerlog_confirm()
    {
        $id = input('param.id');
        //开启事务
        Db::startTrans();
        try {
            //根据id获取信息
            $consumerlog = DB::name('consumer_log')
                ->where(['id'=>$id])
                ->find();
            //更改记录状态为成功
            $consumerlog_update = Db::name('consumer_log')
                ->where(['id'=>$id])
                ->setField('status',1);
            //获取对应卖家信息
            $user_detail = DB::name('user_detail')
                ->where(['uid'=>$consumerlog['seller_id']])
                ->find();
            $balance = $user_detail['balance'] + $consumerlog['real_price'];
            $funds = $user_detail['funds'] - $consumerlog['real_price'];
            if($funds < 0){
                return msg(0, '', '确认失败');
            }
            //更改用户余额和冻结金额
            $user_detail_update = Db::name('user_detail')
                ->where(['uid'=>$consumerlog['seller_id']])
                ->setField(['balance'=>$balance,'funds'=>$funds]);
            if ($consumerlog_update && $user_detail_update) {
                // 提交事务
                Db::commit();
                return msg(1, '', '确认成功');
            } else {
                // 回滚事务
                Db::rollback();
                return msg(-1, '', '确认失败');
            }
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return msg(-2, '', '确认失败');
        }
    }

    /*
     * 提现记录
     */
    public function extractlog()
    {
        if (request()->isAjax()) {
            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $uid = session('id');
            $input_time_start = isset($param['input_time_start'])?strtotime($param['input_time_start']):'';
            $input_time_end = isset($param['input_time_end'])?strtotime($param['input_time_end'].'23:59:59'):'';
            $status = isset($param['status'])?$param['status']:'';
            $number = isset($param['number'])?$param['number']:'';
            $sqlmap = [];
            //查询某个时间之后
            if ($input_time_start != '' && $input_time_end == '') {
                $sqlmap['input_time'] = ['gt', $input_time_start];
            }
            //查询某个时间之前
            if ($input_time_start == '' && $input_time_end != '') {
                $sqlmap['input_time'] = ['lt', $input_time_end];
            }
            //查询某个入库时间段
            if($input_time_start != ''&& $input_time_end != ''){
                $sqlmap['input_time'] = ['between',[$input_time_start,$input_time_end]];
            }
            //查询状态
            if (!empty($status)) {
                $sqlmap['status'] = ['eq', $status];
            }
            //查询订单号
            if (!empty($number)) {
                $sqlmap['number'] = ['eq', $number];
            }
            $sqlmap['user_id'] = ['eq',$uid];
            $extractlog = new ExtractLogModel();
            $user = new UserModel();
            $selectResult = $extractlog->getExtractLogByWhere($sqlmap, $offset, $limit);

            $status = config('extract_status');
            // 拼装参数
            foreach ($selectResult as $key => $vo) {
                if(!empty($vo['update_time'])){
                    $selectResult[$key]['update_time'] = date('Y-m-d H:i:s', $vo['update_time']);
                }
                $selectResult[$key]['input_time'] = date('Y-m-d H:i:s', $vo['input_time']);
                $selectResult[$key]['user_id'] = $user->getOneRealName($vo['user_id']);
//                if($vo['status'] == 1){
//                    $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id'],4));
//                }
                $selectResult[$key]['status'] = $status[$vo['status']];
            }
            $return['total'] = $extractlog->getAllExtractLog($sqlmap);  //总数据
            $return['rows'] = $selectResult;
            return json($return);
        }
        return $this->fetch();
    }

    /*
     * 提现验证管理
     */
    public function extractlog_management()
    {
        if (request()->isAjax()) {
            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $uid = session('id');
            $input_time_start = isset($param['input_time_start'])?strtotime($param['input_time_start']):'';
            $input_time_end = isset($param['input_time_end'])?strtotime($param['input_time_end'].'23:59:59'):'';
            $status = isset($param['status'])?$param['status']:'';
            $number = isset($param['number'])?$param['number']:'';
            $sqlmap = [];
            //查询某个时间之后
            if ($input_time_start != '' && $input_time_end == '') {
                $sqlmap['input_time'] = ['gt', $input_time_start];
            }
            //查询某个时间之前
            if ($input_time_start == '' && $input_time_end != '') {
                $sqlmap['input_time'] = ['lt', $input_time_end];
            }
            //查询某个入库时间段
            if($input_time_start != ''&& $input_time_end != ''){
                $sqlmap['input_time'] = ['between',[$input_time_start,$input_time_end]];
            }
            //查询状态
            if (!empty($status)) {
                $sqlmap['status'] = ['eq', $status];
            }
            //查询订单号
            if (!empty($number)) {
                $sqlmap['number'] = ['eq', $number];
            }
//            $sqlmap['user_id'] = ['eq',$uid];
            $extractlog = new ExtractLogModel();
            $user = new UserModel();
            $selectResult = $extractlog->getExtractLogByWhere($sqlmap, $offset, $limit);

            $status = config('extract_status');
            // 拼装参数
            foreach ($selectResult as $key => $vo) {
                if(!empty($vo['update_time'])){
                    $selectResult[$key]['update_time'] = date('Y-m-d H:i:s', $vo['update_time']);
                }
                $selectResult[$key]['input_time'] = date('Y-m-d H:i:s', $vo['input_time']);
                $selectResult[$key]['user_id'] = $user->getOneRealName($vo['user_id']);
                if ($vo['status'] == 1){
                    $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id'],4));
                }else{
                    $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id'],5));
                }
                $selectResult[$key]['status'] = $status[$vo['status']];
            }
            $return['total'] = $extractlog->getAllExtractLog($sqlmap);  //总数据
            $return['rows'] = $selectResult;
            return json($return);
        }
        return $this->fetch();
    }

    /*
     * 提现验证
     */
    public function extractlog_verify()
    {
        if(request()->isPost()){
            $param = input('param.');
            $result = $this->validate($param, 'ExtractLogVerifyValidate');
            if (true !== $result) {
                // 验证失败 输出错误信息
                return msg(-1, '', $result);
            }
            $param['update_time'] = time();
            //开启事务
            Db::startTrans();
            try {
                //获取提现记录信息
                $extractlog = Db::name('extract_log')
                    ->where(['id'=>$param['id']])
                    ->find();
                //修改提现记录表
                $extractlog_update = Db::name('extract_log')
                    ->where(['id'=>$param['id']])
                    ->update($param);
                if($param['status'] == 2){
                    //提现成功 -- 清除冻结对应数值
                    $user_detail_update = Db::name('user_detail')
                        ->where(['uid'=>$extractlog['user_id']])
                        ->setDec('funds',$extractlog['money']);
                }else{
                    //提现失败 -- 冻结的对应数值返回至余额
                    $user_detail = Db::name('user_detail')
                        ->where(['uid'=>$extractlog['user_id']])
                        ->find();
                    $funds = $user_detail['funds'] - $extractlog['money'];
                    $balance = $user_detail['balance'] + $extractlog['money'];
                    $user_detail_update = Db::name('user_detail')
                        ->where(['uid'=>$extractlog['user_id']])
                        ->setField(['funds'=>$funds,'balance'=>$balance]);
                }
                if ($extractlog_update && $user_detail_update) {
                    // 提交事务
                    Db::commit();
                    return msg(1, url('personal/extractlog_management'), '验证成功');
                } else {
                    // 回滚事务
                    Db::rollback();
                    return msg(-1, '', '验证失败');
                }
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                return msg(-2, '', '验证失败');
            }
        }
        $id = input('param.id');
        $this->assign(['id' =>$id]);
        return $this->fetch();
    }

    /*
     * 删除提现记录
     */
    public function extractlog_del()
    {
        $id = input('param.id');
        $extractlog = new ExtractLogModel();
        $flag = $extractlog->delExtractLog($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }

    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButton($id,$status=0,$url='')
    {
        if($status == 0 || $status == 2){
            return [
                '查看' => [
                    'auth' => 'personal/prepaidlog_detail',
                    'href' => url('personal/prepaidlog_detail', ['id' => $id,'url'=>$url]),
                    'btnStyle' => 'primary',
                    'icon' => 'fa fa-paste',
                ]
            ];
        }else if($status == 1){
            return [
                '查看' => [
                    'auth' => 'personal/prepaidlog_detail',
                    'href' => url('personal/prepaidlog_detail', ['id' => $id,'url'=>$url]),
                    'btnStyle' => 'primary',
                    'icon' => 'fa fa-paste',
                ],
                '验证' => [
                    'auth' => 'personal/prepaidlog_verify',
                    'href' => url('personal/prepaidlog_verify', ['id' => $id]),
                    'btnStyle' => 'primary',
                    'icon' => 'fa fa-paste',
                ]
            ];
        }else if($status == 3){
            return [
                '查看' => [
                    'auth' => 'personal/prepaidlog_detail',
                    'href' => url('personal/prepaidlog_detail', ['id' => $id,'url'=>$url]),
                    'btnStyle' => 'primary',
                    'icon' => 'fa fa-paste',
                ],
                '删除' => [
                    'auth' => 'personal/prepaidlog_del',
                    'href' => "javascript:prepaidlog_del(" .$id .")",
                    'btnStyle' => 'danger',
                    'icon' => 'fa fa-trash-o'
                ]
            ];
        }else if ($status == 4){
            return [
                '验证' => [
                    'auth' => 'personal/extractlog_verify',
                    'href' => url('personal/extractlog_verify', ['id' => $id]),
                    'btnStyle' => 'primary',
                    'icon' => 'fa fa-paste',
                ]
            ];
        }else if($status == 5){
            return [
                '删除' => [
                    'auth' => 'personal/extractlog_del',
                    'href' => "javascript:extractlog_del(" .$id .")",
                    'btnStyle' => 'danger',
                    'icon' => 'fa fa-trash-o'
                ]
            ];
        }else if($status == 6){
            return [
                '确认收货' => [
                    'auth' => 'personal/consumerlog_confirm',
                    'href' => "javascript:consumerlog_confirm(" .$id .")",
                    'btnStyle' => 'primary',
                    'icon' => 'fa fa-paste'
                ]
            ];
        }
    }

}