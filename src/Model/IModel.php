<?php

namespace Helios\Model;

interface IModel
{
    public function count(): ?int;
    public function delete(): bool;
    public function first(): ?Model;
    public function get(int $limit = 0): null|array|Model;
    public function last(): ?Model;
    public function orWhere(
        string $column,
        string $operator = "=",
        ?string $value = null
    ): Model;
    public function orderBy(string $column, string $direction = "ASC"): Model;
    public function refresh(): Model;
    public function save(): Model;
    public function update(array $data): Model;
    public static function all(): array;
    public static function create(array $data): Model|bool|null;
    public static function destroy(string $id): bool;
    public static function find(string $id): ?Model;
    public static function findOrFail(string $id): Model;
    public static function where(
        string $column,
        string $operator = "=",
        ?string $value = null
    ): Model;
}
