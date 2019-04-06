<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/6
 * Time: 11:41
 */

namespace app\admin\model;


use think\Model;

class PrepaidLogModel extends Model
{
    // 确定链接表名
    protected $table = 'snake_prepaid_log';

    /**
     * 插入充值记录
     * @param $param
     */
    public function insertLog($param)
    {
        try{
            $result =  $this->validate('PrepaidValidate')->save($param);
            if(false === $result){
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            }else{
                return msg(1, url('personal/prepaid'), '提交成功，等待审核！');
            }
        }catch(\Exception $e){

            return msg(-2, '', $e->getMessage());
        }
    }

    /**
     * 根据搜索条件获取列表信息
     * @param $where
     * @param $offset
     * @param $limit
     */
    public function getPrepaidLogByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('id desc')->select();
    }

    /**
     * 根据搜索条件获取所有的数量
     * @param $where
     */
    public function getAllPrepaidLog($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 统计充值
     * @param $id
     */
    public function getPrepaidLogMoney($id)
    {
        return $this->where(['user_id'=>$id,'status'=>2])->count('money');
    }

    /**
     * 根据id获取信息
     * @param $id
     */
    public function getOnePrepaidLog($id)
    {
        return $this->where('id', $id)->find();
    }

}