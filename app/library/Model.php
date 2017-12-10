<?php

use PhpOffice\PhpWord\Element\Object;

class Model
{

    protected $db = null;

    protected $options = array();

    // 查询表达式
    protected $selectSql = 'SELECT%DISTINCT% %FIELD% FROM %TABLE%%JOIN%%WHERE%%GROUP%%HAVING%%ORDER%%LIMIT%';

    public function __construct($table = null)
    {
        if (! is_null($table)) {
            $this->options['table'] = (Db::getConfig())['prefix'] . $table;
        }
        $this->db = $this->db();
    }

    /**
     * 数据库对象实例
     * @return Object
     */
    protected function db()
    {
        return Db::getInstance();
    }

    /**
     * sql查询
     * @param string $sql
     * @return array
     */
    public function query(string $sql):array
    {
        return $this->db->query($sql);
    }

    /**
     * 查询单条数据
     * @return array|NULL
     */
    public function find()
    {
        $this->limit(1);
        $sql = $this->selectSql();
        $data = $this->db->query($sql);
        if ($data) {
            return $data[0];
        } else {
            return null;
        }
    }

    /**
     * sql查询
     * @return array
     */
    public function select():array
    {
        $sql = $this->selectSql();
        return $this->db->query($sql);
    }

    /**
     * 插入数据
     * @param array $data
     * @return int
     */
    public function add(array $data):int
    {
        if (is_array($data)) {
            $keysStr = '';
            $valueStr = '';
            foreach ($data as $key => $v) {
                $keysStr .= "`" . $key . "`,";
                $valueStr .= "'" . $this->escapeString($v) . "',";
            }
            $keysStr = substr($keysStr, 0, - 1);
            $valueStr = substr($valueStr, 0, - 1);
            $sql = "INSERT INTO " . $this->options['table'] . "(" . $keysStr . ")VALUES(" . $valueStr . ")";
            return $this->db->execute($sql);
        } else {
            return $this->db->execute($sql);
        }
    }

    /**
     * SQL指令安全过滤
     * 
     * @access public
     * @param string $str
     *            SQL字符串
     * @return string
     */
    protected function escapeString(string $str)
    {
        return addslashes($str);
    }

    /**
     * 分析表
     * 
     * @param string $table            
     * @return Model
     */
    public function table($table)
    {
        $this->options['table'] = (Db::getConfig())['prefix'] . $table;
        return $this;
    }

    /**
     * where条件分析
     * 
     * @param mixed $where            
     * @param mixed $parse            
     * @return Model
     */
    public function where($where)
    {
        ;
        $this->options['where'] = $where;
        return $this;
    }

    /**
     * 分组查询
     * @param string $group
     * @return Model
     */
    public function group(string $group):Model
    {
        $this->options['group'] = $group;
        return $this;
    }

    /**
     * 指定查询数量
     * 
     * @access public
     * @param mixed $offset
     *            起始位置
     * @param mixed $length
     *            查询数量
     * @return Model
     */
    public function limit($offset, $length = null):Model
    {
        if (is_null($length) && strpos($offset, ',')) {
            list ($offset, $length) = explode(',', $offset);
        }
        $this->options['limit'] = intval($offset) . ($length ? ',' . intval($length) : '');
        return $this;
    }

    /**
     * 查询的字段
     * @param string $field
     * @return Model
     */
    public function field(string $field):Model
    {
        if ($field == true) {
            $this->options['field'] = $field ? $field : ' * ';
        } else {
            if (is_string($field)) {
                $this->options['field'] = $field;
            }
        }
        return $this;
    }

    /**
     * 过滤
     * @param string $distinct
     * @return Model
     */
    public function distinct(string $distinct):Model
    {
        $this->options['distinct'] = $distinct;
        return $this;
    }

    /**
     * 联合查询
     * @param string $joion
     * @return Model
     */
    public function join(string $joion):Model
    {
        $this->options['join'] = $joion;
        return $this;
    }

    public function having($having)
    {
        $this->options['having'] = $having;
        return $this;
    }

    public function order($order)
    {
        $this->options['order'] = $order;
        return $this;
    }

    /**
     * 组装查询语句
     * 
     * @return string
     */
    protected function selectSql()
    {
        $sql = $this->parseSql($this->selectSql, $this->options);
        $this->options = array();
        return $sql;
    }

    /**
     * 替换SQL语句中表达式
     * 
     * @access public
     * @param array $options
     *            表达式
     * @return string
     */
    protected function parseSql($sql, $options = array())
    {
        $sql = str_replace(array(
            '%TABLE%',
            '%DISTINCT%',
            '%FIELD%',
            '%JOIN%',
            '%WHERE%',
            '%GROUP%',
            '%HAVING%',
            '%ORDER%',
            '%LIMIT%'
        ), array(
            $options['table'],
            $this->db->parseDistinct(isset($options['distinct']) ? $options['distinct'] : false),
            ! empty($options['field']) ? $options['field'] : '*',
            $this->db->parseJoin(! empty($options['join']) ? $options['join'] : ''),
            $this->db->parseWhere(! empty($options['where']) ? $options['where'] : ''),
            $this->db->parseGroup(! empty($options['group']) ? $options['group'] : ''),
            $this->db->parseHaving(! empty($options['having']) ? $options['having'] : ''),
            $this->db->parseOrder(! empty($options['order']) ? $options['order'] : ''),
            $this->db->parseLimit(! empty($options['limit']) ? $options['limit'] : '')
        ), $sql);
        return $sql;
    }

    public function count($fields = '*')
    {
        $sql = "SELECT count(" . $fields . ") as num FROM " . $this->options['table'];
        $data = $this->db()->query($sql);
        return $data[0]['num'];
    }

    /**
     * 更新操作
     * @param array $data
     * @return int
     */
    public function update(array $data):int
    {
        if (is_array($data)) {
            $field = '';
            foreach ($data as $key => $vo) {
                $field .= $key . "='" . $this->escapeString($vo) . "',";
            }
            $field = substr($field, 0, - 1);
            $where = $this->db->parseWhere(! empty($this->options['where']) ? $this->options['where'] : '');
            $sql = "UPDATE " . $this->options['table'] . " SET " . $field ." ".$where;
            return $this->db()->execute($sql);
        }
    }
}

