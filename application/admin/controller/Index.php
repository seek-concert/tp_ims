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
namespace app\admin\controller;
use app\admin\model\NodeModel;
use app\admin\model\RoleModel;
use think\Session;
class Index extends Base
{

    public function index()
    {
        // 获取权限菜单
        $node = new NodeModel();
        $this->assign([
            'menu' => $node->getMenu(session('rule'))
        ]);

        return $this->fetch('/index');
    }

    /**
     * 后台默认首页
     * @return mixed
     */
    public function indexPage()
    {
        $role_model = new RoleModel();
        $admin_id = session('id');
        $admin_info = model('admin/UserModel')->getAdminDetail($admin_id);
        $admin_info['role_name'] = $role_model->getOneRole($admin_info['role_id'])['role_name'];
        $return_data = [];
        $return_data['admin_info'] = $admin_info;
        return view('index',$return_data);
    }
}
