<?php
declare(strict_types=1);

namespace BoardGame;

interface WinningTokenResolver
{
    /**
     * @return int Winning token position.
     */
    public function resolve(): int;
}
