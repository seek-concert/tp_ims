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

class PrepaidValidate extends Validate
{
    protected $rule = [
        ['money', 'require|integer|confirm', '充值金额不能为空|请输入整数|充值金额与确认金额不一致'],
        ['btc', 'require', '付款地址不能为空']
    ];

}