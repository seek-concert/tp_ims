<?php
/**
 * 文件路径： \application\index\job\Hello.php
 * 这是一个消费者类，用于处理 helloJobQueue 队列中的任务
 */
namespace app\admin\job;
use think\queue\Job;
use think\Db;

class Hello {
    /**
     * fire方法是消息队列默认调用的方法
     * @param Job            $job      当前的任务对象
     * @param array|mixed    $data     发布任务时自定义的数据
     */
    public function fire(Job $job,$data){
            $id = $data['id'];
            //根据id获取信息
            $consumerlog = DB::name('consumer_log')
            ->where(['id'=>$id])
            ->find();
            if($consumerlog['status'] != 3){
                $job->delete();
                return ;
            }
            //开启事务
            Db::startTrans();
            try {
                
                //更改记录状态为成功
                $consumerlog_update = Db::name('consumer_log')
                    ->where(['id'=>$id])
                    ->setField('status',1);
                //获取对应卖家信息
                $user_detail = DB::name('user_detail')
                    ->where(['uid'=>$consumerlog['seller_id']])
                    ->find();
                $balance = $user_detail['balance'] + $consumerlog['real_price'];
                $funds = $user_detail['funds'] - $consumerlog['real_price'];
                //更改用户余额和冻结金额
                $user_detail_update = Db::name('user_detail')
                    ->where(['uid'=>$consumerlog['seller_id']])
                    ->setField(['balance'=>$balance,'funds'=>$funds]);
                if ($consumerlog_update && $user_detail_update) {
                    // 提交事务
                    Db::commit();
                    $job->delete();
                    return ;
                } else {
                    // 回滚事务
                    Db::rollback();
                    $job->delete();
                    return ;
                }
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                $job->delete();
                return ;
            }
      
        }
    }