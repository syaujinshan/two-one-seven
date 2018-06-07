<?php
/**
 * Created by PhpStorm.
 * User: 91641
 * Date: 2018/6/7
 * Time: 19:07
 */

namespace api\goods\controller;

use cmf\controller\RestBaseController;
use think\Log;
use think\Request;
use api\goods\model\GoodsModel;

class IndexController extends RestBaseController
{
    private $goodsModel;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->goodsModel = new GoodsModel();
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

        $this->goodsModel->save($data);
        $this->success('添加成功');
    }

    public function goodsList(){
        $list = $this->goodsModel->order('create_time desc')->field('id,title,banner,amount,price')->select();

        $this->success('获取成功',$list);
    }
}