<?php
/*========================【14码接口】===========================*/
namespace app\api\controller;
use app\api\model\BunledModel;
use app\api\model\ProductModel;
use app\api\model\StockModel;
use app\api\model\UserModel;
use app\admin\model\MobleModel;
use think\Controller;

class Moble extends Controller
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
        $this->user_model = new UserModel;
        $this->moble_model = new MobleModel;
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


        /*
        *
        *添加设备信息
        */
        public function add(){
             //检测协议
             if(false==$this->is_https){
                return msg(-1,'当前接口暂不支持此协议');
            }

             //数据检测
             $rule = [
                ['username', 'require', '请填写账号!'],
                ['password', 'require', '请填写密码!']
            ];
        }


        /*
        *
        *查询设备信息
        */
        public function query(){
            //检测协议
            if(false==$this->is_https){
               return msg(-1,'当前接口暂不支持此协议');
           }

           //数据检测
        $rule = [
            ['token', 'require', '请输入token令牌!']
        ];
        $result = $this->validate(input(''), $rule);
        if (true !== $result) {
            return msg(1, $result);
        }
        //token检测
        $token = stripTags(input('token/s'));
        $user_id = $this->user_model->where(['token'=>$token])->value('id');
        if(!$user_id){
            return msg(1,'token令牌不存在');
        }

        //获取保存的14码数据
        $moble_info = $this->moble_model->where(['user_id'=>$user_id])->select();
        if(!$moble_info){
            return msg(10001,'该用户未查询到14码');
        }
        $devices = [];
        foreach ($moble_info as $key => $value) {
            $devices[$key]['name'] = $value['name'];
            $devices[$key]['sn'] = $value['sn'];
            $devices[$key]['wifi'] = $value['wifi'];
            $devices[$key]['ecid'] = hexToDecimal($value['ecid']);
            $devices[$key]['udid'] = $value['udid'];
            $devices[$key]['imei'] = $value['imei'];
            $devices[$key]['meid'] = $value['meid'];
            $devices[$key]['model_number'] = $value['model_number'];
            $devices[$key]['region_code'] = $value['region_code'];
            $devices[$key]['product_version'] = $value['product_version'];
            $devices[$key]['build_version'] = $value['build_version'];
            $devices[$key]['hardware_platform'] = $value['hardware_platform'];
            $devices[$key]['model_str'] = $value['model_str'];
            $devices[$key]['product_type'] = $value['product_type'];
            $devices[$key]['mlbsn'] = $value['mlbsn'];
        }
      
        $errno = 0;
        $txt = '获取成功';
        return json(compact('errno', 'txt', 'devices'));
       }
}