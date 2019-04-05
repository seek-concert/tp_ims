<?php
/*========================【档位接口】===========================*/
namespace app\api\controller;
use app\api\model\UserModel;
use think\Controller;

class Files extends Controller
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

    //登陆
    public function signin(){
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

    //查询档位
    public function product_query(){
        if(false==$this->is_https){
            return msg(-1,'当前接口暂不支持此协议');
        }
        //数据检测
        $rule = [
            ['token', 'require', '请输入token令牌!'],
            ['bid', 'require', '请填写应用ID!']
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
        //查询档位


        $errno = 0;
        $txt = '查询成功';
        $products = '';

        return json(compact('errno','txt','products'));

    }

    //档位修改
    public function product_modify(){
        if(false==$this->is_https){
            return msg(-1,'当前接口暂不支持此协议');
        }
        //数据检测
        $rule = [
            ['token', 'require', '请输入token令牌!'],
            ['id', 'require', '请填写档位ID!'],
            ['name', 'require', '请填写档位名称!']
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
        //修改档位
        $rs = 1;
        if(!$rs){
            return msg(1,'修改失败');
        }


        return msg(0,'修改成功');

    }

    //档位删除
    public function product_delete(){
        if(false==$this->is_https){
            return msg(-1,'当前接口暂不支持此协议');
        }
        //数据检测
        $rule = [
            ['token', 'require', '请输入token令牌!'],
            ['id', 'require', '请填写档位记录ID!']
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
        //删除档位
        $rs = 1;
        if(!$rs){
            return msg(1,'删除失败');
        }

        return msg(0,'删除成功');

    }


}