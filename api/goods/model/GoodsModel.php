<?php
/**
 * Created by PhpStorm.
 * User: 91641
 * Date: 2018/6/7
 * Time: 17:22
 */
namespace api\goods\model;

use think\Model;

class GoodsModel extends Model
{
    protected $autoWriteTimestamp = true;

    public function user(){
        return $this->belongsToMany('User','goods_user');
    }
    public function getDeadline($goods_id){
        return self::where('id',$goods_id)->value('deadline');
    }
    public function getPrice($goods_id){
        return self::where('id',$goods_id)->value('price');
    }
    public function getPriceAttr($value){
        return $value/100;
    }

    public function setPriceAttr($value){
        return $value*100;
    }

    public function getBannerAttr($value){
        return 'http://'.$_SERVER['HTTP_HOST'].DS.'upload'.DS.$value;
    }

    public function setRuleAttr($value){
        return json_encode($value);
    }
}