<?php
/**
 * Created by PhpStorm.
 * User: 91641
 * Date: 2018/6/7
 * Time: 17:22
 */
namespace api\goods\controller;

use think\Model;

class GoodsModel extends Model
{
    public function getPriceAttr($value){
        return $value/100;
    }
}