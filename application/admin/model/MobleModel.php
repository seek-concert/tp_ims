<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/6
 * Time: 11:41
 */

namespace app\admin\model;


use think\Model;

class MobleModel extends Model
{
    // 确定链接表名
    protected $table = 'snake_moble';

    /**
     * 根据搜索条件获取列表信息
     * @param $where
     * @param $offset
     * @param $limit
     */
    public function getMobleByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('id desc')->select();
    }

    /**
     * 根据搜索条件获取所有的数量
     * @param $where
     */
    public function getAllMoble($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 插入信息
     * @param $param
     */
    public function insertMoble($param)
    {
        try{
            $result =  $this->saveAll($param);
            if($result){
                return msg(1, url('moble/index'), '添加成功');
            }else{
                return msg(-1, '', '添加失败');
            }
        }catch(\Exception $e){

            return msg(-2, '', $e->getMessage());
        }
    }

    /**
     * 根据id获取信息
     * @param $id
     */
    public function getOneMoble($id)
    {
        return $this->where('id', $id)->find();
    }

    /**
     * 删除
     * @param $id
     */
    public function delMoble($id)
    {
        try{
            $this->where('id', $id)->delete();
            return msg(1, '', '删除成功');

        }catch(\Exception $e){
            return msg(-1, '', $e->getMessage());
        }
    }
}