<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/11
 * Time: 10:11
 */

namespace app\admin\model;

use think\Model;

class PriceModel extends Model
{
    // 确定链接表名
    protected $table = 'snake_price';


    /**
     * 根据搜索条件获取列表信息
     * @param $where
     * @param $offset
     * @param $limit
     */
    public function getPriceByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('id desc')->select();
    }

    /**
     * 根据搜索条件获取所有的数量
     * @param $where
     */
    public function getAllPrice($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 根据条件获取信息
     * @param $bid
     * @param $pid
     */
    public function getIsPrice($bid, $pid)
    {
        return $this->where(['bid'=>$bid,'pid'=>$pid])->find();
    }

    /**
     * 根据条件获取信息
     * @param array $data
     */
    public function getInsertAll($data=[])
    {
        return $this->saveAll($data);
    }

    /**
     * 编辑信息
     * @param $param
     */
    public function editPrice($param)
    {
        try{

            $result =  $this->save($param, ['id' => $param['id']]);

            if(false === $result){
                // 验证失败 输出错误信息
                return msg(-1, '', $this->getError());
            }else{

                return msg(1, url('price/index'), '编辑成功');
            }
        }catch(\Exception $e){
            return msg(-2, '', $e->getMessage());
        }
    }

    /**
     * 根据id获取信息
     * @param $id
     */
    public function getOnePrice($id)
    {
        return $this->where('id', $id)->find();
    }

    /**
     * 根据条件获取某个单条信息
     * @param $id
     */
    public function get_one_data($where=[],$value=''){
        return $this->where($where)->value($value);
    }
}