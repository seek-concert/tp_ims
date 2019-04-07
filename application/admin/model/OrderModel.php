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

class OrderModel extends Model
{
    // 确定链接表名
    protected $table = 'snake_order';

    /**
     * 获取订单列表
     */
    public function getAllLists($page,$limit,$where){
        $lists = $this->where($where)->page($page,$limit)->select();
        return objToArray($lists);
    }


    /**
     * 获取订单总数
     */
    public function getAllCount($where){
        return $this->where($where)->count();
    }

}