<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$
return [

    // 模板参数替换
    'view_replace_str' => array(
        '__CSS__' => '/static/admin/css',
        '__JS__' => '/static/admin/js',
        '__IMG__' => '/static/admin/images',
    ),

    // 管理员状态
    'user_status' => [
        '1' => '正常',
        '2' => '禁用'
    ],
    // 角色状态
    'role_status' => [
        '1' => '启用',
        '2' => '禁用'
    ],
    // 子用户权限
    'user_power' => [
        '1' => '入库',
        '2' => '出库'
    ],
    // 充值记录状态
    'prepaid_status' => [
        '1' => '验证中',
        '2' => '充值成功',
        '3' => '订单过期'
    ],
    // 消费记录状态
    'consumer_status' => [
        '1' => '成功',
        '2' => '失败',
        '3' => '待确定'
    ],
    // 提现记录状态
    'extract_status' => [
        '1' => '验证中',
        '2' => '提现成功',
        '3' => '提现失败'
    ],

    // 多库测试
    'local' => [
        // 数据库类型
        'type' => 'mysql',
        // 服务器地址
        'hostname' => '127.0.0.1',
        // 数据库名
        'database' => 'test',
        // 数据库用户名
        'username' => 'root',
        // 数据库密码
        'password' => 'root',
        // 数据库编码默认采用utf8
        'charset' => 'utf8',
        // 数据库表前缀
        'prefix' => '',
    ],
];
