<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/6
 * Time: 11:41
 */

namespace app\admin\model;


use think\Model;

class ConsumerLogModel extends Model
{
    // 确定链接表名
    protected $table = 'snake_consumer_log';

    /**
     * 根据搜索条件获取列表信息
     * @param $where
     * @param $offset
     * @param $limit
     */
    public function getConsumerLogByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('id desc')->select();
    }

    /**
     * 根据搜索条件获取所有的数量
     * @param $where
     */
    public function getAllConsumerLog($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 统计充值
     * @param array $where
     */
    public function getConsumerLogMoney($where)
    {
        return $this->where($where)->sum('price');
    }
}