<?php
declare(strict_types=1);

namespace BoardGame;

interface TimerObserver
{
    public function timeout();
}
