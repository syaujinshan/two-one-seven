<?php
/**
 * Created by PhpStorm.
 * User: 91641
 * Date: 2018/6/7
 * Time: 19:07
 */

namespace api\goods\controller;



use cmf\controller\RestBaseController;
use think\Request;

class IndexController extends RestBaseController
{
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
}