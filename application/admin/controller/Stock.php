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

use app\admin\model\StockModel;
use app\admin\model\ProductModel;
use app\admin\model\BunledModel;
use app\admin\model\UserModel;
class Stock extends Base
{
    // 库存列表(总览)
    public function index()
    {
      
        return $this->fetch();
    }

    /**
     *  function 获取库存总览列表 
     *  
     * 
     */
    public function get_stock(){
        $stock_model = new StockModel();
        $bunled_model = new BunledModel();
        $product_model = new ProductModel();
        $user_model = new UserModel();
        $param = input('param.');
        //获取条件值
        $limit = isset($param['pageSize'])?(int)$param['pageSize']:0;
        $page = isset($param['pageNumber'])?(int)$param['pageNumber']:0;
        $pname = isset($param['pname'])?$param['pname']:'';
        $sqlamp = [];
        //组装条件
        if (!empty($pname)) {
            $searchsql['bname'] = ['like', '%' . $pname . '%'];
            $bunled_ids = $bunled_model->get_like_name($searchsql);
            $sqlamp['bunled_id'] = ['in',$bunled_ids];
        }

        
        $id = session('id');
        $uid = $this->get_user($id);
        $sqlamp['input_user'] = ['in',$uid];
        
        $selectResult = $stock_model->getStockGroup($page, $limit, $sqlamp,'product_id,status,bunled_id');
        //组装数据
        foreach ($selectResult as $key => $value) {
            $selectResult[$key]['bname'] = $bunled_model->get_bunled_name($value['bunled_id']);
            $selectResult[$key]['pname'] = $product_model->get_product_name($value['product_id']);
            $selectResult[$key]['all_count'] = $selectResult[$key]['count'];
            $selectResult[$key]['out_count'] = $stock_model->where($sqlamp)->where(['product_id'=>$value['product_id'],'bunled_id'=>$value['bunled_id'],'status'=>4])->count();
            $selectResult[$key]['not_use_count'] = $stock_model->where($sqlamp)->where(['product_id'=>$value['product_id'],'bunled_id'=>$value['bunled_id'],'status'=>1])->count();;
      
        }
        $return['total'] = $stock_model->getStockGroupCount($sqlamp,'product_id,status');  // 总数据
        $return['rows'] = array_values($selectResult);
        return json($return);
    }

    // 我的库存
    public function selfStock()
    {
        //实例化模型
        $stock_model = new StockModel();
        $user_model = new UserModel();
        //获取所有子用户列表
        $id = session('id');
        $uid = $this->get_user($id);
        $all_price = $stock_model->where('input_user','in',$uid)->sum('tprice');
        $child_lists = $user_model->get_child_lists($id);
        $return_data = [];
        $return_data['all_price'] = $all_price;
        $return_data['child_lists'] = $child_lists;
        return view('',$return_data);
    }

    /**
     *  function 获取我的库存列表 
     *  
     * 
     */
    public function get_self_stock(){
        $stock_model = new StockModel();
        $bunled_model = new BunledModel();
        $user_model = new UserModel();
        $param = input('param.');
        //传递数据
        $limit = isset($param['pageSize'])?(int)$param['pageSize']:0;
        $page = isset($param['pageNumber'])?(int)$param['pageNumber']:0;
        $keywords = isset($param['keywords'])?$param['keywords']:'';
        $out_user = isset($param['out_user'])?(int)$param['out_user']:0;
        $input_user = isset($param['input_user'])?(int)$param['input_user']:0;
        $status = isset($param['status'])?(int)$param['status']:0;
        $id = session('id');
        $pid = $user_model->get_user_one_data($id,'pid');
        $power = $user_model->get_user_one_data($id,'power');
        $uid = $this->get_user($id);
        //组装查询条件
        $sqlamp = [];
        if(!empty($out_user)){
            $sqlamp['out_user'] = $out_user;
        }
        if(!empty($input_user)){
            $sqlamp['input_user'] = $input_user;
        }else{
            if(!$pid){
                $sqlamp['input_user|out_user'] = ['in',$uid];
            }else{
               
                if($power == 1){
                  
                    $sqlamp['input_user'] = $id;
                }else{
                  
                    $sqlamp['out_user'] = $id;
                }
            }
            

        }
        if(!empty($status)){
            $sqlamp['status'] = $status;
        }
        if(!empty($keywords)){
            $searchsql['bname'] = ['like', '%' . $keywords . '%'];
            $bunled_ids = $bunled_model->get_like_name($searchsql);
          
           $sqlamp['bunled_id'] = ['in',$bunled_ids];
        }
       
        $selectResult = $stock_model->getAllStock($page, $limit, $sqlamp);
      
        //组装列表数据
        foreach ($selectResult as $key => $value) {
            $selectResult[$key]['bname'] = $bunled_model->get_bunled_name($value['bunled_id']);
            $selectResult[$key]['input_time'] = !empty($selectResult[$key]['input_time'])?date('Y-m-d H:i:s'):'';
            $selectResult[$key]['out_time'] = !empty($selectResult[$key]['out_time'])?date('Y-m-d H:i:s'):'';
            $selectResult[$key]['input_user'] = $user_model->get_user_one_data($selectResult[$key]['input_user'],'user_name');
            $selectResult[$key]['out_user'] = $user_model->get_user_one_data($selectResult[$key]['out_user'],'user_name');
            
            switch ($selectResult[$key]['status']) {
                case '-1':
                    $selectResult[$key]['status'] ='交易失败';
                    break;
                
                case '1':
                    $selectResult[$key]['status'] ='未使用';
                    break;
                case '2':
                    $selectResult[$key]['status'] ='使用中';
                    break;
                case '3':
                    $selectResult[$key]['status'] ='发布中';
                    break;
                case '4':
                    $selectResult[$key]['status'] ='已出库';
                    break;
                case '5':
                    $selectResult[$key]['status'] ='出库失败';
                    break;
            }
        }
        $return['total'] = $stock_model->getAllStockCount($sqlamp);  // 总数据
        $return['rows'] = array_values($selectResult);
        return json($return);
    }

  
    // Pid改名
    public function pidRename()
    {
        return view();
    }

    //获取所有产品列表
    public function getpname(){
       
        $stock_model = new ProductModel();
        $param = input('param.');
        $page = isset($param['pageNumber'])?(int)$param['pageNumber']:1;
        $limit = isset($param['pageSize'])?(int)$param['pageSize']:10;
        $keywords = isset($param['keywords'])?$param['keywords']:'';
        $sqlmap = [];
        if(!empty($keywords)){
            $sqlmap['pid|pname'] = ['like', '%' . $keywords . '%'];
        }

        $lists = $stock_model->getAllProduct($page, $limit, $sqlmap);
        foreach ($lists as $key => $value) {
            $lists[$key]['operate'] = showOperate($this->makeButton($value['id']));
        }
        $return['total'] = $stock_model->getAllProductCount($sqlmap);  // 总数据
        $return['rows'] = $lists;
        return json($return);
    }


    //获取单个产品信息
    public function get_product_info(){
        $product_model = new ProductModel();
        $param = input('');
        $id = isset($param['id'])?(int)$param['id']:0;
        if(empty($id)){
            $this->error('请勿非法访问');
        }
        $sqlmap = [];
        $sqlmap['id'] = $id;
        $info = $product_model->getProductInfo($sqlmap);
        return json($info);
    }
    //修改产品名称
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
    //删除产品
    public function del($id = 0){
        $product_model = new ProductModel();
        $stock_model = new StockModel();
        if(empty((int)$id)){
            $this->error('请勿非法访问');
        }
        
        $is_stock = $stock_model->getAllStockCount(['product_id'=>$id]);
        if($is_stock > 0){
            $this->error('该档位下还有库存,不能直接删除哦');
        }
        $sqlmap = [];
        $sqlmap['id'] = $id;
        $ret = $product_model->del($sqlmap);
        if($ret){
            $this->success('删除成功');
        }else{
            $this->error('删除出错,请重试');
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
            '修改' => [
                'auth' => 'user/useredit',
                'href' => "javascript:edit_pid(" .$id .")",
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste',
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