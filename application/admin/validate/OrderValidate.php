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

class OrderValidate extends Validate
{
    protected $rule = [
        ['bunled_id', 'require|integer', '请选择产品|请选择产品'],
        ['product_id', 'require|integer', '请选择档位|请选择档位'],
        ['num', 'require|integer', '请输入数量|请输入整数'],
        ['price', 'require', '请输入价格'],
        ['password', 'require', '请输入二级密码'],
    ];

}