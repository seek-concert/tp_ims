<?php
/* +----------------------------------------------------------------------
// | 库存模型
// +----------------------------------------------------------------------*/
namespace app\api\model;

use think\Model;

class StockModel extends Model
{
    protected $table = 'snake_stock';
    protected $field=true;
    protected $type = [

    ];

    //关联档位
    public function ProductModel(){
        return $this->hasOne('ProductModel','id','product_id');
    }


}