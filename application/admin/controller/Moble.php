<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/7
 * Time: 14:38
 */

namespace app\admin\controller;


use app\admin\model\MobleModel;
use app\admin\model\UserModel;

class Moble extends Base
{
    /*
     * 14码列表
     */
    public function index()
    {
        if (request()->isAjax()) {
            $param = input('param.');
            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;
            $where = [];
            if (!empty($param['user_id'])) {
                $where['user_id'] = ['eq', $param['user_id']];
            }
            $moble = new MobleModel();
            $user = new UserModel();
            $selectResult = $moble->getMobleByWhere($where, $offset, $limit);
            // 拼装参数
            foreach ($selectResult as $key => $vo) {
                $selectResult[$key]['user_id'] = $user->getOneRealName($vo['user_id']);
                $selectResult[$key]['input_time'] = date('Y-m-d H:i:s',$vo['input_time']);
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id']));
            }
            $return['total'] = $moble->getAllMoble($where);  //总数据
            $return['rows'] = $selectResult;
            return json($return);
        }
        $id = session('id');
        $this->assign('id', $id);
        return $this->fetch();
    }

    /*
     * 新增14码
     */
    public function mobleadd()
    {
        if(request()->isPost()){
            $code = input('param.code');
            $code = explode("\n",$code);
            //过滤多余空换行
            foreach ($code as $k=>$v){
                if(!empty($v)){
                    $codes[] = $v;
                }
            }
            if(empty($codes)){
                return msg(-1,'','14码填写有误');
            }
            //拆分字符串为数组
            foreach ($codes as $k=>$v){
                $codes[$k] = explode("----",$v);
                if(count($codes[$k]) != 14){
                    return msg(-1,'','14码填写有误');
                }
            }
            //定义字段名
            $name = ['sn','imei','meid','wifi','bluetootn','ecid','udid','mlbsn','product_type','model_code','model_str','hardware_platform','product_version','build_version'];
            //合并数组
            foreach ($codes as $k=>$v){
                $row[]=array_combine($name,$v);
            }
            //拆分model_code为model_number region_code
            foreach ($row as $k=>$v){
                $row[$k]['input_time'] = time();
                $row[$k]['name'] = session('username');
                $row[$k]['user_id'] = session('id');
                $row[$k]['model_number'] = substr($v['model_code'],0,5);
                $row[$k]['region_code'] = substr($v['model_code'],5,2);
                unset($row[$k]['model_code']);
            }
            $moble = new MobleModel();
            $flag = $moble->insertMoble($row);
            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }
        return $this->fetch();
    }

    /*
     * 14码查看
     */
    public function mobledetail()
    {
        $id = input('param.id');
        $moble = new MobleModel();
        $user = new UserModel();
        $row = $moble->getOneMoble($id);
        $row['user_id'] = $user->getOneRealName($row['user_id']);
        $row['input_time'] = date('Y-m-d H:i:s',$row['input_time']);
        $row['status'] = $row['status']==1?'启用':'禁用';
        $this->assign([
            'row' => $row,
        ]);
        return $this->fetch();
    }

    /*
     * 14码删除
     */
    public function mobledel()
    {
        $id = input('param.id');

        $moble = new MobleModel();
        $flag = $moble->delMoble($id);
        return json(msg($flag['code'], $flag['data'], $flag['msg']));
    }

    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButton($id)
    {
        return [
            '查看' => [
                'auth' => 'moble/mobledetail',
                'href' => url('moble/mobledetail', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ],
            '删除' => [
                'auth' => 'moble/mobledel',
                'href' => "javascript:mobledel(" .$id .")",
                'btnStyle' => 'danger',
                'icon' => 'fa fa-trash-o'
            ]
        ];
    }

}