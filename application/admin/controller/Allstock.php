<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/3
 * Time: 18:13
 */

namespace app\admin\controller;
use app\admin\model\StockModel;


class Allstock extends Base
{

    public function index(){
        return view();
    }

    public function getallstock(){
        $stock_model = new StockModel();
        $param = input('');
        $input_time_start = isset($param['input_time_start'])?strtotime($param['input_time_start']):'';
        $input_time_end = isset($param['input_time_end'])?strtotime($param['input_time_end']):'';
        $out_time_start = isset($param['out_time_start'])?strtotime($param['out_time_start']):'';
        $out_time_end = isset($param['out_time_end'])?strtotime($param['out_time_end']):'';
        $sqlmap = [];
        //查询某个入库时间之后
        if($input_time_start != ''&& $input_time_end == ''){
            $sqlmap['input_time'] = ['gt',$input_time_start];
        }
        //查询某个入库时间之前
        if($input_time_start == ''&& $input_time_end != ''){
            $sqlmap['input_time'] = ['lt',$input_time_end];
        }
        //查询某个入库时间段
        if($input_time_start != ''&& $input_time_end != ''){
            $sqlmap['input_time'] = ['between',[$input_time_start,$input_time_end]];
        }
        //查询某个出库时间之后
        if($out_time_start != ''&& $out_time_end == ''){
            $sqlmap['out_time'] = ['gt',$out_time_start];
        }
        //查询某个出库时间之前
        if($out_time_start == ''&& $out_time_end != ''){
            $sqlmap['out_time'] = ['lt',$out_time_end];
        }
        //查询某个出库时间段
        if($out_time_start != ''&& $out_time_end != ''){
            $sqlmap['out_time'] = ['between',[$out_time_start,$out_time_end]];
        }
        
        $lists = $stock_model->getAllStock($param['pageNumber'],$param['pageSize'],$sqlmap); 
        foreach ($lists as $key => $value) {
            $lists[$key]['is_check'] = '<div class="switch" data-animated="false"  data-on-label="启用" data-off-label="禁用"> <input name ="my_stock"  type="checkbox"';
            if($value['is_check'] == 1){
                $lists[$key]['is_check'] .= 'checked';
            }
            $lists[$key]['is_check'] .=' /> </div>';
            $lists[$key]['pid'] = ' <a>修改PID</a> ';
            $lists[$key]['uid'] = ' <a>修改UID</a> ';
            $lists[$key]['operate'] = showOperate($this->makeButton($value['id']));
        }

        $return['total'] = $stock_model->getAllStockCount($sqlmap);  //总数据
        $return['rows'] = $lists;
        return json($return);
       
    }


    public function stock_detail($id = 0){
        $stock_model = new StockModel();
        if($id == 0){
            $this->error('请勿非法访问');
        }
        $sqlmap = [];
        $sqlmap['id'] = $id;
        $info = $stock_model->getOneStock();
        $return_data = [];
        $return_data['info'] = $info;
        return view('',$return_data);
    }

    public function get_succ(){
        $stock_model = new StockModel();
        $param = input('post.');
        $id = isset($param['id'])?$param['id']:0;
        if($id == 0){
            $this->error('请勿非法访问');
        }
        $ret = $stock_model->where(['id'=>$id])->setField('status',4);
        if($ret){
            $this->success('修改成功');
        }else{
            $this->error('修改出错,请重试');
        }
        
    }

    public function get_return(){
        $stock_model = new StockModel();
        $param = input('post.');
        $id = isset($param['id'])?$param['id']:0;
        if($id == 0){
            $this->error('请勿非法访问');
        }
        $ret = $stock_model->where(['id'=>$id])->setField('status',1);
        if($ret){
            $this->success('修改成功');
        }else{
            $this->error('修改出错,请重试');
        }
        
    }


    public function del(){
        $stock_model = new StockModel();
        $param = input('');
        $id = isset($param['id'])?$param['id']:0;
        if($id == 0){
            $this->error('请勿非法访问');
        }
        $ret = $stock_model->where(['id'=>$id])->delete();
        if($ret){
            $this->success('删除成功');
        }else{
            $this->error('删除出错,请重试');
        }
        
    }

    public function get_check(){
        $stock_model = new StockModel();
        $param = input('');
        $id = isset($param['id'])?$param['id']:0;
        if($id == 0){
            $this->error('请勿非法访问');
        }
        $info = $stock_model->where(['id'=>$id])->find();
        if($info['status'] == 1){
            $status = -1;
        }else{
            $status = 1;
        }
       $stock_model->where(['id'=>$id])->setField('status',$status);
       
    }




    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButton($id)
    {
        return [
            '查看' => [
                'auth' => 'user/useredit',
                'href' => url('allstock/stock_detail', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste',
            ],
            '成功' => [
                'auth' => 'user/userdel',
                'href' => "javascript:get_succ(" .$id .")",
                'btnStyle' => 'success',
                'icon' => 'glyphicon glyphicon-pencil'
            ],
            '还原状态' => [
                'auth' => 'user/userdel',
                'href' => "javascript:get_return(" .$id .")",
                'btnStyle' => 'warning',
                'icon' => 'glyphicon glyphicon-repeat'
            ],
            '删除' => [
                'auth' => 'user/userdel',
                'href' => "javascript:stockDel(" .$id .")",
                'btnStyle' => 'danger',
                'icon' => 'glyphicon glyphicon-trash'
            ]
        ];
    }
}