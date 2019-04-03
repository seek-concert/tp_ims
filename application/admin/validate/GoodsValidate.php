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
namespace app\admin\validate;

use think\Validate;

class GoodsValidate extends Validate
{
    protected $rule = [
        ['name', 'require', '商品名称不能为空'],
        ['img', 'require', '商品封面图不能空'],
        ['imgs', 'require', '商品轮播图不能空'],
        ['price', 'require', '商品价格不能空'],
        ['stock', 'require', '商品库存不能空'],
        ['content', 'require', '商品介绍内容不能空'],
        ['category_id', 'require', '商品分类不能空']
    ];

}