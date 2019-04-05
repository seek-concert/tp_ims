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

class ProductModel extends Model
{
    // 确定链接表名
    protected $table = 'snake_product';


    public function get_product_name($id = 0){
        if($Id == 0){
            return false;
        }
        return $this->where(['id'=>$id])->value('bname');
        
    }
}