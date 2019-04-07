<?php
// +----------------------------------------------------------------------
// | @自定义常用
// +----------------------------------------------------------------------
/**
 * 将字符解析成数组
 * @param $str
 * @return array
 */
function parseParams($str)
{
    $arrParams = [];
    parse_str(html_entity_decode(urldecode($str)), $arrParams);
    return $arrParams;
}

/**
 * 统一返回信息
 * @param $errno
 * @param $txt
 * @param $token
 * @return array
 */
function msg($errno, $txt,$token='')
{

    if($token==''){
        return json(compact('errno', 'txt'));
    }
    return json(compact('errno', 'txt', 'token'));
}

/**
 * 对象转换成数组
 * @param $obj
 * @return array
 */
function objToArray($obj)
{
    return json_decode(json_encode($obj), true);
}

/**
 * 字符串过滤
 * @param $str
 * @return array
 */
function stripTags($str)
{
    return strip_tags(trim($str));
}

/** 生成GUID
 * @return string
 */
function create_guid(){
    $charid = strtoupper(md5(uniqid(mt_rand(), true)));
    $hyphen = chr(45);// "-"
    $guid = substr($charid, 6, 2).substr($charid, 4, 2).
        substr($charid, 2, 2).substr($charid, 0, 2).$hyphen
        .substr($charid, 10, 2).substr($charid, 8, 2).$hyphen
        .substr($charid,14, 2).substr($charid,12, 2).$hyphen
        .substr($charid,16, 4).$hyphen.substr($charid,20,12);
    return $guid;
}

/** 是否https
 * @return bool
 */
function is_https() {
    if ( !empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
        return true;
    } elseif ( isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ) {
        return true;
    } elseif ( !empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
        return true;
    }
    return false;
}
/** 是否拥有访问权限
 * @return bool
 */
function check_node($token = '',$contrl_name = '',$action_name=''){
    if(!$token||!$contrl_name||!$action_name){
        return false;
    }
    $user_info = \app\api\model\UserModel::field(['id','token','role_id'])
                ->with(['RoleModel'])
                ->where(['token'=>$token])
                ->find();
    $rule = objToArray($user_info)['role_model']['rule'];
    if($rule=="*"){
        return true;
    }
    if(!$rule){
        return false;
    }
    $node_info = \think\Db::name('node')->field(['id','control_name','action_name'])
        ->where(['control_name'=>$contrl_name,'action_name'=>$action_name])->find();
    $node_id = objToArray($node_info)['id'];
    $rule_arr = explode(',',$rule);
    if(!in_array($node_id,$rule_arr)){
        return false;
    }
    return true;
}


