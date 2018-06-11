<?php
/**
 * Created by PhpStorm.
 * User: 91641
 * Date: 2018/6/11
 * Time: 16:08
 */

namespace api\goods\service;

use api\goods\model\WalletModel;
use api\goods\model\MoneyRecordModel;
use think\Db;
use think\Log;

class WalletService
{
    private $walletModel;
    private $moneyRecordModel;

    public function __construct()
    {
        $this->walletModel = new WalletModel();
        $this->moneyRecordModel = new MoneyRecordModel();
    }

    /**
     * 创建钱包
     *
     * @param $user_id
     */
    public function createWallet($user_id){
        $this->walletModel->save([
            'user_id' => $user_id,
            'balance' => 0
        ]);
    }

    /**
     * 金额增加
     *
     * @param $user_id
     * @param $amount
     * @param $remark
     * @return string
     */
    public function moneyIn($user_id,$amount,$remark){
        Db::startTrans();
        try{
            $record_data = [
                'user_id' => $user_id,
                'type'    => 0,
                'amount'  => $amount,
                'remark'  => $remark
            ];
            $this->moneyRecordModel->save($record_data);
            $this->walletModel->save([
               'balance'  => Db::raw("balance+$amount")
            ]);
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            return $e->getMessage();
        }

    }

    /**
     * 金额减少
     *
     * @param $user_id
     * @param $amount
     * @param $remark
     * @return bool|string
     */
    public function moneyOut($user_id,$amount,$remark){
        Db::startTrans();
        if($amount > $this->getBalance($user_id)){
            return "余额不足";
        }
        try{
            $record_data = [
                'user_id' => $user_id,
                'type'    => 1,
                'amount'  => $amount,
                'remark'  => $remark
            ];
            $this->moneyRecordModel->save($record_data);
            $this->walletModel->save([
                'balance'  => Db::raw("balance-$amount")
            ],['user_id'=>$user_id]);
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            Log::error($e->getTraceAsString());
            return "系统错误";
        }
        return true;
    }
    protected function getBalance($user_id){
        return $this->walletModel->where('user_id',$user_id)->value('balance');
    }

}