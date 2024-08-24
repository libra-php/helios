<?php

namespace Helios\Model;

use Error;
use Exception;
use PDO;
use PDOStatement;

class Model implements IModel
{
    protected array $columns = [];
    protected string $key_column = "id";

    private array $parameters = [];

    protected array $query = [
        'mode' => 'select',
        'select' => '',
        'update' => '',
        'insert' => '',
        'delete' => '',
        'where' => '',
        'having' => '',
        'group_by' => '',
        'order_by' => '',
        'sort' => 'ASC',
        'offset' => 0,
        'limit' => 0,
        'params' => []
    ];

    public function __construct(
        private string $table_name,
        private mixed $key = null
    ) {
        if (empty($this->columns)) {
            // if columns aren't set, then use all the enity attributes
            $this->columns = $this->getColumns();
        }
        if (!is_null($key)) {
            if (!$this->load($key)) {
                throw new Exception("model not found");
            }
        }
    }

    /**
     * Get the model's primary key
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Get the model's table name
     */
    public function getTableName(): string
    {
        return $this->table_name;
    }

    /**
     * Get the model's parameters
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Get table columns
     */
    public function getColumns(): array
    {
        $columns = db()->run("DESCRIBE $this->table_name");
        return $columns->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Load the model
     */
    public function load(mixed $key): bool
    {
        $result = $this->select($this->columns)
            ->where(["$this->key_column = ?"], $key)
            ->execute()
            ->fetch();
        if ($result) {
            $this->parameters = (array) $result;
        }
        return !empty($this->parameters);
    }

    /**
     * Model hydration
     */
    public function hydrate()
    {
        $this->load($this->getKey());
    }

    /**
     * Model hydration (alias)
     */
    public function refresh(): void
    {
        $this->hydrate();
    }

    /**
     * Get all models from db
     */
    public static function all()
    {
        $class = get_called_class();
        $model = new $class();
        $results = $model->select($model->columns)
            ->execute()
            ->fetchAll();
        $key_column = $model->key_column;
        return array_map(fn($result) => $model->find($result->$key_column), $results);
    }

    /**
     * Find a model from the db by primary key
     */
    public static function find(mixed $key)
    {
        $class = get_called_class();
        try {
            $model = new $class($key);
            return $model;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * Find a model from the db by primary key or fail
     * @throws Exception model not found
     */
    public static function findOrFail(mixed $key)
    {
        $class = get_called_class();
        $model = new $class($key);
        return $model;
    }

    /**
     * Find a model from the db by attribute
     */
    public static function findByAttribute(string $attribute, mixed $key)
    {
        $class = get_called_class();
        $model = new $class();
        $result = $model->select($model->columns)
            ->where(["$attribute = ?"], $key)
            ->execute()
            ->fetch();
        if ($result) {
            $key_column = $model->key_column;
            return new $model($result->$key_column);
        }
    }

    /**
     * Create a new model (static)
     */
    public static function new(array $data): Model
    {
        $class = get_called_class();
        $model = new $class();

        $result = $model->insert($data)->execute();

        if ($result) {
            $id = db()->lastInsertId();
            return new $class($id);
        }
    }

    /**
     * Save model to db
     */
    public function save(): bool
    {
        $result = $this->update($this->getParameters());
        if ($result) {
            $this->hydrate();
            return true;
        }
        return false;
    }

    /**
     * Reset SQL query array
     */
    private function resetQuery(): void
    {
        $this->query = [
            'mode' => 'select',
            'select' => '',
            'update' => '',
            'insert' => '',
            'delete' => '',
            'where' => '',
            'having' => '',
            'group_by' => '',
            'order_by' => '',
            'sort' => '',
            'offset' => 0,
            'limit' => 0,
            'params' => []
        ];
    }

    /**
     * Add query params
     */
    private function addQueryParams(array $replacements): void
    {
        foreach ($replacements as $replacement) {
            $this->query["params"][] = $replacement;
        }
    }

    /**
     * Alias for findOrFail
     */
    public static function get(mixed $key = null): self
    {
        return self::findOrFail($key);
    }

    /**
     * SELECT query
     */
    public function select(array $data = []): self
    {
        $this->resetQuery();
        if (empty($data)) $data = $this->columns;
        $select = implode(", ", array_values($data));
        $this->query["mode"] = "select";
        $this->query["select"] = $select;
        return $this;
    }

    /**
     * INSERT query
     */
    public function insert(array $data = []): self
    {
        $this->resetQuery();
        $insert = implode(", ", array_keys($data));
        $replacements = array_values($data);
        $this->addQueryParams($replacements);
        $this->query["mode"] = "insert";
        $this->query["insert"] = $insert;
        return $this;
    }

    /**
     * UPDATE query
     */
    public function update(array $data = []): self
    {
        $this->resetQuery();
        $columns = array_keys($data);
        $update = array_map(fn($column) => "$column = ?", $columns);
        $replacements = array_values($data);
        $replacements[] = $this->key;
        $this->addQueryParams($replacements);
        $this->query["mode"] = "update";
        $this->query["update"] = implode(", ", $update);
        return $this;
    }

    /**
     * DELETE query
     */
    public function delete(): self
    {
        $this->resetQuery();
        $this->query["mode"] = "delete";
        $this->addQueryParams([$this->key]);
        return $this;
    }

    /**
     * WHERE clause
     */
    public function where(array $data, ...$replacements): self
    {
        $where = implode(" AND ", $data);
        $this->query["where"] = $where;
        $this->addQueryParams($replacements);
        return $this;
    }

    /**
     * GROUP BY clause
     */
    public function groupBy(array $data): self
    {
        $where = implode(" AND ", $data);
        $this->query["group_by"] = $where;
        return $this;
    }

    /**
     * HAVING clause
     */
    public function having(array $data, ...$replacements): self
    {
        $where = implode(" AND ", $data);
        $this->query["having"] = $where;
        $this->addQueryParams($replacements);
        return $this;
    }

    /**
     * ORDER BY clause
     */
    public function orderBy(string $column): self
    {
        $this->query["order_by"] = $column;
        return $this;
    }

    /**
     * SORT clause
     */
    public function sort(bool $asc = true): self
    {
        $this->query["sort"] = $asc ? "ASC" : "DESC";
        return $this;
    }

    /**
     * LIMIT clause
     */
    public function limit(int $limit): self
    {
        $this->query["limit"] = $limit;
        return $this;
    }

    /**
     * OFFSET clause
     */
    public function offset(int $offset): self
    {
        $this->query["offset"] = $offset;
        return $this;
    }

    /**
     * Explicity set query params
     */
    public function params(array $data): self
    {
        $this->query["params"] = $data;
        return $this;
    }

    /**
     * Return query
     * @throws Error unknown query mode
     */
    public function getQuery(): string
    {
        $sql = match ($this->query["mode"]) {
            "select" => $this->buildSelect(),
            "update" => $this->buildUpdate(),
            "insert" => $this->buildInsert(),
            "delete" => $this->buildDelete(),
            default => throw new Error("unknown query mode")
        };
        return trim($sql);
    }

    /**
     * Get query params (? replacements)
     */
    public function getQueryParams(): array
    {
        return $this->query["params"];
    }

    /**
     * Build SELECT query
     */
    private function buildSelect(): string
    {
        $select = $this->query["select"];
        $where = $this->query["where"]
            ? "WHERE " . $this->query["where"]
            : '';
        $group_by = $this->query["group_by"]
            ? "GROUP BY " . $this->query["group_by"]
            : '';
        $having = $this->query["having"]
            ? "HAVING " . $this->query["having"]
            : '';
        $order_by = $this->query["order_by"]
            ? "ORDER BY " . $this->query["order_by"] . ' ' . $this->query["sort"]
            : '';
        $limit = $this->query["limit"]
            ? "LIMIT " . $this->query["offset"] . ', ' . $this->query["limit"]
            : '';

        return sprintf(
            "SELECT %s FROM `%s` %s %s %s %s %s",
            $select,
            $this->getTableName(),
            $where,
            $group_by,
            $having,
            $order_by,
            $limit
        );
    }

    /**
     * Build INSERT query
     */
    private function buildInsert(): string
    {
        $insert = $this->query["insert"];
        $insert_arr = explode(', ', $insert);
        $placeholders = array_fill(0, count($insert_arr), "?");

        return sprintf(
            "INSERT INTO `%s` (%s) VALUES (%s)",
            $this->getTableName(),
            $insert,
            implode(",", $placeholders)
        );
    }

    /**
     * Build UPDATE query
     */
    private function buildUpdate(): string
    {
        $update = $this->query["update"];
        return sprintf(
            "UPDATE `%s` SET %s WHERE %s = ?",
            $this->getTableName(),
            $update,
            $this->key_column,
        );
    }

    /**
     * Build DELETE query
     */
    private function buildDelete(): string
    {
        return sprintf(
            "DELETE FROM `%s` WHERE %s = ?",
            $this->getTableName(),
            $this->key_column,
        );
    }

    /**
     * Execute query
     */
    public function execute(): bool|PDOStatement
    {
        $query = $this->getQuery();
        return db()->run($query, $this->query["params"]);
    }

    public function __isset(mixed $name): bool
    {
        return isset($this->parameters[$name]);
    }

    public function __set(mixed $name, mixed $value): void
    {
        $this->parameters[$name] = $value;
    }

    public function __get(mixed $name): mixed
    {
        return $this->parameters[$name];
    }
}
