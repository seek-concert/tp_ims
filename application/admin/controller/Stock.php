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
        return "this is pidRename";
    }

    
}