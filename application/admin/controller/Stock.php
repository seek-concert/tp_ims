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

class Stock extends Base
{
    // 库存列表(总览)
    public function index()
    {
        if(request()->isAjax()){

            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            if (!empty($param['searchText'])) {
                $where['bname'] = ['like', '%' . $param['searchText'] . '%'];
            }

            $user = new StockModel();
            $selectResult = $user->getStockByWhere($where, $offset, $limit);


            $return['total'] = $user->getAllStock($where);  // 总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        return $this->fetch();
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
       
        $user = new StockModel();
        $param = input('param.');
        $page = isset($param['pageNumber'])?(int)$param['pageNumber']:1;
        $limit = isset($param['pageSize'])?(int)$param['pageSize']:10;
        $keywords = isset($param['keywords'])?$param['keywords']:'';
        $sqlmap = [];
        if(!empty($keywords)){
            $sqlmap['pid|panme'] = ['like', '%' . $keywords . '%'];
        }

        $lists = $user->getAllStock($page, $limit, $sqlmap);
        foreach ($lists as $key => $value) {
            $lists[$key]['operate'] = showOperate($this->makeButton($value['id']));
        }
        $return['total'] = $user->getAllStockCount($sqlmap);  // 总数据
        $return['rows'] = $lists;
        return json($return);
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
                'href' => url('allstock/stock_detail', ['id' => $id]),
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