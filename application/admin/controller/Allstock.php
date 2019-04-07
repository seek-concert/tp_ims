<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/3
 * Time: 18:13
 */

namespace app\admin\controller;
use app\admin\model\StockModel;
use app\admin\model\BunledModel;
use app\admin\model\ProductModel;
use app\admin\model\UserModel;
class Allstock extends Base
{


     /**
     *  function 库存总览页面 
     *  
     * 
     */
    public function index(){
        return view();
    }

    /**
     *  function 获取库存总览列表 
     *  
     * 
     */
    public function getallstock(){
        $stock_model = new StockModel();
        $bunled_model = new BunledModel();
        $product_model = new ProductModel();
        $user_model = new UserModel();
        $param = input('');
        $input_time_start = isset($param['input_time_start'])?strtotime($param['input_time_start']):'';
        $input_time_end = isset($param['input_time_end'])?strtotime($param['input_time_end']):'';
        $out_time_start = isset($param['out_time_start'])?strtotime($param['out_time_start']):'';
        $out_time_end = isset($param['out_time_end'])?strtotime($param['out_time_end']):'';
        $status = isset($param['status'])?(int)$param['status']:0;
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

        if(!empty($status)){
            $sqlmap['status'] = $status;
        }
        $lists = $stock_model->getAllStock($param['pageNumber'],$param['pageSize'],$sqlmap); 
        //整理返回数据
        foreach ($lists as $key => $value) {
            $bunled_name = $lists[$key]['bunled_name'] = $bunled_model->get_bunled_name($value['bunled_id']);
            $bunled_id = $bunled_model->get_bunled_id($value['bunled_id']);
            $product_name = $product_model->get_product_name($value['product_id']);
            $product_id = $product_model->get_product_id($value['product_id']);
            $lists[$key]['is_check'] = '<input name ="my_stock" value="'.$value['id'].'"  type="checkbox"';
            if($value['is_check'] == 1){
                $lists[$key]['is_check'] .= 'checked';
            }
            $lists[$key]['is_check'] .=' /> </div>';
            switch ($value['status']) {
                case '-1':
                    $lists[$key]['status'] ='交易失败';
                    break;
                
                case '1':
                    $lists[$key]['status'] ='未使用';
                    break;
                case '2':
                    $lists[$key]['status'] ='使用中';
                    break;
                case '3':
                    $lists[$key]['status'] ='发布中';
                    break;
                case '4':
                    $lists[$key]['status'] ='已出库';
                    break;
                case '5':
                    $lists[$key]['status'] ='出库失败';
                    break;
            }
            if(!empty($value['user'])){
                $lists[$key]['user'] =  $user_model->getOneRealName($value['user']);
            }else{
                $lists[$key]['user'] = '';
            }
            if(!empty($value['out_user'])){
                $lists[$key]['out_user'] =  $user_model->getOneRealName($value['out_user']);
            }else{
                $lists[$key]['user'] = '';
            }
            $lists[$key]['pid'] = ' <a href="javascript:;" onclick="edit_pid(\''.$value['id'].'\',\''.$product_id.'\',\''.$product_name.'\')">修改PID</a> ';
            $lists[$key]['uid'] = ' <a href="javascript:;" onclick="edit_uid(\''.$value['id'].'\',\''.$bunled_id.'\',\''.$bunled_name.'\')">修改UID</a> ';
            $lists[$key]['operate'] = showOperate($this->makeButton($value['id']));
        }

        $return['total'] = $stock_model->getAllStockCount($sqlmap);  //总数据
        $return['rows'] = $lists;
        return json($return);
       
    }

    /**
     *  function 查看单条详情 
     *  
     * 
     */
    public function stock_detail($id = 0){
        $stock_model = new StockModel();
        $product_model = new ProductModel();
        $bunled_model = new BunledModel();
        $user_model = new UserModel();
        if($id == 0){
            $this->error('请勿非法访问');
        }
        $sqlmap = [];
        $sqlmap['id'] = $id;
        $info = $stock_model->getOneStock($sqlmap);
        $info['bname'] = $bunled_model->get_bunled_name($info['bunled_id']);
        $info['bid'] = $bunled_model->get_bunled_id($info['bunled_id']);
        $info['pname'] = $product_model->get_product_name($info['product_id']);
        $info['pid'] = $product_model->get_product_name($info['product_id']);
        if($info['user']){
            $info['user'] =  $user_model->getOneRealName($info['user']);
        }else{
            $info['user'] = '';
        }
        if($info['out_user']){
            $info['out_user'] =  $user_model->getOneRealName($info['out_user']);
        }else{
            $info['out_user'] = '';
        }

        if($info['input_user']){
            $info['input_user'] =  $user_model->getOneRealName($info['input_user']);
        }else{
            $info['input_user'] = '';
        }
        if($info['input_time']){
            $info['input_time'] = date('Y-m-d H:i:s',$info['input_time']);
        }
        if($info['out_time']){
            $info['out_time'] = date('Y-m-d H:i:s',$info['out_time']);
        }
        $return_data = [];
        $return_data['info'] = $info;
        return view('',$return_data);
    }
    /**
     *  function 更改成功状态 
     *  
     * 
     */
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
    /**
     *  function 回滚状态 
     *  
     * 
     */
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

/**
     *  function 删除单条记录 
     *  
     * 
     */
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
    /**
     *  function 是否开启检测 
     *  
     * 
     */
    public function get_check(){
        $stock_model = new StockModel();
        $param = input('');
        $id = isset($param['id'])?$param['id']:0;
        if($id == 0){
            $this->error('请勿非法访问');
        }
        $info = $stock_model->where(['id'=>$id])->find();
        if(!empty($info['is_check']) && $info['is_check'] == 1){
            $status = -1;
        }else{
            $status = 1;
        }
       
       $stock_model->where(['id'=>$id])->setField('is_check',$status);
    }
    /**
     *  function 修改pid 
     *  
     * 
     */
    public function get_edit_pid(){
        $product_model = new ProductModel();
        $param =input('post.');
        $id = isset($param['id'])?(int)$param['id']:0;
        if($id == 0){
            $this->error('请勿非法访问');
        }
        $ret = $product_model->update_data(['id'=>$id],['pname'=>$param['pname']]);
      
        if($ret){
            $this->success('修改成功');
        }else{
            $this->error('修改出错');
        }
    }

/**
     *  function 修改uid 
     *  
     * 
     */
    public function get_edit_uid(){
        $bunled_model = new BunledModel();
        $param =input('post.');
        $id = isset($param['id'])?(int)$param['id']:0;
        if($id == 0){
            $this->error('请勿非法访问');
        }
        $ret = $bunled_model->update_data(['id'=>$id],['bname'=>$param['bname']]);
        if($ret){
            $this->success('修改成功');
        }else{
            $this->error('修改出错');
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