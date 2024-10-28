<?php

namespace Helios\Utils;

define('INTEGER_LENGTH', 64);

class Bitmask
{
    private string $binary;

    public function __construct(private int $decimal)
    {
        $this->binary = str_pad(decbin($decimal), INTEGER_LENGTH, '0', STR_PAD_LEFT);
    }

    public function getDecimal(): string
    {
        return $this->binary;
    }

    public function getBinary(): string
    {
        return $this->binary;
    }

    public function getState(int $position): int
    {
        // Bitwise AND checks if bit is set
        return ($this->decimal & (1 << $position)) != 0 ? 1 : 0;
    }

    public function toggleState(int $position): void
    {
        // XOR assignment, flips bit at position
        $this->decimal ^= (1 << $position);
    }
}
