<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use App\Models\{User, UserType};

final class SqlTest extends TestCase
{
    public function testSelectQuery(): void
    {
        $sql = UserType::get()
            ->select(["id", "name"])
            ->where(["id > ?"], 1);

        $this->assertSame("SELECT id, name FROM `user_types` WHERE id > ?", $sql->getQuery());
        $this->assertSame([1], $sql->getQueryParams());

        $sql = User::get()
            ->select(["id", "name", "email"])
            ->where(["name LIKE ?"], "Alex%")
            ->orderBy("name")
            ->ascending(false);

        $this->assertSame("SELECT id, name, email FROM `users` WHERE name LIKE ? ORDER BY name DESC", $sql->getQuery());
        $this->assertSame(["Alex%"], $sql->getQueryParams());

        $sql = User::get()
            ->select()
            ->where(["permission_level > ?"], 3);

        $this->assertSame("SELECT * FROM `users` WHERE permission_level > ?", $sql->getQuery());
        $this->assertSame([3], $sql->getQueryParams());
    }

    public function testInsertQuery(): void
    {
        $sql = UserType::get()->insert([
            "name" => "testing",
            "permission_level" => 12
        ]);

        $this->assertSame("INSERT INTO `user_types` (name, permission_level) VALUES (?,?)", $sql->getQuery());
        $this->assertSame(["testing", 12], $sql->getQueryParams());
    }

    public function testUpdateQuery(): void
    {
        $sql = User::get(1)->update([
            "name" => "Claudio Sanchez",
            "email" => "claudio@live.com"
        ]);

        // Note: the key is mixed, so the param for the primary key is a string
        $this->assertSame("UPDATE `users` SET name = ?, email = ? WHERE id = ?", $sql->getQuery());
        $this->assertSame(["Claudio Sanchez", "claudio@live.com", '1'], $sql->getQueryParams());
    }

    public function testDeleteQuery(): void
    {
        $sql = UserType::get(2)->delete();

        $this->assertSame("DELETE FROM `user_types` WHERE id = ?", $sql->getQuery());
        $this->assertSame(['2'], $sql->getQueryParams());

        // You must have an ID to delete
        $sql = UserType::get()->delete();

        $this->assertSame("DELETE FROM `user_types`", $sql->getQuery());
    }
}
