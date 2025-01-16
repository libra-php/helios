<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Models\User;

final class ModelTest extends TestCase
{
    public function testQueryWhere(): void
    {
        $sql = User::where("id", "1")->sql();

        $this->assertSame("SELECT * FROM `users` WHERE (id = ?) LIMIT 1", $sql['sql']);
        $this->assertSame("1", $sql['params'][0]);
    }

    public function testQueryAndWhere(): void
    {
        $sql = User::where("id", "1")->andWhere("name", "admin")->sql();

        $this->assertSame("SELECT * FROM `users` WHERE (id = ?) AND (name = ?) LIMIT 1", $sql['sql']);
        $this->assertSame("1", $sql['params'][0]);
        $this->assertSame("admin", $sql['params'][1]);
    }

    public function testQueryOrWhere(): void
    {
        $sql = User::where("id", "1")->orWhere("name", "admin")->sql();

        $this->assertSame("SELECT * FROM `users` WHERE (id = ?) OR (name = ?) LIMIT 1", $sql['sql']);
        $this->assertSame("1", $sql['params'][0]);
        $this->assertSame("admin", $sql['params'][1]);
    }

    public function testQueryOrderBy(): void
    {
        $sql = User::where("id", "1")->orderBy("name", "DESC")->sql();

        $this->assertSame("SELECT * FROM `users` WHERE (id = ?) ORDER BY name DESC LIMIT 1", $sql['sql']);
        $this->assertSame("1", $sql['params'][0]);
    }
}
