<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/4/3
 * Time: 18:13
 */

namespace app\admin\controller;
use app\admin\model\StockModel;
use app\admin\model\BunledModel;
use app\admin\model\ProductModel;
use app\admin\model\OrderModel;
use app\admin\model\UserDetailModel;
use app\admin\model\UserModel;
use think\Db;
use think\Queue;
class Buysell extends Base
{


    public function __construct()
    {
        parent::__construct();
        $this->order_model = new OrderModel();
        $this->bunled_model = new BunledModel();
        $this->stock_model = new StockModel();
        $this->product_model = new ProductModel();
        $this->user_detail_model = new UserDetailModel();
        $this->user_model = new UserModel();
    }
    public function index(){
        return view();
    }

    public function get_all_list(){
        $param = input('');
        $page = isset($param['pageNumber'])?(int)$param['pageNumber']:1;
        $limit = isset($param['pageSize'])?(int)$param['pageSize']:10;
        $status = isset($param['status'])?(int)$param['status']:0;
        $keywords = isset($param['keywords'])?$param['keywords']:'';
        $sqlmap = [];
        if(!empty($status)){
            $sqlmap['status'] = $status;
        }
        if(!empty($keywords)){
            $sqlmap['product_name|bunled_name'] = ['like','%'.$keywords.'%'];
        }
        $id = session('id');
        $uid = $this->get_user($id);
        $sqlmap['user_id'] = ['in',$uid];
        $lists = $this->order_model->getAllLists($page,$limit,$sqlmap);
        foreach ($lists as $key => $value){
            if($value['status'] == 1){

                $lists[$key]['operate'] = "<a href='javascript:;' onclick='return_order(".$value['id'].")'>撤销订单</a>";

            }elseif($value['status'] == 2){
                $lists[$key]['operate'] = "<a href='javascript:;'>已经完成</a>";
            }else{
                $lists[$key]['operate'] = "<a href='javascript:;'>已经撤回</a>";
            }

            $lists[$key]['only_num'] = $value['num'] - $value['sell_num'];
            $lists[$key]['end_time'] = date('Y-m-d H:i:s',$value['end_time']);
            if($lists[$key]['status'] == 1){
                $lists[$key]['status'] = '发布中';
            }elseif($lists[$key]['status'] == 2){
                $lists[$key]['status'] = '已完成';
            }else{
                $lists[$key]['status'] = '已撤回';
            }
        }
        $count = $this->order_model->getAllCount($sqlmap);
        $return['total'] = $count;  //总数据
        $return['rows'] = $lists;
        return json($return);
    }



    
    /**
     * 分组查询所有产品
     * @param $id
     * @return array
     */
    public function get_bunled()
    {
        $id = session('id');
         //相关用户id
        $uid = $this->get_user($id);
        $stock = new StockModel();
        //查找相关库存--应用名称
        $where['input_user'] = ['in',$uid];
        $where['status'] = ['eq',1];
        $stocks = $this->stock_model ->where($where) ->field('bunled_id') ->group('bunled_id') ->select();
        $bunled = new BunledModel();
        foreach ($stocks as $k=>$v){
            $bunleds = $this->bunled_model->where(['id'=>$v['bunled_id']]) ->find();
            $stocks[$k]['bname'] = $bunleds['bname'];
        }
        return $stocks;
    }

    /**
     * 查询所有档次
     * @param $id
     * @return array
     */
    public function get_product(){
        $id = session('id');
        $bid = input('param.id');
        //相关用户id
        $uid = $this->get_user($id);
        //查找相关库存--档位名称与对应数量
        $where['input_user'] = ['in',$uid];
        $where['bunled_id'] = ['eq',$bid];
        $where['status'] = ['eq',1];
        $stocks = $this->stock_model->where($where)->field('product_id,count(*) as num')->group('product_id')->select();
        foreach ($stocks as $k=>$v){
            $products = $this->product_model->where(['id'=>$v['product_id']])->find();
            $stocks[$k]['pname'] = $products['pname'];
        }
        return $stocks;
    }

    /**
     * 发布操作
     * @param $id
     * @return array
     */
    public function get_sell(){
        $param = input('post.');
        $result = $this->validate($param, 'OrderValidate');
        if (true !== $result) {
            // 验证失败 输出错误信息
            $this->error($result);
        }
        $id = session('id');
        $uid = $this->get_user($id);
        $sql_password = $this->user_detail_model->get_user_one($id,'password');
       

        $stock_count = $this->stock_model->where(['product_id'=>$param['product_id'],'bunled_id'=>$param['bunled_id'],'status'=>1,'input_user'=>['in',$uid]])->count();

        if($stock_count < $param['num']){
            $this->error('发布数量不得大于持有数量');
        }

        if($sql_password != md5($param['password'])){
            $this->error('二级密码错误');
        }
        $sqlmap = [];
        $sqlmap['price'] = $param['price'];
        $sqlmap['product_id'] = $param['product_id'];
        $sqlmap['bunled_id'] = $param['bunled_id'];
        $sqlmap['status'] = 1;
        $sqlmap['end_time'] = time()+(86400*365);
        $sqlmap['note'] = $param['note'];
        $sqlmap['sell_num'] = 0;
        $sqlmap['num'] = $param['num'];
        $sqlmap['product_name'] = $this->product_model->get_product_name($sqlmap['product_id']);
        $sqlmap['bunled_name'] = $this->bunled_model->get_bunled_name($sqlmap['bunled_id']);
        $sqlmap['user_id'] = $id;
        $stock_ids = $this->stock_model->where(['product_id'=>$param['product_id'],'bunled_id'=>$param['bunled_id'],'status'=>1,'input_user'=>['in',$uid]])->order('id asc')->limit($param['num'])->column('id');
        
         //开启事务
         Db::startTrans();
         try {
             $get_order = Db::name('order')->insert($sqlmap);
            
             $edit_status = Db::name('stock')->where(['id'=>['in',$stock_ids]])->update(['status'=>3]);
           
             if ($get_order && $edit_status) {
                 // 提交事务
                 Db::commit();
                return msg(1, '', '商品发布完成');
             } else {
                 // 回滚事务
                 Db::rollback();
                 return msg(0, '', '发布出错,请重试');
             }
         } catch (\Exception $e) {
             // 回滚事务
             Db::rollback();
             return msg(0, '', '发布出错,请重试');
         }
        
    }
    /**
     * 撤销发布
     * @param $id
     * @return array
     */
    public function return_order(){
        $param = input('post.');
        $id = isset($param['id'])?(int)$param['id']:0;
        if(empty($id)){
            $this->error('请勿非法访问');
        }
        $order_info = $this->order_model->where(['id'=>$id])->find();
        $order_info = objToArray($order_info);
        if($order_info['status'] != 1){
            $this->error('该订单已撤回或已交易完成');
        }
        $num = $order_info['num'] - $order_info['sell_num'];
        $user_id = session('id');
        $uid = $this->get_user($user_id);
        $product_id = $order_info['product_id'];
        $bunled_id = $order_info['bunled_id'];
        $stock_ids = $this->stock_model->where(['product_id'=>$product_id,'bunled_id'=>$bunled_id,'status'=>3,'input_user'=>$user_id])->order('id desc')->limit($num)->column('id');
         //开启事务
         Db::startTrans();
         try {
             $edit_order_status = Db::name('order')->where(['id'=>$id])->update(['status'=>3]);
             $edit_stock_status = Db::name('stock')->where(['id'=>['in',$stock_ids]])->update(['status'=>1]);
             if ($edit_order_status && $edit_stock_status) {
                 // 提交事务
                 Db::commit();
                return msg(1, '', '撤回成功');
             } else {
                 // 回滚事务
                 Db::rollback();
                 return msg(0, '', '撤回出错,请重试1');
             }
         } catch (\Exception $e) {
             // 回滚事务
             Db::rollback();
             return msg(0, '', '撤回出错,请重试2');
         }
    }

     /**
     * 交易大厅
     * @param $id
     * @return array
     */

     public function all_sell(){
         return view();
     }
     /**
     * 交易大厅数据
     * @param $id
     * @return array
     */
    public function get_all_sell(){
        $param = input('');
        $page = isset($param['pageNumber'])?(int)$param['pageNumber']:1;
        $limit = isset($param['pageSize'])?(int)$param['pageSize']:10;
        $keywords = isset($param['keywords'])?$param['keywords']:'';
        $status = isset($param['status'])?(int)$param['status']:0;
        $sqlmap = [];
       
        if(!empty($keywords)){
            $sqlmap['product_name|bunled_name'] = ['like','%'.$keywords.'%'];
        }
        if(!empty($status)){
            $sqlmap['status'] = $status;
        }else{
            $sqlmap['status'] = 1;
        }

        $lists = $this->order_model->getAllLists($page,$limit,$sqlmap);
        foreach ($lists as $key => $value) {
            if(session('id') == 1){
                $lists[$key]['user_id'] = $this->user_model->getOneRealName($value['user_id']);
            }
            if($value['status'] == 1){
                $lists[$key]['operate'] = "<a href='javascript:;' onclick='buy_this(".$value['id'].")'>选择要购买的数量</a>";
            }

            $lists[$key]['only_num'] = $value['num'] - $value['sell_num'];
            $lists[$key]['end_time'] = date('Y-m-d H:i:s',$value['end_time']);
            $lists[$key]['all_price'] =($value['num'] - $value['sell_num'])*$value['price'];

            if($lists[$key]['sell_num'] >0 && $lists[$key]['sell_num']<$lists[$key]['num']){
                $lists[$key]['status'] = '已部分完成';
            }else{
                if($value['status'] == 2){
                    $lists[$key]['status'] = '已完成';
                }elseif($value['status'] == 3){
                     $lists[$key]['status'] = '已撤回';
                }else{
                    $lists[$key]['status'] = '订单发布中';
                }
                
            }

        }
        $count = $this->order_model->getAllCount($sqlmap);
        $return['total'] = $count;  //总数据
        $return['rows'] = $lists;
        return json($return);
    }

    public function get_order_info(){
        $param = input('post.');
        $id = isset($param['id'])?(int)$param['id']:0;
        if(empty($id)){
            $this->error('请勿非法访问');
        }
        $order_info = $this->order_model->where(['id'=>$id])->find();
        $order_info = objToArray($order_info);
        $order_info['can_buy_num'] = $order_info['num'] - $order_info['sell_num'];
        $order_info['user_money'] = $this->user_detail_model->where(['uid'=>session('id')])->value('balance');
        return json($order_info);
    }
    
    public function get_buy(){
        $param = input('post.');
        $order_id = isset($param['order_id'])?(int)$param['order_id']:0;
        $buy_num = isset($param['buy_num'])?(int)$param['buy_num']:0;
        $password = isset($param['password'])?$param['password']:'';
        $note = isset($param['note'])?$param['note']:0;
        if(empty($order_id)){
            $this->error('非法访问');
        }
        if(empty($buy_num)){
            $this->error('请输入购买数量');
        }
        if(empty($password)){
            $this->error('请输入二级密码');
        }

       
       $userid = session('id');

        $order_info = $this->order_model->where(['id'=>$order_id])->find();
        if(!$order_info){
            $this->error('请勿非法访问');
        }
        if($order_info['status'] != 1){
            $this->error('请勿非法访问');
        }
        $can_buy_num = $order_info['num'] - $order_info['sell_num'];
        if($can_buy_num < $buy_num){
            $this->error('购买数量不得大于可购数量');
        }
        $user_money = $this->user_detail_model->get_user_one($userid,'balance');
//        if($user_money < $order_info['price']*$buy_num){
//            $this->error('余额不够,请及时充值');
//        }

        $sql_password = $this->user_detail_model->get_user_one($userid,'password');
        if($sql_password != md5($param['password'])){
            $this->error('二级密码错误');
        }
      
        $uid = $this->get_user($order_info['user_id']);
        dump($order_info['product_id']);
        dump($order_info['bunled_id']);
        dump($uid);
        $stock_ids = $this->stock_model->where(['product_id'=>$order_info['product_id'],'bunled_id'=>$order_info['bunled_id'],'status'=>3,'input_user'=>['in',$uid]])->order('id asc')->limit($buy_num)->column('id');
      
        $sqlmap = [];
        $stocksql = [];
        if($can_buy_num == $buy_num){
            $sqlmap['status'] = 2;
        }
        $sqlmap['sell_num'] = $order_info['sell_num']+$buy_num;
        $service_price = ($order_info['price']*$buy_num)/100;
        $real_price = ($order_info['price']*$buy_num)-$service_price;
        $consumer_sql = [];
        $consumer_sql['bunled_id'] = $order_info['bunled_id'];
        $consumer_sql['product_id'] = $order_info['product_id'];
        $consumer_sql['seller_id'] = $order_info['user_id'];
        $consumer_sql['user_id'] = $userid;
        $consumer_sql['num'] = $buy_num;
        $consumer_sql['price'] = $order_info['price']*$buy_num;
        $consumer_sql['real_price'] = $real_price;
        $consumer_sql['service_price'] = $service_price;
        $consumer_sql['remark'] = $note;
        $consumer_sql['input_time'] = time();
        $consumer_sql['status'] = 3;

        //开启事务
        Db::startTrans();
        try {
            $edit_order_status = Db::name('order')->where(['id'=>$order_id])->update($sqlmap);
            $edit_stock_status = Db::name('stock')->where(['id'=>['in',$stock_ids]])->update(['status'=>1,'user'=>$userid,'input_user'=>$userid]);
        
            $insert_consumer = Db::name('consumer_log')->insertGetId($consumer_sql);
            $seller_detail_edit = Db::name('user_detail')->where(['uid'=>$order_info['user_id']])->setInc('funds',$real_price);
            $buyer_detail_edit = Db::name('user_detail')->where(['uid'=>$userid])->setDec('balance',$order_info['price']*$buy_num);
            $admin_detail_edit = Db::name('user_detail')->where(['uid'=>1])->setInc('balance',$service_price);
            if ($edit_order_status && $edit_stock_status && $insert_consumer && $seller_detail_edit && $buyer_detail_edit && $admin_detail_edit) {
                // 提交事务
                Db::rollback();
//                Db::commit();
//                $queue_data = [];
//                $queue_data['id'] = $insert_consumer;
//                //若6小时未进行通过操作由系统自动通过队列
//               $isPushed = Queue::later(6*3600, 'app\admin\job\Hello@fire' ,  $queue_data , 'helloJobQueue' );
               return msg(1, '', '交易成功');
            } else {
                dump($stock_ids);
                dump($edit_order_status);
                dump($edit_stock_status);
                dump($insert_consumer);
                dump($seller_detail_edit);
                dump($buyer_detail_edit);
                dump($admin_detail_edit);
                // 回滚事务
                Db::rollback();
                return msg(0, '', '交易失败1,请重试');
            }
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return msg(0, '', '交易失败2,请重试');
        }

    }

    
    
}