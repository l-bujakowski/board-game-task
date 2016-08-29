<?php
declare(strict_types=1);

namespace BoardGame;

use DateInterval;

interface Timer
{
    public function register(TimerObserver $observer);

    public function tic();

    public function setFor(DateInterval $time);

    public function stop();
}
