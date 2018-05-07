<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/7
 * Time: 22:24
 */
namespace App\Libs;

class Helper
{
    static public function getTree($array, $pid =0)
    {
        $list = [];
        foreach ($array as $key => $value){
            if ($value['parent_id'] == $pid){
                if ($value['parent_id'] == 0) {
                    $value['list'] = self::getTree($array, $value['id']);
                }
                $list[] = $value;
            }
        }

        return $list;
    }
}