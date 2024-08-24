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
            ->where(["name LIKE ?"], "Alex%");

        $this->assertSame("SELECT id, name, email FROM `users` WHERE name LIKE ?", $sql->getQuery());
        $this->assertSame(["Alex%"], $sql->getQueryParams());
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
        $sql = UserType::get(1)->update([
            "name" => "blue",
            "permission_level" => 15
        ]);

        // Note: the key is mixed, so the param for the primary key is a string
        $this->assertSame("UPDATE `user_types` SET name = ?, permission_level = ? WHERE id = ?", $sql->getQuery());
        $this->assertSame(["blue", 15, '1'], $sql->getQueryParams());
    }

    public function testDeleteQuery(): void
    {
        $sql = UserType::get(2)->delete();

        $this->assertSame("DELETE FROM `user_types` WHERE id = ?", $sql->getQuery());
        $this->assertSame(['2'], $sql->getQueryParams());
    }
}
