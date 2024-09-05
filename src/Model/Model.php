<?php

namespace Helios\Model;

use PDO;
use Exception;
use Helios\Database\QueryBuilder;

class Model implements IModel
{
    protected array $columns = [];
    protected string $key_column = "id";

    private array $attributes = [];

    public function __construct(
        private string $table_name,
        private mixed $key = null
    ) {
        if (empty($this->columns)) {
            // if columns aren't set, then use all the enity attributes
            $this->columns = $this->getColumns();
        }
        if (!is_null($key)) {
            if (!$this->loadAttributes($key)) {
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
     * Get the model's primary key column
     */
    public function getKeyColumn(): string
    {
        return $this->key_column;
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
    public function getAttributes(): array
    {
        return $this->attributes;
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
    public function loadAttributes(mixed $key): bool
    {
        $result = QueryBuilder::select($this->columns)
            ->from($this->table_name)
            ->where(["$this->key_column = ?"], $key)
            ->execute()
            ->fetch();
        if ($result) {
            $this->attributes = (array) $result;
        }
        return !empty($this->attributes);
    }

    /**
     * Model hydration
     */
    public function hydrate()
    {
        $this->loadAttributes($this->getKey());
    }

    /**
     * Model hydration (alias)
     */
    public function refresh(): void
    {
        $this->hydrate();
    }

    /**
     * Does the model attribute exist
     */
    public function has(string $attribute)
    {
        return isset($this->attributes[$attribute]);
    }

    /**
     * Get all models from db
     */
    public static function all()
    {
        $class = get_called_class();
        $model = new $class();
        $results = QueryBuilder::select($model->columns)
            ->from($model->table_name)
            ->execute()
            ->fetchAll();
        $key_column = $model->key_column;
        return array_map(fn($result) => $model->find($result->$key_column), $results);
    }

    /**
     * Get model (no key required)
     */
    public static function get(): Model
    {
        $class = get_called_class();
        return new $class;
    }

    /**
     * Find a model from the db by primary key
     */
    public static function find(string|int $key): Model|bool
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
    public static function findOrFail(string|int $key): Model
    {
        $class = get_called_class();
        $model = new $class($key);
        return $model;
    }

    /**
     * Find a model from the db by attribute
     */
    public static function findByAttribute(string $attribute, mixed $value): Model|bool
    {
        $class = get_called_class();
        $model = new $class();
        $result = QueryBuilder::select($model->columns)
            ->from($model->table_name)
            ->where(["$attribute = ?"], $value)
            ->execute()
            ->fetch();
        if ($result) {
            $key_column = $model->key_column;
            return new $model($result->$key_column);
        }
        return false;
    }

    /**
     * Create a new model (static)
     */
    public static function new(array $data): Model
    {
        $class = get_called_class();
        $model = new $class();

        $result = QueryBuilder::insert($data)
            ->into($model->table_name)
            ->params(array_values($data))
            ->execute();

        if ($result) {
            $key = db()->lastInsertId();
            return new $class($key);
        }
    }

    /**
     * Search for models in the db
     */
    public static function search(array $columns): Querybuilder
    {
        $class = get_called_class();
        $model = new $class();
        return QueryBuilder::select($columns)
            ->from($model->table_name);
    }

    /**
     * Save model to db
     */
    public function save(array $attributes): bool
    {
        $payload = !empty($attributes) ? $attributes : $this->getAttributes();
        $params = [...$attributes, $this->key_column => $this->key];
        $result = QueryBuilder::update($payload)
            ->table($this->table_name)
            ->where(["$this->key_column = ?"], $this->key)
            ->params(array_values($params))
            ->execute();
        if ($result) {
            $this->hydrate();
            return true;
        }
        return false;
    }

    /**
     * Delete model from db
     */
    public function destroy(): bool
    {
        $result = QueryBuilder::delete()
            ->from($this->table_name)
            ->where(["$this->key_column = ?"], $this->key)
            ->execute();
        if ($result) {
            $this->hydrate();
            return true;
        }
        return false;
    }

    public function __isset(mixed $name): bool
    {
        return isset($this->attributes[$name]);
    }

    public function __set(mixed $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function __get(mixed $name): mixed
    {
        return $this->attributes[$name];
    }
}
