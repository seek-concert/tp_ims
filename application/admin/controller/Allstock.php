<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/3
 * Time: 18:13
 */

namespace app\admin\controller;
use app\admin\model\StockModel;


class Allstock extends Base
{
    public function index(){
        return view();
    }

    public function getallstock(){
        $stock_model = new StockModel();
        $param = input('');
        $input_time_start = isset($param['input_time_start'])?strtotime($param['input_time_start']):'';
        $input_time_end = isset($param['input_time_end'])?strtotime($param['input_time_end']):'';
        $out_time_start = isset($param['out_time_start'])?strtotime($param['out_time_start']):'';
        $out_time_end = isset($param['out_time_end'])?strtotime($param['out_time_end']):'';
        $sqlmap = [];
        //查询某个入库时间之后
        if($input_time_start != ''&& $input_time_end == ''){
            $sqlmap['input_time'] = ['gt',$input_time_start];
        }
        //查询某个入库时间之前
        if($input_time_start == ''&& $input_time_end != ''){
            $sqlmap['input_time'] = ['lt',$input_time_end];
        }
        //查询某个入库时间段
        if($input_time_start != ''&& $input_time_end != ''){
            $sqlmap['input_time'] = ['between',[$input_time_start,$input_time_end]];
        }
        //查询某个出库时间之后
        if($out_time_start != ''&& $out_time_end == ''){
            $sqlmap['out_time'] = ['gt',$out_time_start];
        }
        //查询某个出库时间之前
        if($out_time_start == ''&& $out_time_end != ''){
            $sqlmap['out_time'] = ['lt',$out_time_end];
        }
        //查询某个出库时间段
        if($out_time_start != ''&& $out_time_end != ''){
            $sqlmap['out_time'] = ['between',[$out_time_start,$out_time_end]];
        }
        
        $lists = $stock_model->getAllStock($param['pageNumber'],$param['pageSize'],$sqlmap); 
        foreach ($lists as $key => $value) {
            $lists[$key]['is_check'] = '<div class="switch" data-animated="false" data-on-label="SI" data-off-label="NO"> <input type="checkbox" checked /> </div>';
        }
        $return['total'] = $stock_model->getAllStockCount();  //总数据
        $return['rows'] = $lists;

        return json($return);
       
    }
}