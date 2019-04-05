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




    // 获取所有的库存信息
    public function getStock()
    {
        return $this->select();
    }

    /**
     * 根据搜索条件获取库存列表(总览)信息
     * @param $page  当前页码
     * @param $limit 每页个数
     * @param $where  查询条件
     */
    public function getAllStock($page,$limit,$where){
        $obj_lists  = $this->where($where)->page($page,$limit)->select();    
        return objToArray($obj_lists);
    }

    /**
     * 根据搜索条件获取所有的库存数量
     * @param $where 
     */
     public function getAllStockCount($where=[]){
        $obj_lists  = $this->where($where)->count();
        return $obj_lists;
     }

    /**
     * 撤销分配
     * @param $id
     */
    public function returnGive($id)
    {
        try{

            $this->where('id', $id)->setField('user',0);
            return msg(1, '', '撤销分配成功');

        }catch(\Exception $e){
            return msg(-1, '', $e->getMessage());
        }
    }

/**
     * 获取某一条库存数据信息
     * @param $where 
     */
    public function getOneStock($where = []){
        $obj_info = $this->where($where)->find();
        return objToArray($obj_info);
    }
}