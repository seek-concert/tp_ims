<?php
/*========================【库存接口】===========================*/
namespace app\api\controller;
use app\api\model\StockModel;
use app\api\model\UserModel;
use app\admin\model\ProductModel;
use app\admin\model\UserDetailModel;
use app\admin\model\BunledModel;
use think\Controller;
use think\Db;
use think\Request;
use think\Queue;

class Index extends Controller
{
    //协议类型
    private $is_https = true;

    //初始化
    public function __construct()
    {
        parent::__construct();
        $is_https = is_https();
        if(false===$is_https){
            $this->is_https=false;
        }
    }

    //登陆
    public function login()
    {
       //检测协议
        if(false==$this->is_https){
            return msg(-1,'当前接口暂不支持此协议');
        }
        //数据检测
        $rule = [
            ['username', 'require', '请填写账号!'],
            ['password', 'require', '请填写密码!']
        ];
        $result = $this->validate(input(''), $rule);
        if (true !== $result) {
            return msg(1, $result);
        }
        //账号密码过滤
        $user = stripTags(input('username/s'));
        $pwd = stripTags(input('password/s'));
        //数据检测
        $user_info = UserModel::field(['id','user_name','password','status','token'])->where(['user_name'=>$user])->find();
        if(!$user_info){
            return msg(1, '该账号不存在');
        }
        if($user_info['status']==2){
            return msg(1, '该账号已停用');
        }
        if(md5($pwd)!==$user_info['password']){
            return msg(1,'密码错误，请重新输入');
        }
        //生成token
        $token = create_guid();
        try{
            $rs = model('UserModel')->save(['token'=>$token],['user_name'=>$user]);
            if(!$rs){
                return msg(1,'网络异常，token生成失败');
            }
        }catch (\Exception $e){
            return msg(1,'网络异常，token生成失败!');
        }

        return msg(0, '登陆成功', $token);
    }


    //入库
    public function inbound(){
        if(false==$this->is_https){
            //return msg(-1,'当前接口暂不支持此协议');
        }
        //数据检测
        $rule = [
            ['token', 'require', '请输入token令牌!'],
            ['bid', 'require', '请填写应用ID!'],
            ['pname', 'require', '请填写产品名!'],
            ['pid', 'require', '请填写产品ID!'],
            ['tid', 'require', '请填写交易ID!'],
            ['tprice', 'require', '请填写交易价格!'],
            ['tcurrency', 'require', '请填写币种!'],
            ['tdate', 'require', '请填写date!'],
            ['receipt', 'require', '请填写交易收据!']
        ];
        $result = $this->validate(input(''), $rule);
        if (true !== $result) {
            return msg(1, $result);
        }
        //token检测
        $token = stripTags(input('token/s'));
        $token = UserModel::field(['id','status','pid','token','power'])->where(['token'=>$token])->find();
        if(!$token){
            return msg(1,'token令牌不存在');
        }
        //是否拥有入库权限
        if($token['status']==2){
            return msg(1, '该账号已停用');
        }
        if($token['pid']!=0){
            if($token['power']==2){
                return msg(1,'暂无入库权限');
            }
        }
        //检测权限
        $contrl_name = strtolower(Request()->controller());
        $action_name = strtolower(Request()->action());
        $result = check_node($token['token'],$contrl_name,$action_name);
        if(false==$result){
            return msg(-1,'暂无权限');
        }
        //数据过滤
        $data = [];
        //应用
        $bid = stripTags(input('bid/s'));
        $bname = input('bname')?stripTags(input('bname/s')):'';
        //产品档位
        $pname = stripTags(input('pname/s'));
        $pid = stripTags(input('pid/s'));

        $data['account'] = input('account')?stripTags(input('account/s')):'';
        $data['tid'] = stripTags(input('tid/s'));
        $data['price'] = stripTags(input('tprice/s'));
        $data['tcurrency'] = stripTags(input('tcurrency/s'));
        $data['tdate'] = strtotime(stripTags(input('tdate/s')));
        $data['receipt'] = stripTags(input('receipt/s'));
        $data['input_user'] = $token['id'];
        $data['input_time'] = time();
        $data['status'] = 1;
        if($token['pid']){
            $data['pristine_user'] = $token['pid'];
        }else{
            $data['pristine_user'] = $token['id'];
        }
        
        Db::startTrans();
        try{
            //获取应用ID
            $bunled_info = BunledModel::where(['bid'=>$bid])->find();
            if(!$bunled_info){
                if(!$bname){
                    throw new \Exception('请传入应用名称',404404);
                }
                $insert_bunled =  Db::name('bunled')->insert(['bid'=>$bid,'bname'=>$bname]);
                if(!$insert_bunled){
                    throw new \Exception('暂无应用数据',404404);
                }
            }
            $data['bunled_id'] = isset($bunled_info['id'])?$bunled_info['id']:Db::name('bunled')->getLastInsID();
            //获取产品ID（档位ID）
            $product_info = ProductModel::where(['pid'=>$pid])->find();
            if(!$product_info){
                $insert_product =  Db::name('product')->insert(['pid'=>$pid,'pname'=>$pname]);
                if(!$insert_product){
                    throw new \Exception('暂无档位数据',404404);
                }
            }
            $data['product_id'] = isset($product_info['id'])?$product_info['id']:Db::name('product')->getLastInsID();
             //取得面值与入库价格
             $data['tprice'] = bcmul($data['price'],6.7,2);
            //入库
            $rs = model('StockModel')->save($data);
            if(!$rs){
                return msg(1,'入库失败');
            }
            $errno = 0;
            $txt = '入库成功';
            Db::commit();
        }catch (\Exception $e){
            $errno = 1;
            $txt = $e->getCode()==404404?$e->getMessage():'网络异常，请稍后再试！';
            Db::rollback();
        }
        return msg($errno,$txt);
    }


    //出库
    public function outbound(){
        if(false==$this->is_https){
            //return msg(-1,'当前接口暂不支持此协议');
        }
        // //数据检测
        $rule = [
            ['token', 'require', '请输入token令牌!'],
            ['bid', 'require', '请填写应用ID!'],
            ['pid', 'require', '请填写产品ID!']
        ];
        $result = $this->validate(input(''), $rule);
        if (true !== $result) {
            return msg(1, $result);
        }
        //token检测
        $token = stripTags(input('token/s'));
        //$token = 'D32994C6-D4F0-8411-96DE-6D0BEC149C6F';
        //获取用户信息
        $token = UserModel::field(['id','status','pid','token','power'])->where(['token'=>$token])->find();
        if(!$token){
            return msg(0,'token令牌不存在');
        }
      
        //是否拥有出库权限
        if($token['status']==2){
            return msg(0, '该账号已停用');
        }
        if($token['pid']!=0){
            if($token['power']==1){
                return msg(0,'暂无出库权限');
            }
        }
        //检测权限
        $contrl_name = strtolower(Request()->controller());
        $action_name = strtolower(Request()->action());
        $result = check_node($token['token'],$contrl_name,$action_name);

        if(false==$result){
            return msg(-1,'暂无权限');
        }
        //数据过滤
        $where = [];
        $where['bid'] = stripTags(input('bid/s'));
        $where['pid'] = stripTags(input('pid/s'));
        // $where['bid'] = 1;
        // $where['pid'] = 'com.test.diamond101';
        //出库
        $uid = $this->get_user($token['id']);
        //通过pid获取priduct_id
        $product_id = ProductModel::where(['pid'=>$where['pid']])->value('id');
        //通过bid获取bunled_id
        $bunled_id = BunledModel::where(['bid'=>$where['bid']])->value('id');
        //stock详情
        $stock_info = StockModel::where(['product_id'=>$product_id,'bunled_id'=>$bunled_id,'status'=>1,'user'=>$token['id']])->find();


        if(!$stock_info){
            return msg(10001,'暂无库存可出库');
        }
        //格式化stock详情
        $stock_info = objToArray($stock_info);
        //获取用户余额
        $user_money = UserDetailModel::where(['uid'=>$token['pid']])->value('balance');
        // if($stock_info['price']/100 >$user_money){
        //    return msg(10002,'余额不足');
        // }
        //手续费
        //$service_price = $stock_info['price']/100;
        //组装出库数据库数据    
        $out_sql = [];
        $out_sql['product_id'] = $product_id;
        $out_sql['bunled_id'] = $bunled_id;
        $out_sql['user_id'] = $token['id'];
        $out_sql['input_time'] = time();
        $out_sql['status'] = 1;
        $out_sql['receipt'] = md5($where['bid'].'-'.$where['pid'].'-'.time());
        //开启事务
        Db::startTrans();
        try {
            //出库记录写入
            $out_insert = Db::name('out_stock')->insertGetId($out_sql);
            //数据状态更改为使用中
            $edit_stock_status = Db::name('stock')->where(['id'=>$stock_info['id']])->update(['status'=>2]);
            if ($out_insert && $edit_stock_status) {
                // 提交事务
                Db::commit();
                $errno = 0;
                $txt = '出库成功' ;
                $tid = $stock_info['tid'];
                $id = $out_insert;
                $receipt = $stock_info['receipt'];
                return json(compact('errno', 'txt','tid','id','receipt'));
            } else {
                // 回滚事务
                Db::rollback();
                return msg(10003, '', '出库失败,请重试');
            }
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return msg(10004, '', '出库失败,请重试');
        }
       
      
       
    }

    //报告出库结果
    public function report(){
        if(false==$this->is_https){
           // return msg(-1,'当前接口暂不支持此协议');
        }
        //数据检测
        $rule = [
            ['token', 'require', '请输入token令牌!'],
            ['tid', 'require', '请填写交易ID!'],
            ['status', 'require', '请填写状态!']
        ];
        $result = $this->validate(input(''), $rule);
        if (true !== $result) {
            return msg(1, $result);
        }
        //token检测
        $token = stripTags(input('token/s'));
        //$token = 'D32994C6-D4F0-8411-96DE-6D0BEC149C6F';
        $token = UserModel::field(['id','status','pid','token','power'])->where(['token'=>$token])->find();
        if(!$token){
            return msg(1,'token令牌不存在');
        }
        //是否拥有出库权限
        if($token['status']==2){
            return msg(1, '该账号已停用');
        }
        if($token['pid']!=0){
            if($token['power']==1){
                return msg(1,'暂无出库权限');
            }
        }
        //检测权限
        $contrl_name = strtolower(Request()->controller());
        $action_name = strtolower(Request()->action());
        $result = check_node($token['token'],$contrl_name,$action_name);
        if(false==$result){
            return msg(-1,'暂无权限');
        }
        //数据过滤
        $where = [];
        $where['tid'] = stripTags(input('tid/s'));
        $status = stripTags(input('status/s'));
        //$where['tid'] = 13;
        //$status = 2;
        $data = [];
        switch ($status){
            case 1:
                $data['status'] = 2;
            break;
            case 2:
                $data['status'] = 4;
                break;
            case 3:
                $data['status'] = 5;
                break;

                default:
                    $data['status'] = 6;
                ;
        }

        //检测状态
        $out_info =  StockModel::field(['id','tid','tprice','price','product_id','receipt','status'])->where($where)->find();
        $service_price = $out_info['tprice']/100;
        $service_sql = [];
        $service_sql['price'] = $service_price;
        $service_sql['input_time'] = time();
        $service_sql['type'] = 2;
        $service_sql['product_id'] = $out_info['product_id'];
        $service_sql['user_id'] = $token['id'];
        if($status == 2){
            //开启事务
            Db::startTrans();
            try {
                //写入服务费记录表
                //$service_insert = Db::name('service_money')->insert($service_sql);
                //扣除用户服务费
                //$out_detail_edit = Db::name('user_detail')->where(['uid'=>$token['id']])->setDec('balance',$service_price);
                //增加管理员收取的费用就是用户扣除的服务费
                //$buyer_detail_edit = Db::name('user_detail')->where(['uid'=>1])->setInc('balance',$service_price);
                //根据回执数据更改数据状态
                $edit_stock_status = Db::name('stock')->where(['id'=>$out_info['id']])->update(['out_user'=>$token['id'],'out_time'=>time(),'status'=>$data['status']]);

                if ($edit_stock_status) {
                    // 提交事务
                    Db::commit();
                    return msg(0, '', '成功');
                } else {
                    // 回滚事务
                    Db::rollback();
                    return msg(10003, '', '出错');
                }
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                return msg(10004, '', '出错');
            }
        }else{
            Db::name('stock')->where(['id'=>$out_info['id']])->update(['status'=>$data['status']]);
            return msg(0, '', '成功');
        }
     
    }



     /*
     * 查询相关用户id
     */
    public function get_user($id)
    {
        $user = new UserModel();
        //根据当前用户id查找有无上级
        $pid = $user->where(['id'=>$id])->value('pid');
        //$pid等于0时表示无上级id 查询所有下级 pid = $id
        if($pid == 0){
            $uid = $user->where(['pid'=>$id,'power'=>1])->column('id');
            //最后加上自身id
            $uid['id'] = $id;
        }else{
            //$pid不等于0时表示有上级id 查询到所有同级 pid = $pid
            $uid = $user->where(['pid'=>$pid,'power'=>1])->column('id');
            //最后加上上级id
            $uid['id'] = $pid;
        }
        //分割成字符串
        $uid = implode(',', $uid);
        return $uid;
    }

}
