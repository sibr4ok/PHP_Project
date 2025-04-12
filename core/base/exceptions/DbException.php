<?php

namespace core\base\exceptions;

use core\base\controller\BaseMethods;

//класс исключения
class DbException extends \Exception
{
    protected $messages;

    use BaseMethods;    //trait

    public function __construct($message = '', $code  = 0)
    { 
        parent::__construct($message, $code);   //вызов метода родительского класса 

        $this->messages = include 'messages.php';   //вернет в messages массив из файла

        //сообщение для log
        $error = $this->getMessage() ? $this->getMessage() : $this->messages[$this->getCode()];
        $error .= "\r\n" . 'file ' . $this->getFile() . "\r\n" . 'In line ' . $this->getLine() . "\r\n";

        //if($this->messages[$this->getCode()]) $this->message = $this->messages[$this->getCode()]; 
        
        $this->writeLog($error, 'db_log.txt');
    }

}