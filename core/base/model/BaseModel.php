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
     * 'limit' => '1',
     * 'join' => [
     *   [
     *       'table' => 'join_table1',
     *       'fields' => ['id as j_id', 'name as j_name'],
     *       'type' => 'left',
     *       'where' => ['name' => 'sasha'],
     *       'operand' => ['='],
     *       'condition' => ['OR'],
     *       'on' => ['id', 'parent_id'],
     *       'group_condition' => 'AND'
     *   ],
     *   'join_table2' => [
     *       'table' => 'join_table2',
     *       'fields' => ['id as j2_id', 'name as j2_name'],
     *       'type' => 'left',
     *       'where' => ['name' => 'sasha'],
     *       'operand' => ['='],
     *       'condition' => ['AND'],
     *       'on' => [
     *           'table' => 'teachers',
     *           'fields' => ['id', 'parent_id']
     *       ]
     *   ]
     * ]
     */

    final public function get($table, $set = [])
    {
        $fields = $this->createFields($table, $set);
        $where = $this->createWhere($table, $set);

        if(!$where) $new_where = true;
            else $new_where = false;

        $join_arr = $this->createJoin($table, $set, $new_where);

        $fields .= $join_arr['fields'];
        $fields = rtrim($fields, ',');

        $join = $join_arr['join'];
        $where = $join_arr['where'];  

        $order = $this->createOrder($table, $set);

        $limit = $set['limit'] ? $set['limit'] : '';

        $query = "SELECT $fields FROM $table $join $where $order $limit";

        return $this->query($query);
    }

    protected function createFields($table = false, $set){

        $set['fields'] = is_array($set['fields']) && !empty($set['fields'])
                         ? $set['fields'] : ['*']; 

        $table = $table ? $table . '.': ''; 

        $fields = '';

        foreach($set['fields'] as $field){
            $fields .= $table . $field . ',';
        }

        return $fields;

    }

    protected function createOrder($table = false, $set){
    
        $table = $table ? $table . '.': ''; 

        $order_by = '';

        if(is_array($set['order']) && !empty($set['order'])){

            $set['order_direction'] = is_array($set['order_direction']) && !empty($set['order_direction'])
                         ? $set['order_direction'] : ['ASC']; 

            $order_by = 'ORDER BY ';

            $direct_count = 0;

            foreach($set['order'] as $order){

                if($set['order_direction'][$direct_count]){

                    $order_direction = strtoupper($set['order_direction'][$direct_count]);
                    $direct_count++;
                }else{

                    $order_direction = strtoupper($set['order_direction'][$direct_count - 1]);
                }

                if(is_int($order)) $order_by .= $order . ' ' . $order_direction . ',';
                    else $order_by .= $table . $order . ' ' . $order_direction . ',';
            }   

            $order_by = rtrim($order_by, ',');
        }
        return $order_by;
    }

    protected function createWhere($table = false, $set, $instruction = 'WHERE'){
     
        $table = $table ? $table . '.': '';

        $where = '';

        if(is_array($set['where']) && !empty($set['where'])){

            $set['operand'] = is_array($set['operand']) && !empty($set['operand']) 
                                ? $set['operand'] : ['='];

            $set['condition'] = is_array($set['condition']) && !empty($set['condition']) 
                                ? $set['condition'] : ['AND'];
                                
            $where = $instruction;

            $operand_count = 0;
            $condition_count = 0;

            foreach($set['where'] as $key => $item){

                $where .= ' ';

                if($set['operand'][$operand_count]){

                    $operand = $set['operand'][$operand_count];
                    $operand_count++;
                }else{

                    $operand = $set['operand'][$operand_count - 1];
                }

                if($set['condition'][$condition_count]){

                    $condition = $set['condition'][$condition_count];
                    $condition_count++;
                }else{

                    $condition = $set['condition'][$condition_count - 1];
                }

                if($operand === 'IN' || $operand === 'NOT IN'){

                    if(is_string($item) && strpos($item , 'SELECT')){

                        $in_str = $item;
                    }else{

                        if(is_array($item)) $temp_item = $item; 
                            else $temp_item = explode(',', $item);

                        $in_str = '';

                        foreach($temp_item as $v){

                            $in_str .= "'" . trim($v) . "',";
                        }
                    }
                    $where .= $table . $key . ' ' . $operand . ' (' . rtrim($in_str , ',') . ') ' . $condition;

                }elseif(strpos($operand, 'LIKE')!== false){

                    $like_template = explode('%', $operand);

                    foreach($like_template as $lt_key => $lt){
                        if(!$lt){
                            if(!$lt_key){
                                $item = '%' . $item;
                            }else{
                                $item .= '%';
                            }
                        }
                    }

                    $where .= $table . $key . ' LIKE ' . "'" . $item . "' " . $condition;
                    
                }else{
                    
                    if(strpos($item, 'SELECT') === 0){
                        $where .= $table . $key . ' ' . $operand . ' (' . $item . ') ' . $condition;
                    }else{
                        $where .= $table . $key . ' ' . $operand . " '" . $item . "' " . $condition;
                    }

                }

            }
            $where = substr($where, 0 , strrpos($where, $condition));

        }
        return $where;

    }

    protected function createJoin($table, $set, $new_where = false){
        
        $fields = '';
        $join = '';
        $where = '';

        if($set['join']){

            $join_table = $table;

            foreach($set['join'] as $key => $item){

                if(is_int($key)){
                    if(!$item['table']) continue;
                        else $key = $item['table'];
                }

                if($join) $join .= ' ';

                if($item['on']){

                    $join_fields = [];

                    switch (2){

                        case count($item['on']['fields']):
                            $join_fields = $item['on']['fields'];
                            break;

                        case count($item['on']):
                            $join_fields = $item['on'];
                            break;

                        default:
                            continue 2;
                            break;
                    }

                    if(!$item['type']) $join .= 'LEFT JOIN ';
                        else $join .= trim(strtoupper($item['type'])) . ' JOIN ';
                    
                    $join .= $key . ' ON ';

                    if($item['on']['table']) $join .= $item['on']['table'];
                        else $join .= $join_table;

                    $join .= '.' . $join_fields[0] . '=' . $key . '.' . $join_fields[1];

                    $join_table = $key;

                    if($new_where){

                        if($item['where']){
                            $new_where = false;
                        }

                        $group_condition = 'WHERE';
                    }else{

                        $group_condition = $item['group_condition'] ? strtoupper($item['group_condition']) : 'AND';
                    }

                    $fields .= $this->createFields($key, $item);

                    $where .= $this->createWhere($key, $item, $group_condition);

                }

            }

        }
        return compact('fields', 'where', 'join');
    }

}