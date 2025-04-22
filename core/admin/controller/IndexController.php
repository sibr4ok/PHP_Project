<?php

namespace core\admin\controller;

use core\base\controller\BaseController;
use core\admin\model\Model;

class IndexController extends BaseController{

    protected $name;

    protected function inputData(){ 

        $db = Model::instance();

        $table = "teachers";

        $files['gallery_img'] = ['red', 'blue', 'black'];
        $files['img'] = 'main_img.jpg';


        $res = $db->add($table, [
            'fields' => ['name' => 'Slavia', 'content' => 'hello'],
            'except'=> ['name'],
            'files' => $files
        ]);

        exit('id = ' . $res['id'] . 'Name = ' . $res['name']);
    }

    protected function outputData(){

    }
}