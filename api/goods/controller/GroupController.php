<?php
/**
 * Created by PhpStorm.
 * User: 91641
 * Date: 2018/6/11
 * Time: 15:03
 */

namespace api\goods\controller;


use api\goods\model\GoodsUserModel;
use api\goods\service\WalletService;
use cmf\controller\RestUserBaseController;
use think\Db;
use think\exception\DbException;
use think\Log;
use think\Request;
use api\goods\model\GoodsModel;

class GroupController extends RestUserBaseController
{
    private $goodsModel;
    private $walletService;
    private $goodsUserModel;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->goodsModel = new GoodsModel();
        $this->walletService = new WalletService();
        $this->goodsUserModel = new GoodsUserModel();
    }
    public function joinGroup(){
        $goods_id = $this->request->param('goods_id');
        if(empty($goods_id)){
            $this->error('无效的商品');
        }
        try {
            $goods = GoodsModel::get($goods_id);
        } catch (DbException $e) {
            $this->error('数据库错误');
        }
        if ($goods->deadline < time()){
            $this->error('拼团已结束');
        }
        $remark = '商品名称-'.$goods->title;
        $result = $this->walletService->moneyOut(
            $this->getUserId(),
            $goods->getData('price'),
            $remark
            );
        if($result !== true){
            $this->error($result);
        }
        Db::startTrans();
        try{
            $this->goodsModel->save([
                'amount' => Db::raw('amount-1')
            ],['id'=>$goods_id]);

            $this->goodsUserModel->save([
                'user_id'  => $this->getUserId(),
                'goods_id' => $goods_id
            ]);
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            Log::error($e->getTraceAsString());
            $this->error($e->getMessage());
        }

        $this->success('参团成功');
    }
}