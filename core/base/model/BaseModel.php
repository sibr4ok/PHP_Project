<?php

namespace core\base\model;

use core\base\controller\Singleton;
use core\base\exceptions\DbException;

class BaseModel
{
    use Singleton;

    protected $db;

    private function __construct()
    {
        try{
            //  инициализация подключения к mysql при помощи библиотеки mysqli
            $this->db = @new \mysqli(HOST, USER, PASS, DB_NAME);

            /*if($this->db->connect_error){ // ?
                throw new DbException('Ошибка подключения к базе данных: '
                    . $this->db->connect_errno . ' ' . $this->db->connect_error);
            }*/
            // устанавливаем кодировку соединения 
            $this->db->query("SET NAMES UTF8");
        }
        catch(\mysqli_sql_exception $e){
            throw new DbException($e->getMessage());
        }
    }

    // Входные параметры: 1-запрос 2-метод для осуществления запроса  3-идентификатор вставки
    final public function query($query, $crud = 'r', $return_id = false)
    {
        try{
            $result = $this->db->query($query);//   приходит объект содержащий выборку из баз данных

            switch($crud){
                
                case 'r':

                    if($result->num_rows){  //  если что-то пришло из базы данных

                        $res = [];

                        for($i = 0; $i < $result->num_rows; $i++){
                            //  вернет массив каждого ряда выборки который будет хранится в объекте result
                            $res[] = $result->fetch_assoc();
                        }

                        return $res;
                    }

                    return false;

                    break;
                case 's':

                    if($return_id) return $this->db->insert_id;//

                    return true;

                    break;
                default:
                    return true;

                    break;
            }

        }
        catch(\mysqli_sql_exception $e){
            throw new DbException($e->getMessage());
        }
    }

    /** $table - Таблица баз данных, $set - массив для запроса
     * 'fields' => ['id', 'name'],
     * 'where' => ['id' => 1, 'name' => 'Masha'],
     * 'operand' => ['<>', '='],
     * 'condition' => ['AND'],
     * 'order' => ['fio', 'name'], 
     * 'order_direction' => ['ASC','DESC'],
     * 'limit' => '1' */

    final public function get($table, $set = [])
    {
        $fields = $this->createFields($table, $set);
        $where = $this->createWhere($table, $set);

        $join_arr = $this->createJoin($table,$set);

        $fields .= $join_arr['fields'];
        $fields = rtrim($fields, ',');

        $join = $join_arr['join'];
        $where = $join_arr['where'];  

        $order = $this->createOrder($table, $set);

        $limit = $set['limit'] ? $set['limit'] : '';

        $query = "SELECT $fields FROM $table $join $where $order $limit";

        return $this->query($query);
    }

}