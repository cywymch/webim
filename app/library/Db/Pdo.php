<?php
namespace Db;

class Pdo
{

    protected $db = null;

    protected $statement = null;

    protected $bind = array();

    protected $lastInsID = null;

    protected $numRows = 0;

    protected $config = array(
        'type' => 'pdo',
        'hostname' => '127.0.0.1',
        'database' => '',
        'username' => '',
        'password' => '',
        'hostport' => '3306',
        'prefix' => '',
        'charset' => 'utf8',
        'dsn' => ''
    );

    public function __construct($config = array())
    {
        $this->config = array_merge($this->config, $config);
    }

    protected function parseDsn(array $config)
    {}

    protected function connect(): \PDO
    {
        try {
            $pdo = new \PDO($this->config['dsn'], $this->config['username'], $this->config['password']);
            $pdo->exec("set names utf8;");
            return $pdo;
        } catch (\Exception $e) {
            
            echo $e->getMessage();
        }
    }

    /**
     * 执行单条sql
     * 
     * @param string $sql
     *            sql语句
     * @return array
     */
    public function _query(string $sql): array
    {
        // TODO Auto-generated method stub
        $result = $this->connect()->query($sql);
        $rows = array();
        foreach ($result as $vo) {
            $rows[] = $vo;
        }
        return $rows;
    }

    /**
     * 执行一条 SQL 语句，并返回受影响的行数
     * 
     * @param string $sql            
     * @return int 受影响的行数
     */
    public function exc(string $sql): int
    {
        return $this->connect()->exec($sql) or die($this->connect()->errorInfo());
    }

    /**
     * 执行预处理sql查询
     * 
     * @param string $sql            
     * @return boolean|array
     */
    public function query($sql)
    {
        $this->statement = $this->connect()->prepare($sql);
        if (! empty($this->bind)) {
            foreach ($this->bind as $key => $val) {
                if (is_array($val)) {
                    $this->statement->bindValue($key, $val[0], $val[1]);
                } else {
                    $this->statement->bindValue($key, $val);
                }
            }
            $this->bind = array();
        }
        try {
            $result = $this->statement->execute();
            //echo $this->statement->debugDumpParams();
            if ($result) {
                $result = $this->getResult();
            } else {
                //dump($this->db->errorInfo());
            }
        } catch (\PDOException $e) {
            dump($e->errorInfo);
            return false;
        }
        
        return $result;
    }

    /**
     * 执行预处理sql
     * 
     * @param string $sql
     *            sql语句
     * @return array 返回结果集
     */
    public function execute(string $sql)
    {
        $this->statement = $this->connect()->prepare($sql);
        if (! empty($this->bind)) {
            foreach ($this->bind as $key => $val) {
                if (is_array($val)) {
                    $this->statement->bindValue($key, $val[0], $val[1]);
                } else {
                    $this->statement->bindValue($key, $val);
                }
            }
            $this->bind = array();
        }
        try {
            $result = $this->statement->execute();
            if ($result === true) {
                $this->numRows = $this->statement->rowCount();
                $this->lastInsID =  $this->connect()->lastInsertId();
            } else {
                dump($this->connect()->errorInfo());
            }
        } catch (\PDOException $e) {
            echo $e->errorInfo;
            return false;
        }
        
        return $this->numRows;
    }

    public function bindParam($name, $value)
    {
        $this->bind[':' . $name] = $value;
    }

    /**
     * 获取statement处理结果集
     * 
     * @return array
     */
    protected function getResult()
    {
        $result = array();
        while (($row = $this->statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            $result[] = $row;
        }
        return $result;
    }

    /**
     * where条件分析
     * 
     * @param string $where            
     * @param mixed $parse            
     * @return string
     */
    public function parseWhere($where, $parse = null)
    {
        
        $whereStr = '';
        if (isset($where)&&!empty($where)){
            if (empty($parse)) {
                $parse = ' AND ';
            }
            if (is_string($where)) {
                $whereStr = " WHERE " . $where;
            } else {
                if (is_array($where)) {
                    foreach ($where as $key => $value) {
                        $whereStr .= $key . "= '" . $value . "'  " . $parse . " ";
                    }
                    $whereStr = " WHERE " . substr(rtrim($whereStr), 0, - 3);
                }
            }
        }
        return $whereStr;
    }

    public function parseLimit($limit)
    {
        return ! empty($limit) ? ' LIMIT ' . $limit : ' ';
    }

    /**
     * join分析
     * 
     * @access public
     * @param mixed $join            
     * @return string
     */
    public function parseJoin($join)
    {
        return $join;
    }

    /**
     * order分析
     * 
     * @access public
     * @param mixed $order            
     * @return string
     */
    public function parseOrder($order)
    {
        if (is_array($order)) {
            $array = array();
            foreach ($order as $key => $val) {
                if (is_numeric($key)) {
                    $array[] = $this->parseKey($val);
                } else {
                    $array[] = $this->parseKey($key) . ' ' . $val;
                }
            }
            $order = implode(',', $array);
        }
        return ! empty($order) ? ' ORDER BY ' . $order : '';
    }

    /**
     * group分析
     * 
     * @access public
     * @param mixed $group            
     * @return string
     */
    public function parseGroup($group)
    {
        return ! empty($group) ? ' GROUP BY ' . $group : '';
    }

    /**
     * having分析
     * 
     * @access public
     * @param string $having            
     * @return string
     */
    public function parseHaving($having)
    {
        return ! empty($having) ? ' HAVING ' . $having : '';
    }

    /**
     * comment分析
     * 
     * @access public
     * @param string $comment            
     * @return string
     */
    public function parseComment($comment)
    {
        return ! empty($comment) ? ' /* ' . $comment . ' */' : '';
    }

    /**
     * distinct分析
     * 
     * @access public
     * @param mixed $distinct            
     * @return string
     */
    public function parseDistinct($distinct)
    {
        return ! empty($distinct) ? ' DISTINCT ' : '';
    }

    public function __destruct()
    {
        //$this->statement->closeCursor();
        $this->statement = null;
    }
}

