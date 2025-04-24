<?php

namespace core\admin\controller;

use core\base\controller\BaseController;
use core\admin\model\Model;

class IndexController extends BaseController{

    protected $name;

    protected function inputData(){ 

        $db = Model::instance();

        $table = "teachers";


        $res = $db->delete($table, [
            'where' => ['id' => 2],
            'join' => [
                [
                    'table' => 'students',
                    'on' => ['student_id', 'id']
                ],
            ]
        ]);

        exit('i`m admin panel');
    }

    protected function outputData(){

    }
}