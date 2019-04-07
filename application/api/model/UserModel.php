<?php
/* +----------------------------------------------------------------------
// | 用户模型
// +----------------------------------------------------------------------*/
namespace app\api\model;

use think\Model;

class UserModel extends Model
{
    protected $table = 'snake_user';
    protected $field=true;
    protected $type = [

    ];
    //关联角色
    public function RoleModel(){
        return $this->hasOne('RoleModel','id','role_id');
    }


}