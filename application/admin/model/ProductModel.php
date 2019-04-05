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
        
        if($id == 0){
            return false;
        }
       
        return $this->where(['id'=>$id])->value('pname');
        
    }
    public function get_product_id($id = 0){
        if($id == 0){
            return false;
        }
        return $this->where(['id'=>$id])->value('pid');
        
    }

    public function update_data($where=[],$data=[]){
       return  $this->where($where)->update($data);
    }

    public function getAllProduct($page,$limit,$where){
        $obj_lists  = $this->where($where)->page($page,$limit)->select();    
        return objToArray($obj_lists);
    }

    public function getAllProductCount($where=[]){
        $obj_lists  = $this->where($where)->count();
        return $obj_lists;
    }

    public function getProductInfo($where){
        $obj_info = $this->where($where)->find();
        return objToArray($obj_info);
    }

    public function del($where = []){
        return $this->where($where)->delete();
    }
}