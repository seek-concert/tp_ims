<?php
namespace app\api\controller;
use app\api\model\StockModel;
use app\api\model\UserModel;
use think\Controller;

class Index extends Controller
{
    //协议类型
    private $is_https = true;

    //初始化
    public function __construct()
    {
        parent::__construct();
//        $is_https = is_https();
//        if(false===$is_https){
//            $this->is_https=false;
//        }
    }
    /*========================【库存接口】===========================*/
    //登陆
    public function login()
    {
        if(false==$this->is_https){
            return msg(-1,'当前接口暂不支持此协议');
        }
        //数据检测
        $rule = [
            ['username', 'require', '请填写账号!'],
            ['password', 'require', '请填写密码!']
        ];
        $result = $this->validate(input('post.'), $rule);
        if (true !== $result) {
            return msg(1, $result);
        }
        //账号密码过滤
        $user = stripTags(input('post.username/s'));
        $pwd = stripTags(input('post.password/s'));
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
            return msg(-1,'当前接口暂不支持此协议');
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
        $result = $this->validate(input('post.'), $rule);
        if (true !== $result) {
            return msg(1, $result);
        }
        //token检测
        $token = stripTags(input('post.token/s'));
//        $token = 'D32994C6-D4F0-8411-96DE-6D0BEC149C3F';
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
        //数据过滤
        $data = [];
        $data['bid'] = stripTags(input('post.bid/s'));
        $data['pname'] = stripTags(input('post.pname/s'));
        $data['pid'] = stripTags(input('post.pid/s'));
        $data['tid'] = stripTags(input('post.tid/s'));
        $data['tprice'] = stripTags(input('post.tprice/s'));
        $data['tcurrency'] = stripTags(input('post.tcurrency/s'));
        $data['tdate'] = stripTags(input('post.tdate/s'));
        $data['receipt'] = stripTags(input('post.receipt/s'));
        $data['input_time'] = time();

//        $data['bid'] = 1;
//        $data['pname'] = 'test';
//        $data['pid'] = 2;
//        $data['tid'] = 3;
//        $data['tprice'] = '12.50';
//        $data['tcurrency'] = '111';
//        $data['tdate'] = time();
//        $data['receipt'] = '2350';
        //入库
        $rs = model('StockModel')->save($data);
        if(!$rs){
            return msg(1,'入库失败');
        }
        return msg(0,'入库成功');
    }

    //出库
    public function outbound(){
        if(false==$this->is_https){
            return msg(-1,'当前接口暂不支持此协议');
        }
        //数据检测
        $rule = [
            ['token', 'require', '请输入token令牌!'],
            ['bid', 'require', '请填写应用ID!'],
            ['pid', 'require', '请填写产品ID!']
        ];
        $result = $this->validate(input('post.'), $rule);
        if (true !== $result) {
            return msg(1, $result);
        }
        //token检测
        $token = stripTags(input('post.token/s'));
//        $token = 'D32994C6-D4F0-8411-96DE-6D0BEC149C3F';
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
        //数据过滤
        $where = [];
        $where['bid'] = stripTags(input('post.bid/s'));
        $where['pid'] = stripTags(input('post.pid/s'));
        $where['bid'] = 1;
        $where['pid'] = 2;
        $data = [];
        $data['out_time'] = time();
        //出库
        $rs = model('StockModel')->save($data,$where);
        if(!$rs){
            return msg(1,'出库失败');
        }
        //出库成功
        $out_info =  StockModel::field(['id','tid','receipt'])->where($where)->find();
        $errno = 0;
        $txt = 0;
        $tid = $out_info['tid'];
        $receipt = $out_info['receipt'];
        return json(compact('errno', 'txt','tid','receipt'));
    }

    //报告出库结果
    public function report(){
        if(false==$this->is_https){
            return msg(-1,'当前接口暂不支持此协议');
        }
        //数据检测
        $rule = [
            ['token', 'require', '请输入token令牌!'],
            ['tid', 'require', '请填写交易ID!'],
            ['status', 'require', '请填写状态!']
        ];
        $result = $this->validate(input('post.'), $rule);
        if (true !== $result) {
            return msg(1, $result);
        }
        //token检测
        $token = stripTags(input('post.token/s'));
//        $token = 'D32994C6-D4F0-8411-96DE-6D0BEC149C3F';
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
        //数据过滤
        $where = [];
        $where['tid'] = stripTags(input('post.bid/s'));

        $where['tid'] = 1;
        $data = [];
        $status = stripTags(input('post.pid/s'));

        switch ($status){
            case 2:
                $data['status'] = 4;
                break;
            case 3:
                $data['status'] = 1;
                break;
                default;
        }

        //检测状态
        $out_info =  StockModel::field(['id','tid','receipt','status'])->where($where)->find();
        if($out_info['status']==1){
            return msg(1,'使用中');
        }
        //出库
        $rs = model('StockModel')->save($data,$where);
        if(!$rs){
            return msg(1,'出库失败');
        }


        return msg(0,'出库成功');
    }



}
