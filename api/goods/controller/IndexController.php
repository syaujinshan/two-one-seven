<?php
/**
 * Created by PhpStorm.
 * User: 91641
 * Date: 2018/6/12
 * Time: 21:03
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

    public function myAllGroup()
    {
         $goods = $this->myGoods();
         $mygoods = [];
         $i =0;
         foreach ($goods as $key => $v) {
            if ($v['deadline']<time()) {
                $v['status'] = '已完成';
            }else{
                $v['status'] = '未完成';
            }
            $mygoods[$i]['goods_id'] = $v['goods_id'];
            $mygoods[$i]['status'] = $v['status'];
            $mygoods[$i]['title'] = $v['title'];
            $mygoods[$i]['banner'] = $v['banner'];
            $mygoods[$i]['price'] = $v['price'];
            $i++;
         }
         $this->success($mygoods);    
    }
    public function activeGroup()
    {
         $goods = $this->myGoods();
         $mygoods = [];
         $i =0;
         foreach ($goods as $key => $v) {
            if ($v['deadline']>time()) {
                $mygoods[$i]['goods_id'] = $v['goods_id'];
                $mygoods[$i]['title'] = $v['title'];
                $mygoods[$i]['banner'] = $v['banner'];
                $mygoods[$i]['price'] = $v['price'];
            }
            $i++;
         }
         $this->success($mygoods); 
    }
    public function successGroup($value='')
    {
         $goods = $this->myGoods();
         $mygoods = [];
         $i =0;-
         foreach ($goods as $key => $v) {
            if ($v['deadline']<time()) {
                $mygoods[$i]['goods_id'] = $v['goods_id'];
                $mygoods[$i]['title'] = $v['title'];
                $mygoods[$i]['banner'] = $v['banner'];
                $mygoods[$i]['price'] = $v['price'];
            }
            $i++;
         }
         $this->success($mygoods); 
    }
    public function myCreateGroup()
    {
         $goods = $this->myGoods();
         $mygoods = [];
         $i =0;
         foreach ($goods as $key => $v) {
            if ($v['deadline']<time()) {
                $v['status'] = '拼团成功';
            }else{
                $v['status'] = '正在拼团';
            }
            $mygoods[$i]['goods_id'] = $v['goods_id'];
            $mygoods[$i]['status'] = $v['status'];
            $mygoods[$i]['title'] = $v['title'];
            $mygoods[$i]['banner'] = $v['banner'];
            $mygoods[$i]['price'] = $v['price'];
            $mygoods[$i]['deadline'] = $v['deadline'];
            $i++;
         }
         $this->success($mygoods);    
    }
    private function myGoods()
    {
        $token = Request::instance()->header('XX-token');
        if(empty($token)){
            $this->error('无效的token!');
        }
        $user = db('user')->where(['token'=>$token])->find();
        $user_id = $user['user_id'];
        $goodsUser = [];
        $goodsUser = db('goods') ->where(['user_id'=>$user_id])->select();
        if (empty($goodsUser)) {
             $this->success('还没有拼团哦！');
             exit();
         }

        $goods_id ='';
         foreach ($goodsUser as $key => $v) {
             $goods_id.=$v['goods_id'].',';
         }
         //var_dump($goods_id);
         //获取用户所有拼团记录
         $goods = db('goods')->where('id','in',$goods_id)->select();
         return $goods;
    }
}
