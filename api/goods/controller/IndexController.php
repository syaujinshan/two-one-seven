<?php
/**
 * Created by PhpStorm.
 * User: 91641
 * Date: 2018/6/7
 * Time: 19:07
 */

namespace api\goods\controller;

use api\goods\model\GoodsUserModel;
use cmf\controller\RestUserBaseController;
use think\Log;
use think\Request;
use api\goods\model\GoodsModel;

class IndexController extends RestUserBaseController
{
    private $goodsModel;
    private $goodsUserModel;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->goodsModel = new GoodsModel();
        $this->goodsUserModel = new GoodsUserModel();
    }

    public function imageUpload(Request $request){
        $image = $request->file('file');
        if($image){
            $info = $image->move(ROOT_PATH.'public'.DS.'upload');
            if ($info) {
                $this->success('文件上传成功',$info->getSaveName());
            } else {
                // 上传失败获取错误信息
                $this->error($image->getError());
            }
        }else{
            $this->error('请选择文件');
        }
    }
    public function addGoods(){
        $data = $this->request->param();
        Log::info($data);
        if(empty($data)){
            $this->error('提交数据不能为空');
        }
        $data['owner'] = $this->getUserId();
        $this->goodsModel->save($data);
        $this->success('添加成功');
    }

    public function goodsList(){
        try {
            $list = $this->goodsModel->order('create_time desc')->field('id,title,banner,amount,price')->select();
        }  catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success('获取成功',$list);
    }

    public function goodsDetail(){
        $good_id = $this->request->param('goods_id');
        try {
            $data = $this->goodsModel->where('id', $good_id)->field('id,title,banner,desc,amount,price')->find();
            $count = $this->goodsUserModel->where([
                'goods_id' => $good_id,
                'user_id'  => $this->getUserId()
            ])->count();
            if ($count == 0)
                $data['isJoin'] = false;
            else
                $data['isJoin'] = true;
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('获取成功',$data);
    }
}