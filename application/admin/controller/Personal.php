<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/6
 * Time: 10:28
 */

namespace app\admin\controller;


use app\admin\model\PrepaidLogModel;
use app\admin\model\UserModel;

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
            $prepaidlog = new PrepaidLogModel();
            $flag = $prepaidlog->VerifyPrepaidLog($param);
            return json(msg($flag['code'], $flag['data'], $flag['msg']));
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
        }

    }

}