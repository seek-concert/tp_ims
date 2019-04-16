<?php
/*========================【14码接口】===========================*/

namespace app\api\controller;

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
            $this->is_https = false;
        }
    }

                return msg(1, '网络异常，token生成失败');
            }



    /*
    *
    *添加设备信息
    */
    public function add()
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