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

use app\admin\model\PriceModel;
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
    public function get_stock()
    {
        $stock_model = new StockModel();
        $bunled_model = new BunledModel();
        $product_model = new ProductModel();
        $user_model = new UserModel();
        $param = input('param.');
        //获取条件值
        $limit = isset($param['pageSize']) ? (int)$param['pageSize'] : 0;
        $page = isset($param['pageNumber']) ? (int)$param['pageNumber'] : 0;
        $pname = isset($param['pname']) ? $param['pname'] : '';

        $sqlamp = [];
        //组装条件
        if (!empty($pname)) {
            $searchsql['bname'] = ['like', '%' . $pname . '%'];
            $bunled_ids = $bunled_model->get_like_name($searchsql);
            $sqlamp['bunled_id'] = ['in', $bunled_ids];
        }


        $id = session('id');
        $uid = $this->get_user($id);
        $sqlamp['input_user'] = ['in', $uid];

        $selectResult = $stock_model->getStockGroup($page, $limit, $sqlamp, 'product_id,bunled_id');
        //组装数据
        foreach ($selectResult as $key => $value) {
            $selectResult[$key]['bname'] = $bunled_model->get_bunled_name($value['bunled_id']);
            $selectResult[$key]['pname'] = $product_model->get_product_name($value['product_id']);
            $selectResult[$key]['all_count'] = $selectResult[$key]['count'];
            $selectResult[$key]['out_count'] = $stock_model->where($sqlamp)->where(['product_id' => $value['product_id'], 'bunled_id' => $value['bunled_id'], 'status' => 4])->count();
            $selectResult[$key]['not_use_count'] = $stock_model->where($sqlamp)->where(['product_id' => $value['product_id'], 'bunled_id' => $value['bunled_id'], 'status' => 1])->count();;

        }
        $return['total'] = $stock_model->getStockGroupCount($sqlamp, 'product_id,status');  // 总数据
        $return['rows'] = array_values($selectResult);
        return json($return);
    }

    // 我的库存
    public function selfstock()
    {
        //实例化模型
        $stock_model = new StockModel();
        $user_model = new UserModel();
        $price_model = new PriceModel();
        //获取所有子用户列表
        $id = session('id');
        $uid = $this->get_user($id);
        //获取相关的库存
        $sqlamp['input_user|out_user'] = ['in', $uid];
        $stocks = $stock_model->where($sqlamp)->field('bunled_id,product_id,status')->select();
        //计算面值
        $not_price = 0;
        $all_price = 0;
        foreach ($stocks as $k => $v) {
            if ($v['status'] == 1) {
                $not_price += $price_model->get_one_data(['pid' => $v['product_id'], 'bid' => $v['bunled_id']], 'price');
            }
            $all_price += $price_model->get_one_data(['pid' => $v['product_id'], 'bid' => $v['bunled_id']], 'price');
        }
        $child_lists = $user_model->get_child_lists($id);
        $return_data = [];
        $return_data['not_price'] = $not_price;
        $return_data['all_price'] = $all_price;
        $return_data['child_lists'] = $child_lists;
        return view('', $return_data);
    }

    /**
     *  function 获取我的库存列表
     *
     *
     */
    public function get_self_stock()
    {
        $stock_model = new StockModel();
        $bunled_model = new BunledModel();
        $product_model = new ProductModel();
        $product_model = new ProductModel();
        $user_model = new UserModel();
        $price_model = new PriceModel();
        $param = input('param.');
        //传递数据
        $limit = isset($param['pageSize']) ? (int)$param['pageSize'] : 0;
        $page = isset($param['pageNumber']) ? (int)$param['pageNumber'] : 0;
        $keywords = isset($param['keywords']) ? $param['keywords'] : '';
        $out_user = isset($param['out_user']) ? (int)$param['out_user'] : 0;
        $input_user = isset($param['input_user']) ? (int)$param['input_user'] : 0;
        $status = isset($param['status']) ? (int)$param['status'] : 0;
        $input_time_start = isset($param['input_time_start']) ? strtotime($param['input_time_start']) : '';
        $input_time_end = isset($param['input_time_end']) ? strtotime($param['input_time_end']) : '';
        $out_time_start = isset($param['out_time_start']) ? strtotime($param['out_time_start']) : '';
        $out_time_end = isset($param['out_time_end']) ? strtotime($param['out_time_end']) : '';
        $id = session('id');
        $pid = $user_model->get_user_one_data($id, 'pid');
        $power = $user_model->get_user_one_data($id, 'power');
        $uid = $this->get_user($id);
        //组装查询条件
        $sqlamp = [];
        if (!empty($out_user)) {
            $sqlamp['out_user'] = $out_user;
        }
        if (!empty($input_user)) {
            $sqlamp['input_user'] = $input_user;
        } else {
            if (!$pid) {
                $sqlamp['input_user|out_user'] = ['in', $uid];
            } else {

                if ($power == 1) {

                    $sqlamp['input_user'] = $id;
                } else {
                    $sqlamp['out_user'] = $id;
                }
            }
        }
        //查询某个入库时间之后
        if ($input_time_start != '' && $input_time_end == '') {
            $sqlamp['input_time'] = ['gt', $input_time_start];
        }
        //查询某个入库时间之前
        if ($input_time_start == '' && $input_time_end != '') {
            $sqlamp['input_time'] = ['lt', $input_time_end];
        }
        //查询某个入库时间段
        if ($input_time_start != '' && $input_time_end != '') {
            $sqlamp['input_time'] = ['between', [$input_time_start, $input_time_end]];
        }
        //查询某个出库时间之后
        if ($out_time_start != '' && $out_time_end == '') {
            $sqlamp['outx_time'] = ['gt', $out_time_start];
        }
        //查询某个出库时间之前
        if ($out_time_start == '' && $out_time_end != '') {
            $sqlamp['out_time'] = ['lt', $out_time_end];
        }
        //查询某个出库时间段
        if ($out_time_start != '' && $out_time_end != '') {
            $sqlamp['out_time'] = ['between', [$out_time_start, $out_time_end]];
        }
        if (!empty($status)) {
            $sqlamp['status'] = $status;
        }
        if (!empty($keywords)) {
            $searchsql['bname'] = ['like', '%' . $keywords . '%'];
            $bunled_ids = $bunled_model->get_like_name($searchsql);

            $sqlamp['bunled_id'] = ['in', $bunled_ids];
        }

        if ($status == 4) {
            $selectResult = $stock_model->getAllStockOutDesc($page, $limit, $sqlamp);
        } else {
            $selectResult = $stock_model->getAllStock($page, $limit, $sqlamp, 'input_time desc');
        }

        //组装列表数据
        foreach ($selectResult as $key => $value) {
            $selectResult[$key]['bname'] = $bunled_model->get_bunled_name($value['bunled_id']);
            $selectResult[$key]['pname'] = $product_model->get_product_name($value['product_id']);
            $selectResult[$key]['input_time'] = !empty($value['input_time']) ? date('Y-m-d H:i:s', $value['input_time']) : '';
            $selectResult[$key]['out_time'] = !empty($value['out_time']) ? date('Y-m-d H:i:s', $value['out_time']) : '';
            $selectResult[$key]['input_user'] = $user_model->get_user_one_data($selectResult[$key]['input_user'], 'real_name');
            $selectResult[$key]['out_user'] = $user_model->get_user_one_data($selectResult[$key]['out_user'], 'real_name');
            $pid = $product_model->get_product_id($value['product_id']);
            $bid = $bunled_model->get_bunled_id($value['bunled_id']);
            $selectResult[$key]['excel_price'] = $price_model->get_one_data(['pid' => $pid, 'bid' => $bid], 'price');
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
                case '5':
                    $selectResult[$key]['status'] = '出库失败';
                    break;
            }
        }
        $return['total'] = $stock_model->getAllStockCount($sqlamp);  // 总数据
        $return['rows'] = array_values($selectResult);
        return json($return);
    }


    // Pid改名
    public function pidrename()
    {
        return view();
    }

    //获取所有产品列表
    public function getpname()
    {

        $stock_model = new ProductModel();
        $param = input('param.');
        $page = isset($param['pageNumber']) ? (int)$param['pageNumber'] : 1;
        $limit = isset($param['pageSize']) ? (int)$param['pageSize'] : 10;
        $keywords = isset($param['keywords']) ? $param['keywords'] : '';
        $sqlmap = [];
        if (!empty($keywords)) {
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
    public function get_product_info()
    {
        $product_model = new ProductModel();
        $param = input('');
        $id = isset($param['id']) ? (int)$param['id'] : 0;
        if (empty($id)) {
            $this->error('请勿非法访问');
        }
        $sqlmap = [];
        $sqlmap['id'] = $id;
        $info = $product_model->getProductInfo($sqlmap);
        return json($info);
    }

    //修改产品名称
    public function get_edit_pid()
    {
        $product_model = new ProductModel();
        $param = input('post.');
        $id = isset($param['id']) ? (int)$param['id'] : 0;
        if ($id == 0) {
            $this->error('请勿非法访问');
        }
        $ret = $product_model->update_data(['id' => $id], ['pname' => $param['pname']]);

        if ($ret) {
            $this->success('修改成功');
        } else {
            $this->error('修改出错');
        }
    }

    //删除产品
    public function del($id = 0)
    {
        $product_model = new ProductModel();
        $stock_model = new StockModel();
        if (empty((int)$id)) {
            $this->error('请勿非法访问');
        }

        $is_stock = $stock_model->getAllStockCount(['product_id' => $id]);
        if ($is_stock > 0) {
            $this->error('该档位下还有库存,不能直接删除哦');
        }
        $sqlmap = [];
        $sqlmap['id'] = $id;
        $ret = $product_model->del($sqlmap);
        if ($ret) {
            $this->success('删除成功');
        } else {
            $this->error('删除出错,请重试');
        }
    }

    //excel导出
    public function exportExcel()
    {
        if (request()->isPost()) {
            //获取数据
            $param = input('param.');
//            $ids = implode(',', $param['ids']);
            $ids = $param['ids'];
            $stock = new StockModel();
            $bunled = new BunledModel();
            $product = new ProductModel();
            $user_model = new UserModel();
            $rows = $stock->where(['id' => ['in', $ids]])->field('id,bunled_id,product_id,input_time,out_time,input_user,out_user,status')->select();
            foreach ($rows as $k => $v) {
                $rows[$k]['bunled_id'] = $bunled->where(['id' => $v['bunled_id']])->value('bname');
                $rows[$k]['product_id'] = $product->where(['id' => $v['product_id']])->value('pname');
                $rows[$k]['input_time'] = date('Y/m/d H:i:s', $v['input_time']);
                if (empty($v['out_time'])) {
                    $rows[$k]['out_time'] = '0000-00-00 00:00:00';
                } else {
                    $rows[$k]['out_time'] = date('Y/m/d H:i:s', $v['out_time']);
                }
                $rows[$k]['input_user'] = $user_model->get_user_one_data($v['input_user'], 'real_name');
                if (empty($v['out_user'])){
                    $rows[$k]['out_user'] = '';
                }else{
                    $rows[$k]['out_user'] = $user_model->get_user_one_data($v['out_user'], 'real_name');
                }
                switch ($rows[$k]['status']) {
                    case '-1':
                        $rows[$k]['status'] = '交易失败';
                        break;
                    case '1':
                        $rows[$k]['status'] = '未使用';
                        break;
                    case '2':
                        $rows[$k]['status'] = '使用中';
                        break;
                    case '3':
                        $rows[$k]['status'] = '发布中';
                        break;
                    case '4':
                        $rows[$k]['status'] = '已出库';
                        break;
                    case '5':
                        $rows[$k]['status'] = '出库失败';
                        break;
                }
            }
            $list = [];
            foreach ($rows as $k=>$v){
                $list[$k]['id'] = $v['id'];
                $list[$k]['bunled_id'] = $v['bunled_id'];
                $list[$k]['product_id'] = $v['product_id'];
                $list[$k]['input_time'] = $v['input_time'];
                $list[$k]['out_time'] = $v['out_time'];
                $list[$k]['input_user'] = $v['input_user'];
                $list[$k]['out_user'] = $v['out_user'];
                $list[$k]['status'] = $v['status'];
            }
            $excel = new Offices();
            //设置表头：
            $head = ['ID', '库存名称', '档位名称', '入库时间', '出库时间','入库人','出库人','状态'];
            //数据中对应的字段，用于读取相应数据：
            $keys = ['id', 'bunled_id', 'product_id', 'input_time', 'out_time', 'input_user', 'out_user', 'status'];
            $excel->outdata('table',$list,$head,$keys);
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
                'href' => "javascript:edit_pid(" . $id . ")",
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste',
            ],
            '删除' => [
                'auth' => 'user/userdel',
                'href' => "javascript:stockDel(" . $id . ")",
                'btnStyle' => 'danger',
                'icon' => 'glyphicon glyphicon-trash'
            ]
        ];
    }


}