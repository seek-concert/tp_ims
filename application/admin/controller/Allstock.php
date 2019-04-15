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
use app\admin\model\PriceModel;
class Allstock extends Base
{


     /**
     *  function 库存总览页面 
     *  
     * 
     */
    public function index(){
        $user_model = new UserModel();
        $user_lists = $user_model->where(['pid'=>0,'id'=>['neq',1]])->column('real_name','id');
        $return_data = [];
        $return_data['user_lists'] = $user_lists;
        return view('',$return_data);
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
        $price_model = new PriceModel();
        $param = input('');
        $input_time_start = isset($param['input_time_start'])?strtotime($param['input_time_start']):'';
        $input_time_end = isset($param['input_time_end'])?strtotime($param['input_time_end']):'';
        $out_time_start = isset($param['out_time_start'])?strtotime($param['out_time_start']):'';
        $out_time_end = isset($param['out_time_end'])?strtotime($param['out_time_end']):'';
        $status = isset($param['status'])?(int)$param['status']:0;
        $userid = isset($param['userid'])?(int)$param['userid']:0;
        $stock_id = isset($param['stock_id'])?(int)$param['stock_id']:0;
        $search_pid = isset($param['search_pid'])?$param['search_pid']:'';
        $search_uid = isset($param['search_uid'])?$param['search_uid']:'';

        $sqlmap = [];
        //查询某个入库时间之后
        if($input_time_start != ''&& $input_time_end == ''){
            $sqlmap['input_time'] = ['gt',$input_time_start];
        }
        //查询某个入库时间之前
        if($input_time_start == ''&& $input_time_end != ''){
            $sqlmap['input_time'] = ['lt',$input_time_end+86399];
        }
        //查询某个入库时间段
        if($input_time_start != ''&& $input_time_end != ''){
            $sqlmap['input_time'] = ['between',[$input_time_start,$input_time_end+86399]];
        }
        //查询某个出库时间之后
        if($out_time_start != ''&& $out_time_end == ''){
            $sqlmap['outx_time'] = ['gt',$out_time_start];
        }
        //查询某个出库时间之前
        if($out_time_start == ''&& $out_time_end != ''){
            $sqlmap['out_time'] = ['lt',$out_time_end+86399];
        }
        //查询某个出库时间段
        if($out_time_start != ''&& $out_time_end != ''){
            $sqlmap['out_time'] = ['between',[$out_time_start,$out_time_end+86399]];
        }
        //状态
        if(!empty($status)){
            $sqlmap['status'] = $status;
        }
        //管理员
        if(!empty($userid)){
            $uid = $this->get_user($userid);
            $sqlmap['user|input_user'] = ['in',$uid];
        }
        //库存id
        if(!empty($stock_id)){
            $sqlmap['id'] = $stock_id;
        }
        if(!empty($search_pid)){
            $product_id = $product_model->where(['pname'=> ['like','%'.$search_pid.'%']])->value('id');
            $sqlmap['product_id'] = $product_id;
        }

        if(!empty($search_uid)){
            $bunled_id = $bunled_model->where(['bname'=>['like','%'.$search_uid.'%']])->value('id');
            $sqlmap['bunled_id'] = $bunled_id;
        }
        if($out_time_end != '' || $status == 4){
            $lists = $stock_model->getAllStockOutDesc($param['pageNumber'],$param['pageSize'],$sqlmap); 
        }else{
            $lists = $stock_model->getAllStock($param['pageNumber'],$param['pageSize'],$sqlmap,'input_time desc');
        }
       
        //整理返回数据
        foreach ($lists as $key => $value) {
            $bunled_name = $lists[$key]['bunled_name'] = $bunled_model->get_bunled_name($value['bunled_id']);
            $bunled_id = $bunled_model->get_bunled_id($value['bunled_id']);
            $product_name = $lists[$key]['product_name'] =$product_model->get_product_name($value['product_id']);
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
            if(!empty($value['pristine_user'])){
                $lists[$key]['pristine_user'] =  $user_model->getOneRealName($value['pristine_user']);
            }
            if($value['input_time']){
                $lists[$key]['input_time'] =  date('Y-m-d H:i:s',$value['input_time']);
            }else{
                $lists[$key]['input_time'] = '';
            }
            if($value['out_time']){
                $lists[$key]['out_time'] =  date('Y-m-d H:i:s',$value['out_time']);
            }else{
                $lists[$key]['out_time'] = '';
            }
            $pid = $product_model->get_product_id($value['product_id']);
            $bid = $bunled_model->get_bunled_id($value['bunled_id']);
            $stock_product_id = $value['product_id'];
            $stock_bunled_id = $value['bunled_id'];
            $product_name = str_replace("'","\'",$product_name);
            $bunled_name = str_replace("'","\'",$bunled_name);
            $lists[$key]['excel_price'] = $price_model->get_one_data(['pid'=>$pid,'bid'=>$bid],'price');
            $lists[$key]['tprice'] = '<span class="show_value">'.$value['tprice'].'</span><span class="edit_value"><input type="text" value="'.$value['tprice'].'" id="save_tprice"><button class="btn btn-primary" onclick="save_tprice('.$value['id'].',this)">保存</button></span>';
            $lists[$key]['pid'] = " <a href=\"javascript:;\" onclick=\"edit_pid('$stock_product_id','$product_id','$product_name')\">修改PID</a> ";
//            $lists[$key]['pid'] = ' <a href="javascript:;" onclick="edit_pid(\''.$value['product_id'].'\',\''.$product_id.'\',\''.$product_name.'\')">修改PID</a> ';
            $lists[$key]['uid'] = " <a href=\"javascript:;\" onclick=\"edit_uid('$stock_bunled_id','$bunled_id','$bunled_name')\">修改UID</a> ";
//            $lists[$key]['uid'] = ' <a href="javascript:;" onclick="edit_uid(\''.$value['bunled_id'].'\',\''.$bunled_id.'\',\''.$bunled_name.'\')">修改UID</a> ';
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
        $user_lists = $user_model->where(['pid'=>0,'id'=>['neq',1]])->column('real_name','id');
        $return_data['user_lists'] = $user_lists;
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
     *  function 修改入库价格 
     *  
     * 
     */
    public function edit_tprice(){
        $param = input('post.');
        $id = $param['id'];
        $value = $param['save_tprice'];
        $stock_model = new StockModel();
        $stock_model->where(['id'=>$id])->update(['tprice'=>$value]);
    }

    /**
     *  function 修改库存所有人 
     *  
     * 
     */
    public function edit_stock_user(){
        $stock_model = new StockModel();
        $param = input('');
        $id = $param['id'];
        $userid = $param['user'];
        $ret = $stock_model->where(['id'=>$id])->update(['input_user'=>$userid]);
        if(!$ret){
            $this->error('修改出错,请重试');
        }else{
            $this->success('修改成功');
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