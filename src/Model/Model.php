<?php

namespace Helios\Model;

use Exception;
use Helios\Database\QueryBuilder;
use PDO;

/**
 * @class Model
 */
class Model implements IModel
{
    protected string $primaryKey = "id";
    protected bool $autoIncrement = true;
    protected array $columns = ["*"];
    private array $attributes = [];
    protected QueryBuilder $qb;
    private array $where = [];
    private array $orWhere = [];
    private array $orderBy = [];
    private array $params = [];
    private array $validOperators = [
        "=",
        "!=",
        ">",
        ">=",
        "<",
        "<=",
        "is",
        "not",
        "like",
    ];

    public function __construct(private string $table, private ?string $id = null)
    {
        // Initialize the query builder
        $this->qb = new QueryBuilder;
        // Set id
        if (!is_null($id)) {
            $this->id = $id;
            $this->loadAttributes($id);
            if (empty($this->attributes)) {
                throw new Exception("model not found");
            }
        }
    }

    /**
     * Load model attributes from db
     */
    private function loadAttributes(string $id)
    {
        $key = $this->primaryKey;
        $result = $this->qb->select($this->columns)
            ->from($this->table)
            ->where(["$key = ?"], $id)
            ->execute()
            ->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $this->attributes = $result;
        }
    }

    /**
     * Returns all models from db
     */
    public static function all(): array
    {
        $class = get_called_class();
        $model = new $class;
        $results = $model->qb
            ->select($model->columns)
            ->from($model->table)
            ->execute()
            ->fetchAll();
        $key = $model->primaryKey;
        return array_map(fn($result) => $model->find($result->$key), $results);
    }

    /**
     * Create a new model
     */
    public static function create(array $data): static|Model|bool|null
    {
        $class = get_called_class();
        $model = new $class;
        $result = $model->qb
            ->insert($data)
            ->into($model->table)
            ->params(array_values($data))
            ->execute();

        if ($result && $model->autoIncrement) {
            $id = db()->lastInsertId();
            return $model->find($id);
        } else if ($result && !$model->autoIncrement) {
            return true;
        }
        return null;
    }

    /**
     * Destroy model(s)
     */
    public static function destroy(string|array $id): bool
    {
        $success = true;
        if (is_string($id)) $id = [$id];

        foreach ($id as $model_id) {
            $class = get_called_class();
            $model = new $class;
            $key = $model->primaryKey;
            $result = $model->qb
                ->delete()
                ->from($model->table)
                ->where(["$key = ?"], $model_id)
                ->execute();
            $success &= (bool) $result;
        }
        return $success;
    }

    /**
     * Delete the current model
     */
    public function delete(): bool
    {
        $key = $this->primaryKey;
        $result = $this->qb
            ->delete()
            ->from($this->table)
            ->where(["$key = ?"], $this->id)
            ->execute();
        return (bool) $result;
    }

    /**
     * Refresh model attributes
     */
    public function refresh(): Model|static
    {
        $this->loadAttributes($this->id);
        return $this;
    }

    /**
     * Return a single model by id
     */
    public static function find(string $id): null|Model|static
    {
        $class = get_called_class();
        $model = new $class;
        try {
            $result = new $model($id);
            return $result;
        } catch (Exception $ex) {
            return null;
        }
    }

    /**
     * Return a single model by id or throws model not found
     */
    public static function findOrFail(string $id): Model|static
    {
        $class = get_called_class();
        $model = new $class;
        return new $model($id);
    }

    /**
     * Fetch the first model in the result set
     */
    public function first(): ?Model
    {
        $results = $this->qb
            ->select($this->columns)
            ->from($this->table)
            ->where($this->where)
            ->orWhere($this->orWhere)
            ->orderBy($this->orderBy)
            ->params($this->params)
            ->execute()
            ->fetchAll();
        $key = $this->primaryKey;
        if ($results) {
            $result = $results[0];
            return self::find($result->$key);
        }
        return null;
    }

    /**
     * Fetch the last model in the result set
     */
    public function last(): ?Model
    {
        $results = $this->qb
            ->select($this->columns)
            ->from($this->table)
            ->where($this->where)
            ->orWhere($this->orWhere)
            ->orderBy($this->orderBy)
            ->params($this->params)
            ->execute()
            ->fetchAll();
        $key = $this->primaryKey;
        if ($results) {
            $result = end($results);
            return self::find($result->$key);
        }
        return null;
    }

    /**
     * Expose the sql query and params
     */
    public function sql(int $limit = 1): array
    {
        $qb = $this->qb
            ->select($this->columns)
            ->from($this->table)
            ->where($this->where)
            ->orWhere($this->orWhere)
            ->orderBy($this->orderBy)
            ->limit($limit)
            ->params($this->params);
        return ["sql" => $qb->getQuery(), "params" => $qb->getQueryParams()];
    }

    /**
     * Fetch the model result set
     */
    public function get(int $limit = 0, bool $lazy = true): null|array|static|Model
    {
        $results = $this->qb
            ->select($this->columns)
            ->from($this->table)
            ->where($this->where)
            ->orWhere($this->orWhere)
            ->orderBy($this->orderBy)
            ->limit($limit)
            ->params($this->params)
            ->execute()
            ->fetchAll();
        $key = $this->primaryKey;
        if ($results && $lazy && count($results) === 1) {
            $result = $results[0];
            return self::find($result->$key);
        }
        return $results
            ? array_map(fn($result) => $this->find($result->$key), $results)
            : null;
    }

    /**
     * Fetch the count of models in result set
     */
    public function count(): ?int
    {
        return $this->qb
            ->select($this->columns)
            ->from($this->table)
            ->where($this->where)
            ->orWhere($this->orWhere)
            ->params($this->params)
            ->execute()
            ->rowCount();
    }

    /**
     * Save the current model
     */
    public function save(): Model|static
    {
        $key = $this->primaryKey;
        $params = [...array_values($this->attributes), $this->id];
        $result = $this->qb
            ->update($this->attributes)
            ->table($this->table)
            ->where(["$key = ?"])
            ->params($params)
            ->execute();
        if ($result) {
            $this->loadAttributes($this->id);
        }
        return $this;
    }

    /**
     * Update the current model with provided data
     */
    public function update(array $data): Model|static
    {
        $key = $this->primaryKey;
        $params = [...array_values($data), $this->id];
        $result = $this->qb
            ->update($data)
            ->table($this->table)
            ->where(["$key = ?"])
            ->params($params)
            ->execute();
        if ($result) {
            $this->loadAttributes($this->id);
        }
        return $this;
    }

    /**
     * Add to the model where clause (separated by AND)
     * @static
     */
    public static function where(string $column, string $operator = '=', ?string $value = null): static
    {
        $class = get_called_class();
        $model = new $class;

        // Default operator is =
        if (!in_array(strtolower($operator), $model->validOperators)) {
            $value = $operator;
            $operator = '=';
        }
        // Add the where clause and params
        $model->where[] = "($column $operator ?)";
        $model->params[] = $value;
        return $model;
    }

    /**
     * Add to the model where clause (separated by OR)
     */
    public function andWhere(string $column, string $operator = '=', ?string $value = null): Model
    {
        // Default operator is =
        if (!in_array(strtolower($operator), $this->validOperators)) {
            $value = $operator;
            $operator = '=';
        }
        // Add the where clause and params
        $this->where[] = "($column $operator ?)";
        $this->params[] = $value;
        return $this;
    }

    /**
     * Add to the model where clause (separated by OR)
     */
    public function orWhere(string $column, string $operator = '=', ?string $value = null): Model
    {
        // Default operator is =
        if (!in_array(strtolower($operator), $this->validOperators)) {
            $value = $operator;
            $operator = '=';
        }
        // Add the where clause and params
        $this->orWhere[] = "($column $operator ?)";
        $this->params[] = $value;
        return $this;
    }

    /**
     * Add to the model order by clause
     */
    public function orderBy(string $column, string $direction = "ASC"): Model
    {
        $this->orderBy[] = "$column $direction";
        return $this;
    }

    public function __set($name, $value)
    {
        return $this->attributes[$name] = $value;
    }

    public function __get($name)
    {
        return $this->attributes[$name];
    }
}
