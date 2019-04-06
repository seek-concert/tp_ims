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

class ExtractValidate extends Validate
{
    protected $rule = [
        ['money', 'require', '提现金额不能为空'],
        ['btc', 'require', 'BTC地址不能为空'],
        ['password', 'require', '二级密码不能为空']
    ];

}