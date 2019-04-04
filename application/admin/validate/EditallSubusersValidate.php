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

class EditallSubusersValidate extends Validate
{
    protected $rule = [
        ['used', 'require', '原密码不能为空'],
        ['password', 'require|confirm', '新密码不能为空|新密码与确认密码不一致']
    ];

}