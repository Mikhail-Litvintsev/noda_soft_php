<?php


use DataBase\DB;

/** Базовая модель, только то, что требуется в базовых файлах. Разумеется, требуется расширение функционала
 * и декомпозиция. А по хорошему поставить готовый фреймворк)
 */
abstract class BaseModel
{
    abstract public static function getTableName(): string;
    private string $select = '*';
    /**
     * @var string|null
     */
    private ?string $where = null;
    private ?string $limit = null;

    protected ?\PDO $db;

    public function __construct()
    {
        $this->db = DB::getInstance();
    }

    /**
     * @param array $conditions [поле_в_таблице => значение]
     * @return BaseModel
     */
    public static function find(array $conditions = []): BaseModel
    {
        $model = new static();
        if (empty($conditions)) {
            return new $model;
        }
        return $model->andWhere($conditions);
    }


    /**
     * @param array $conditions Пример ['id' => 1] или ['id', '>', 3]
     * @return $this
     */
    public function andWhere(array $conditions): self
    {
        if (empty($conditions)) {
            return $this;
        }
        $this->where =  (is_null($this->where)) ? 'WHERE ' : ' AND ';
        if (isset($conditions[0], $conditions[1], $conditions[2])) {
            $this->where .= $conditions[0] . ' ' . $conditions[1] . ' ' . $conditions[2];
        } else {
            foreach ($conditions as $key => $value) {
                $this->where .= $key . ' = ' . $value;
            }
        }
        return $this;
    }

    public function select(array $fields = []): self
    {
        if (!empty($fields)) {
            $this->select = implode(', ', $fields);
        }
        return $this;
    }
    public function limit(int $count): self
    {
        $this->limit = "LIMIT $count";
        return $this;
    }

    public function getQuery():string
    {
        $fields = $this->select;
        $table = static::getTableName();
        $query = "SELECT $fields FROM $table";
        if ($this->where) {
            $query .= ' ' . $this->where;
        }
        if ($this->limit) {
            $query .= ' ' . $this->limit;
        }
        return $query;
    }

    /**
     * @return $this|array
     */
    public function one(bool $toArray = false)
    {
        $row = $this->fetch();
        return $this->setOneRow($row, $toArray);
    }
    /**
     * @return $this[]|array[]
     */
    public function all(bool $toArray = false): array
    {
        $rows = $this->fetchAll();
        return $this->setAllRow($rows, $toArray);
    }

    /**
     * @return $this[]|array[]
     */
    private function setAllRow(array $rows, bool $toArray = false): array
    {
        $all = [];
        foreach ($rows as $row) {
            $all[] = $this->setOneRow($row, $toArray);
        }
        return $all;
    }
    /**
     * @return $this|array
     */
    private function setOneRow(array $row, bool $toArray = false)
    {
        $model = clone $this;
        foreach ($row as $key => $value) {
            $model->$key = $value;
        }
        $model->processRowData();
        return ($toArray) ? (array)$model : $model;
    }
    private function fetch(): array
    {
        $this->limit = null;
        $stmt = $this->getExecutedPDOStatement();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    private function fetchAll(): array
    {
        $stmt = $this->getExecutedPDOStatement();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return false|PDOStatement
     */
    private function getExecutedPDOStatement()
    {
        $query = $this->getQuery();
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * @param array $params [поле_в_таблице => значение]
     * @return string|false
     */
    public static function insert(array $params)
    {
        $model = static::find();
        $table = $model::getTableName();
        $keys = '(' . implode(', ', array_keys($params)) . ')';
        foreach ($params as $key => $value) {
            $params[':' . $key] = $value;
            unset($params[$key]);
        }
        $dynamicKeys = '(' . implode(', ', array_keys($params)) . ')';
        $query = "INSERT INTO $table $keys VALUES $dynamicKeys";
        $sth = $model->db->prepare($query);
        $sth->execute($params);

        return $model->db->lastInsertId();
    }
    /** Обработка полученных из базы данных значений */
    public function processRowData()
    {
    }
}