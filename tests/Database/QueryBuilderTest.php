<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Helios\Database\QueryBuilder;

final class QueryBuilderTest extends TestCase
{
    public function testSelectQuery()
    {
        // Mode must be select
        $qb = QueryBuilder::select(["id", "name", "email"])->from("users");
        $this->assertSame("select", $qb->getMode());

        // Select statement should be comma delimited
        $this->assertSame("SELECT id, name, email FROM `users`", $qb->getQuery());

        // No select selects *
        $qb = QueryBuilder::select()->from("users");
        $this->assertSame("SELECT * FROM `users`", $qb->getQuery());

        // Where clause
        $qb = QueryBuilder::select(["id", "name"])
            ->from("users")
            ->where(["email = ?"], "zuck@facebook.com");
        $this->assertSame("SELECT id, name FROM `users` WHERE email = ?", $qb->getQuery());
        $this->assertSame(["zuck@facebook.com"], $qb->getQueryParams());

        // Group By
        $qb = QueryBuilder::select(["count(*), country"])
            ->from("users")
            ->where(["id > ?"], 5)
            ->groupBy(["country"]);
        $this->assertSame("SELECT count(*), country FROM `users` WHERE id > ? GROUP BY country", $qb->getQuery());
        $this->assertSame([5], $qb->getQueryParams());

        // Having
        $qb = QueryBuilder::select(["count(*) as count, country"])
            ->from("users")
            ->where(["id > ?"], 5)
            ->groupBy(["country"])
            ->having(["count > ?"], 10);
        $this->assertSame("SELECT count(*) as count, country FROM `users` WHERE id > ? GROUP BY country HAVING count > ?", $qb->getQuery());
        $this->assertSame([5, 10], $qb->getQueryParams());

        // Order by
        $qb = QueryBuilder::select()->from("users")->orderBy(["id DESC"]);
        $this->assertSame("SELECT * FROM `users` ORDER BY id DESC", $qb->getQuery());

        // Limit
        $qb = QueryBuilder::select()->from("users")->orderBy(["id DESC"])->limit(1);
        $this->assertSame("SELECT * FROM `users` ORDER BY id DESC LIMIT 1", $qb->getQuery());

        // Limit & Offset
        $qb = QueryBuilder::select()->from("users")->orderBy(["id DESC"])->limit(10)->offset(10);
        $this->assertSame("SELECT * FROM `users` ORDER BY id DESC LIMIT 10, 10", $qb->getQuery());
    }

    public function testInsertQuery()
    {
        $qb = QueryBuilder::insert(["name" => "Linus", "email" => "linus@ltt.com"])->into("users");
        $this->assertSame("insert", $qb->getMode());

        $this->assertSame("INSERT INTO `users` (name, email) VALUES (?,?)", $qb->getQuery());
    }

    public function testUpdateQuery()
    {
        $qb = QueryBuilder::update(["enabled" => 1])->table("users")->where(["email = ?"], "zuck@facebook.com");
        $this->assertSame("update", $qb->getMode());

        $this->assertSame("UPDATE `users` SET enabled = ? WHERE email = ?", $qb->getQuery());
    }

    public function testDeleteQuery()
    {
        $qb = QueryBuilder::delete(["enabled" => 1])->from("users")->where(["id = ?"], 99);
        $this->assertSame("delete", $qb->getMode());

        $this->assertSame("DELETE FROM `users` WHERE id = ?", $qb->getQuery());
    }
}
