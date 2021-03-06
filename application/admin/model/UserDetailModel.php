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

class UserDetailModel extends Model
{

     // 确定链接表名
     protected $table = 'snake_user_detail';


     //获取用户某一个值
     public function get_user_one($id,$value){
          return $this->where(['uid'=>$id])->value($value);
     }
}