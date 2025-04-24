<?php

namespace core\base\model;

use core\base\controller\Singleton;
use core\base\exceptions\DbException;

class BaseModel extends BaseModelMethods
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

    /**
     * Входные параметры:
     * $query - запрос
     * $crud - метод для осуществления запроса = r - SELECT / c - INSEPT / d - DELETE
     * $return_id - идентификатор вставки
     */
    final public function query($query, $crud = "r", $return_id = false)
    {
        try{
            $result = $this->db->query($query);//   приходит объект содержащий выборку из баз данных

            switch($crud){
                
                case "r":
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

                case "c":
                    if($return_id) return $this->db->insert_id;

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

    /** $table - Таблица баз данных, $set - массив для запроса:
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
        $fields = $this->createFields($set, $table);

        $order = $this->createOrder($set, $table);

        $where = $this->createWhere($set, $table);

        if(!$where) $new_where = true;
            else $new_where = false;

        $join_arr = $this->createJoin($set, $table, $new_where);

        $fields .= $join_arr['fields'];
        $fields = rtrim($fields, ',');

        $join = $join_arr['join'];
        $where .= $join_arr['where'];  

        $limit = $set['limit'] ? 'LIMIT ' . $set['limit'] : '';

        $query = "SELECT $fields FROM $table $join $where $order $limit";

        return $this->query($query);
    }

    /**
     * $table - таблица для вставки данных
     * array $set - массив параметров:
     * filds => [ поле => значения]; если не указан, то обрабатывается $_POST [поле=>значения]
     * разрешена передача например NOW() в качестве Mysql функции обычно строкой
     * files => ['исключение 1', 'исключение 2'] - исключает данные элементы массива из добавления в запрос 
     * return_id => true|false - возвращать или нет индентификатор вставленной записи
     * return mixed
     */

    final public function add($table, $set = []){

        $set['fields'] = (is_array($set['fields'])) && !empty($set['fields']) ? $set['fields'] : $_POST;
        $set['files'] = (is_array($set['files'])) && !empty($set['files']) ? $set['files'] : false;

        if(!$set['fields'] && !$set['files']) return false;

        $set['return_id'] = $set['return_id'] ? true : false;
        $set['except'] = (is_array($set['except'])) && !empty($set['except']) ? $set['except'] : false;

        $insert_arr = $this->createInsert($set['fields'], $set['files'], $set['except']);

        if($insert_arr){
            //INSERT INTO teachers (name, surname, age) VALUES ('masha', 'sergeivna', '21'),('Slavia', 'Persunova', '19')
            $query = "INSERT INTO $table ({$insert_arr['fields']}) VALUES ({$insert_arr['values']})";

            return $this->query($query, 'c', $set['return_id']);
        }

        return false;
    }


    /**
     * 'fields' => ['name' => 'Sveta'],
     * 'files' => $files,
     * 'where' => ['id' => 1]
     */
    final public function edit($table, $set = []){

        $set['fields'] = (is_array($set['fields'])) && !empty($set['fields']) ? $set['fields'] : $_POST;
        $set['files'] = (is_array($set['files'])) && !empty($set['files']) ? $set['files'] : false;

        if(!$set['fields'] && !$set['files']) return false;

        $set['except'] = (is_array($set['except'])) && !empty($set['except']) ? $set['except'] : false;

        if(!$set['all_rows']){

            if($set['where']){
                $where = $this->createWhere($set);
            }else{
                $columns = $this->showColumns($table);

                if(!$columns) return false;

                if($columns['id_row'] && $set['fields'][$columns['id_row']]){//id_row
                    $where = 'WHERE ' . $columns['id_row'] . '=' . $set['fields'][$columns['id_row']];
                    unset($set['fields'][$columns['id_row']]);
                }
            }

        }

        $update = $this->createUpdate($set['fields'], $set['files'], $set['except']);

        //UPDATE table SET name='slavia',surname='Persunova' WHERE id=1
        $query = "UPDATE $table SET $update $where";

        return $this->query($query, 'u');
    }

    /** $table - Таблица баз данных, $set - массив для запроса:
     * 'fields' => ['id', 'name'],
     * 'where' => ['id' => 1, 'name' => 'Masha'],
     * 'operand' => ['<>', '='],
     * 'condition' => ['AND'], 
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


    final public function delete($table, $set){

        $table = trim($table);

        $where = $this->createWhere($set,$table);

        $columns = $this->showColumns($table);
        
        if(!$columns) return false;

        if(is_array($set['fields']) && !empty($set['fields'])){

            if($columns['id_row']){
                $key = array_search($columns['id_row'], $set['fields']);
                if($key !== false) unset($set['fields'][$key]);
            }

            $fields = [];

            foreach($set['fields'] as $field){

                $fields[$field] = $columns[$field]['Default'];

            }

            $update = $this->createUpdate($fields, false, false);

            //UPDATE teachers SET name=NULL,img=NULL WHERE teachers.id = '2'
            $query = "UPDATE $table SET $update $where";
            
        }else{

            $join_arr = $this->createJoin($set, $table);
            $join = $join_arr['join'];
            $join_tables = $join_arr['tables'];

            //DELETE category,products FROM category LEFT JOIN products ON category.id=products.parent_id WHERE id=1
            $query = 'DELETE ' . $table . $join_tables . ' FROM ' . $table . ' ' . $join . ' ' . $where;

        }
        return $this->query($query, 'u');
    }

    final public function showColumns($table){

        $query = "SHOW COLUMNS FROM $table";
        $res = $this->query($query);

        $columns = [];

        if($res){

            foreach($res as $row){
                $columns[$row['Field']] = $row;
                if($row['Key'] === 'PRI'){
                    $columns['id_row'] = $row['Field'];
                }
            }

        }
        return $columns;
    }

}