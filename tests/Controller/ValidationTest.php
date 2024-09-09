<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Helios\Web\Controller;

final class ValidationTest extends TestCase
{
    private Controller $controller;

    protected function setUp(): void
    {
        $this->controller = new Controller();
    }

    public function testRequired(): void
    {
        $this->assertTrue($this->controller->validate('name', 'required', 'John'));
        $this->assertFalse($this->controller->validate('name', 'required', ''));
        $this->assertFalse($this->controller->validate('name', 'required', null));
    }

    public function testEmail(): void
    {
        $this->assertTrue($this->controller->validate('email', 'email', 'test@example.com'));
        $this->assertFalse($this->controller->validate('email', 'email', 'invalid-email'));
    }

    public function testMin(): void
    {
        $this->assertTrue($this->controller->validate('age', 'min', '18', '18'));
        $this->assertFalse($this->controller->validate('age', 'min', '16', '18'));
    }

    public function testMax(): void
    {
        $this->assertTrue($this->controller->validate('age', 'max', '16', '18'));
        $this->assertFalse($this->controller->validate('age', 'max', '19', '18'));
    }

    public function testMinLength(): void
    {
        $this->assertTrue($this->controller->validate('password', 'min_length', '123456', '6'));
        $this->assertFalse($this->controller->validate('password', 'min_length', '123', '6'));
    }

    public function testMaxLength(): void
    {
        $this->assertTrue($this->controller->validate('password', 'max_length', '123456', '6'));
        $this->assertFalse($this->controller->validate('password', 'max_length', '1234567', '6'));
    }

    public function testAlphaNum(): void
    {
        $this->assertTrue($this->controller->validate('username', 'alpha_num', 'user123'));
        $this->assertFalse($this->controller->validate('username', 'alpha_num', 'user_123!'));
    }

    public function testPalindrome(): void
    {
        $this->assertTrue($this->controller->validate('word', 'palindrome', 'madam'));
        $this->assertFalse($this->controller->validate('word', 'palindrome', 'hello'));
    }

    public function testDivisibleBy(): void
    {
        $this->assertTrue($this->controller->validate('number', 'divisible_by', '10', '2'));
        $this->assertFalse($this->controller->validate('number', 'divisible_by', '10', '3'));
    }

    public function testJson(): void
    {
        $this->assertTrue($this->controller->validate('data', 'json', '{"key":"value"}'));
        $this->assertFalse($this->controller->validate('data', 'json', '{key:value}'));
    }

    public function testRegex(): void
    {
        $this->assertTrue($this->controller->validate('username', 'regex', 'abc123', '/^[a-z0-9]+$/'));
        $this->assertFalse($this->controller->validate('username', 'regex', 'abc@123', '/^[a-z0-9]+$/'));
    }

    public function testGreaterThan(): void
    {
        $this->assertTrue($this->controller->validate('age', 'greater_than', '20', '18'));
        $this->assertFalse($this->controller->validate('age', 'greater_than', '15', '18'));
    }

    public function testBoolean(): void
    {
        $this->assertTrue($this->controller->validate('flag', 'boolean', 'true'));
        $this->assertFalse($this->controller->validate('flag', 'boolean', 'not_boolean'));
    }
}
