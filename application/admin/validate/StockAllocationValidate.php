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

class StockAllocationValidate extends Validate
{
    protected $rule = [
        ['bid', 'require', '应用不能为空'],
        ['pid', 'require', '档位不能为空'],
        ['num', 'require', '数量不能为空']
    ];

}