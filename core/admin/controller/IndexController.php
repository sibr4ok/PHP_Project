<?php

namespace core\admin\controller;

use core\base\controller\BaseController;
use core\admin\model\Model;

class IndexController extends BaseController{

    protected $name;

    protected function inputData(){ 

        $db = Model::instance();

        $table = "teachers";

        $files['gallery_img'] = ['old_red.jpj'];
        $files['img'] = 'main_img.jpg';

        $_POST['id'] = 5;
        $_POST['name'] = 'Miky';
        $_POST['content'] = "<p>'miky_song</p>";


        $res = $db->edit($table);

        exit('i`m admin panel');
    }

    protected function outputData(){

    }
}