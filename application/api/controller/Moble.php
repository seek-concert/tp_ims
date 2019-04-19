<?php
/*========================【14码接口】===========================*/

namespace app\api\controller;

use app\admin\model\MobleModel;
use app\admin\model\UserDetailModel;
use app\api\model\UserModel;
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
        if (false === $is_https) {
            $this->is_https = false;
        }
        $this->user_model = new UserModel();
        $this->user_detail_model = new UserDetailModel();
        $this->moble_model = new MobleModel();
    }

    //登陆
    public function login()
    {
        //检测协议
        if (false == $this->is_https) {
            return msg(-1, '当前接口暂不支持此协议');
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
        $user_info = UserModel::field(['id', 'user_name', 'password', 'status', 'token'])->where(['user_name' => $user])->find();
        if (!$user_info) {
            return msg(1, '该账号不存在');
        }
        if ($user_info['status'] == 2) {
            return msg(1, '该账号已停用');
        }
        if (md5($pwd) !== $user_info['password']) {
            return msg(1, '密码错误，请重新输入');
        }

        //权限检测--使用时间是否到期
        $moble_time = $this->user_detail_model->where(['uid' => $user_info['id']])->value('moble_time');
        if (empty($moble_time)) {
            return msg(1, '您没有权限');
        }
        $time = time();
        if ($moble_time < $time) {
            return msg(1, '使用时间已到期');
        }

        //生成token
        $token = create_guid();
        try {
            $rs = model('UserModel')->save(['token' => $token], ['id' => $user_info['id']]);
            if (!$rs) {
                return msg(1, '网络异常，token生成失败');
            }
        } catch (\Exception $e) {
            return msg(1, '网络异常，token生成失败!');
        }

        return msg(0, '登陆成功', $token);
    }


    /*
    *
    *添加设备信息
    */
    public function add()
    {
        if (false == $this->is_https) {
            return msg(-1, '当前接口暂不支持此协议');
        }

        //数据检测
        $rule = [
            ['token', 'require', '请输入token令牌!'],
            ['name', 'require', '请输入设备名称!'],
            ['sn', 'require', '请输入序列号!'],
            ['wifi', 'require', '请输入Wi-Fi地址!'],
            ['bluetooth', 'require', '请输入蓝牙地址!'],
            ['ecid', 'require', '请输入芯片标识!'],
            ['udid', 'require', '请输入唯一设备识别符!'],
            ['imei', 'require', '请输入imei!'],
            ['meid', 'require', '请输入meid!'],
            ['model_number', 'require', '请输入设备型号!'],
            ['region_code', 'require', '请输入区域码!'],
            ['product_version', 'require', '请输入系统版本!'],
            ['build_version', 'require', '请输入产品版本!'],
            ['hardware_platform', 'require', '请输入硬件平台!'],
            ['model_str', 'require', '请输入硬件型号!'],
            ['product_type', 'require', '请输入产品类型!'],
            ['mlbsn', 'require', '请输入MLB序列号!']
        ];
        $result = $this->validate(input(''), $rule);
        if (true !== $result) {
            return msg(1, $result);
        }

        //token检测
        $token = stripTags(input('token/s'));
        $user_id = $this->user_model->where(['token' => $token])->value('id');
        if (!$user_id) {
            return msg(1, 'token令牌不存在');
        }

        //权限检测--使用时间是否到期
        $moble_time = $this->user_detail_model->where(['uid' => $user_id])->value('moble_time');
        if (empty($moble_time)) {
            return msg(1, '您没有权限');
        }
        $time = time();
        if ($moble_time < $time) {
            return msg(1, '使用时间已到期');
        }

        //数据过滤
        $data = [];
        $model_number = stripTags(input('model_number/s'));
        $region_code = stripTags(input('region_code/s'));
        $data['name'] = stripTags(input('name/s'));
        $data['user_id'] = $user_id;
        $data['input_time'] = time();
        $data['sn'] = stripTags(input('sn/s'));
        $data['wifi'] = stripTags(input('wifi/s'));
        $data['bluetooth'] = stripTags(input('bluetooth/s'));
        $data['ecid'] = stripTags(input('ecid/s'));
        $data['udid'] = stripTags(input('udid/s'));
        $data['imei'] = stripTags(input('imei/s'));
        $data['meid'] = stripTags(input('meid/s'));
//        $data['model_number'] = substr($model_number, 0, 5);
//        $data['region_code'] = substr(substr($region_code, 0, strpos($region_code, '/')),5);
        $data['model_number'] = stripTags(input('model_number/s'));
        $data['region_code'] = stripTags(input('region_code/s'));
        $data['product_version'] = stripTags(input('product_version/s'));
        $data['build_version'] = stripTags(input('build_version/s'));
        $data['hardware_platform'] = stripTags(input('hardware_platform/s'));
        $data['model_str'] = stripTags(input('model_str/s'));
        $data['product_type'] = stripTags(input('product_type/s'));
        $data['mlbsn'] = stripTags(input('mlbsn/s'));

        //新增
        $rs = $this->moble_model->save($data);
        if (!$rs) {
            return msg(1, '添加设备信息失败');
        }

        $errno = 0;
        $txt = '添加设备信息成功';
        return msg($errno, $txt);
    }

    /*
    *
    *查询设备信息
    */
    public function query()
    {
        //检测协议
        if (false == $this->is_https) {
            return msg(-1, '当前接口暂不支持此协议');
        }

        //token检测
        $token = stripTags(input('token/s'));
        $user_id = $this->user_model->where(['token' => $token])->value('id');
        if (!$user_id) {
            return msg(1, 'token令牌不存在');
        }

        //权限检测--使用时间是否到期
        $moble_time = $this->user_detail_model->where(['uid' => $user_id])->value('moble_time');
        if (empty($moble_time)) {
            return msg(1, '您没有权限');
        }
        $time = time();
        if ($moble_time < $time) {
            return msg(1, '使用时间已到期');
        }

        //获取保存的14码数据
        $moble_info = $this->moble_model->where(['user_id' => $user_id])->select();
        if (!$moble_info) {
            return msg(10001, '该用户未查询到14码');
        }
        $devices = [];
        foreach ($moble_info as $key => $value) {
            $devices[$key]['id'] = $value['id'];
            $devices[$key]['name'] = $value['name'];
            $devices[$key]['sn'] = $value['sn'];
            $devices[$key]['wifi'] = $value['wifi'];
            $devices[$key]['bluetooth'] = $value['bluetooth'];
            $devices[$key]['ecid'] = $value['ecid'];
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