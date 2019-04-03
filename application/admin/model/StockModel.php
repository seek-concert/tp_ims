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
namespace app\admin\model;

use think\Model;

class StockModel extends Model
{

     // 确定链接表名
     protected $table = 'snake_stock';
     

     public function getAllStock($page,$limit,$data){
        $obj_lists  = $this->where($data)->page($page,$limit)->select();       
        return objToArray($obj_lists);
     }


     public function getAllStockCount(){
        $obj_lists  = $this->count();
        return $obj_lists;
     }
}