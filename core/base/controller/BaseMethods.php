<?php

namespace core\base\controller;

use DateTime;

trait BaseMethods
{
    protected $styles;
    protected $scripts;
    
    protected function init($admin = false){
        if(!$admin){
            if(USERS_CSS_JS['styles']){
                foreach(USERS_CSS_JS['styles'] as $item) $this->styles[] = PATH . TEMPLATE . trim($item, '/');
            }

            if(USERS_CSS_JS['scripts']){
                foreach(USERS_CSS_JS['scripts'] as $item) $this->scripts[] = PATH . TEMPLATE . trim($item, '/');
            }
        }else{
            if(ADMIN_CSS_JS['styles']){
                foreach(ADMIN_CSS_JS['styles'] as $item) $this->styles[] = PATH . ADMIN_TEMPLATE . trim($item, '/');
            }

            if(ADMIN_CSS_JS['scripts']){
                foreach(ADMIN_CSS_JS['scripts'] as $item) $this->scripts[] = PATH . ADMIN_TEMPLATE . trim($item, '/');
            }
        }

    }

    protected function clearStr($str){
        if(is_array($str)){
            foreach($str as $key => $item) $str[$key] = trim(strip_tags($item));
            return $str;
        }else{
            return trim(strip_tags($str));
        }

    }

    protected function clearNum($num){
        if(is_numeric($num)) return $num * 1;
            else return 0;
    }

    protected function isPost(){
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    protected function isAjax(){
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    protected function redirect($http = false, $code = false){
        if($code){
            $codes = ['301' => 'HTTP/1.1 301 Move Permanently']; 

            if($codes[$code]) header($codes[$code]);
        }

        if($http) $redirect = $http;
            else $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : PATH;

            header('Location:' . $redirect);

            exit;
    }

    protected function writeLog($message, $file = 'log.txt', $event = 'Fault'){
        $dateTime = new \DateTime();

        $str = $event . ': ' . $dateTime->format('d-m-Y G:i:s') . ' - ' . $message . "\r\n";

        file_put_contents('log/' . $file, $str, FILE_APPEND);
    }


}