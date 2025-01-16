<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Models\User;

final class ModelTest extends TestCase
{
    public function testQueryWhere()
    {
        $sql = User::where("id", "1")->sql();

        $this->assertSame("SELECT * FROM `users` WHERE (id = ?) LIMIT 1", $sql['sql']);
        $this->assertSame("1", $sql['params'][0]);
    }

    public function testQueryAndWhere()
    {
        $sql = User::where("id", "1")->andWhere("name", "admin")->sql();

        $this->assertSame("SELECT * FROM `users` WHERE (id = ?) AND (name = ?) LIMIT 1", $sql['sql']);
        $this->assertSame("1", $sql['params'][0]);
        $this->assertSame("admin", $sql['params'][1]);
    }
}
