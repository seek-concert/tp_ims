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
namespace app\admin\model;

use think\Model;

class LoginLogModel extends Model
{
    // 确定链接表名
    protected $table = 'snake_login_log';

    /**
     * 根据搜索条件获取列表信息
     * @param $where
     * @param $offset
     * @param $limit
     */
    public function getLoginLogByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('id desc')->select();
    }

    /**
     * 根据搜索条件获取所有的数量
     * @param $where
     */
    public function getAllLoginLog($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 插入信息
     * @param $param
     */
    public function insertLoginLog($param)
    {
        try{
            $this->save($param);
            return msg(1, '', '写入成功');
        }catch(\Exception $e){
            return msg(-2, '', $e->getMessage());
        }
    }

}