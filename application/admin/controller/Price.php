<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/10
 * Time: 17:00
 */

namespace app\admin\controller;


use app\admin\model\PriceModel;
use app\common\service\Office;

class Price extends Base
{
    /*
     * 面值管理--列表页
     */
    public function index()
    {
        if(request()->isAjax()){

            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            if (!empty($param['bid'])) {
                $where['bid'] = ['eq',$param['bid']];
            }
            $price_model = new PriceModel();
            $selectResult = $price_model->getPriceByWhere($where, $offset, $limit);

            // 拼装参数
            foreach($selectResult as $key=>$vo){
                $selectResult[$key]['operate'] = showOperate($this->makeButton($vo['id']));
            }

            $return['total'] = $price_model->getAllPrice($where);  //总数据
            $return['rows'] = $selectResult;

            return json($return);
        }
        return $this->fetch();
    }

    /*
     * 批量导入
     */
    public function importexecl()
    {
        if (request()->isAjax()) {
            $file = request()->file('file');
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->move(ROOT_PATH . 'public' . DS . 'upload');
            if ($info) {
                $src = 'upload' . '/' . date('Ymd') . '/' . $info->getFilename();
                $excel = new Office();
                //读取EXCEL表数据
                $row = $excel->importExecl($src);
                //清楚第一行
                unset($row[1]);
                //组装为自己的数据
                foreach ($row as $k=>$v){
                    $data[$k]['bid'] = $v['A'];
                    $data[$k]['pid'] = $v['B'];
                    $data[$k]['price'] = $v['C'];
                }
                $price_model = new PriceModel();
                foreach ($data as $k=>$v){
                    $price_find = $price_model->getIsPrice($v['bid'],$v['pid']);
                    //数据是否存在--存在则销毁该键组
                    if(!empty($price_find)){
                        unset($data[$k]);
                    }
                }
                //数组重建
                $data = array_values($data);
                $flag = $price_model->getInsertAll($data);
                if($flag){
                    return json(msg(1, '', '导入成功'));
                }else{
                    return json(msg(-1, '', '导入失败'));
                }
            } else {
                // 上传失败获取错误信息
                return json(msg(-1, '', $file->getError()));
            }
        }
    }

    /*
     * 编辑
     */
    public function priceedit()
    {
        $price_model = new PriceModel();

        if(request()->isPost()){

            $param = input('post.');

            $flag = $price_model->editPrice($param);

            return json(msg($flag['code'], $flag['data'], $flag['msg']));
        }

        $id = input('param.id');

        $this->assign([
            'price' => $price_model->getOnePrice($id)
        ]);
        return $this->fetch();
    }

    /**
     * 拼装操作按钮
     * @param $id
     * @return array
     */
    private function makeButton($id)
    {
        return [
            '编辑' => [
                'auth' => 'price/priceedit',
                'href' => url('price/priceedit', ['id' => $id]),
                'btnStyle' => 'primary',
                'icon' => 'fa fa-paste'
            ]
        ];
    }
}