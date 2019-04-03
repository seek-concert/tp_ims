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

        /**
         * 根据搜索条件获取库存列表(总览)信息
         * @param $where
         * @param $offset
         * @param $limit
         */
        public function getStockByWhere($where, $offset, $limit)
        {
    
            return $this->where($where)->limit($offset, $limit)->order('id desc')->select();
        }
        
        /**
         * 根据搜索条件获取所有的库存数量
         * @param $where 
         */
        public function getAllStock($where)
        {
            return $this->where($where)->count();
        }


        // 获取所有的库存信息
        public function getStock()
        {
            return $this->select();
        }
}