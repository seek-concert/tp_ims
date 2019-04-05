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
class Stock extends Base
{
    // 库存列表(总览)
    public function index()
    {
      

          

        return $this->fetch();
    }


    public function get_stock(){
        $stock_model = new StockModel();
        $bunled_model = new BunledModel();
        $product_model = new ProductModel();
        $param = input('param.');

        $limit = isset($param['pageSize'])?(int)$param['pageSize']:0;
        $page = isset($param['pageNumber'])?(int)$param['pageNumber']:0;
        $pname = isset($param['pname'])?$param['pname']:'';
        $sqlamp = [];

        if (!empty($pname)) {
            $searchsql['bname'] = ['like', '%' . $pname . '%'];
            $bunled_ids = $bunled_model->get_like_name($searchsql);
           $sqlamp['bunled_id'] = ['in',$bunled_ids];
        }
        
        
        $selectResult = $stock_model->getStockGroup($page, $limit, $sqlamp,'product_id,status');
        foreach ($selectResult as $key => $value) {
            $selectResult[$key]['bname'] = $bunled_model->get_bunled_name($value['bunled_id']);
            $selectResult[$key]['pname'] = $product_model->get_product_name($value['product_id']);
            $selectResult[$key]['count'] = '【'.$selectResult[$key]['count'].'】条库存';
            switch ($selectResult[$key]['status']) {
                case '-1':
                    $selectResult[$key]['status'] = '交易失败';
                    break;
                 case '1':
                    $selectResult[$key]['status'] = '未使用';
                    break;
                case '2':
                    $selectResult[$key]['status'] = '使用中';
                    break;
                case '3':
                    $selectResult[$key]['status'] = '发布中';
                    break;
                case '4':
                    $selectResult[$key]['status'] = '已出库';
                    break;
            }
        }
        $return['total'] = $stock_model->getStockGroupCount($sqlamp,'product_id,status');  // 总数据
        $return['rows'] = array_values($selectResult);
        return json($return);
    }

    // 我的库存
    public function selfStock()
    {
        return "this is selfStock";
    }


  
    // Pid改名
    public function pidRename()
    {
        return view();
    }

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