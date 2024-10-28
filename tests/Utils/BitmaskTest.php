<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Helios\Utils\Bitmask;

final class BitmaskTest extends TestCase
{
    public function testGetBinary(): void
    {

        $bitmask = new Bitmask(9);
        $bin = $bitmask->getBinary();
        $this->assertSame("0000000000000000000000000000000000000000000000000000000000001001", $bin);

        $bitmask = new Bitmask(6487324);
        $bin = $bitmask->getBinary();
        $this->assertSame("0000000000000000000000000000000000000000011000101111110100011100", $bin);

        $bitmask = new Bitmask(1);
        $bin = $bitmask->getBinary();
        $this->assertSame("0000000000000000000000000000000000000000000000000000000000000001", $bin);
    }

    public function testGetState(): void
    {
        $bitmask = new Bitmask(9);
        $state = $bitmask->getState(0);
        $this->assertSame(1, $state);

        $state = $bitmask->getState(3);
        $this->assertSame(1, $state);

        $state = $bitmask->getState(4);
        $this->assertSame(0, $state);

        $state = $bitmask->getState(63);
        $this->assertSame(0, $state);

        $bitmask = new Bitmask(6487324);
        $state = $bitmask->getState(22);
        $this->assertSame(1, $state);

        $state = $bitmask->getState(11);
        $this->assertSame(1, $state);

        $state = $bitmask->getState(9);
        $this->assertSame(0, $state);
    }

    public function testToggleState(): void
    {
        $bitmask = new Bitmask(6487324);

        $bitmask->toggleState(22);
        $state = $bitmask->getState(22);
        $this->assertSame(0, $state);

        $bitmask->toggleState(11);
        $state = $bitmask->getState(11);
        $this->assertSame(0, $state);

        $bitmask->toggleState(9);
        $state = $bitmask->getState(9);
        $this->assertSame(1, $state);

        $this->assertNotSame(6487324, $bitmask->getDecimal());
    }
}

