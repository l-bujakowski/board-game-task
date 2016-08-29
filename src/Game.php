<?php
declare(strict_types=1);

namespace BoardGame;

use UnexpectedValueException;

class Game
{
    const STATE_CONTINUES = 'CONTINUES';
    const STATE_LOST = 'LOST';
    const STATE_WON = 'WON';
    const BOARD_TOKENS = 20;
    const MAX_ATTEMPTS = 5;

    /** @var int */
    private $winningToken;

    /** @var string */
    private $state;

    /** @var array */
    private $guesses = [];

    private function __construct(int $winningTokenPosition)
    {
        $this->winningToken = $winningTokenPosition;
        $this->state = static::STATE_CONTINUES;
    }

    public static function startNew(WinningTokenResolver $winningToken): Game
    {
        return new Game($winningTokenPosition = $winningToken->resolve());
    }

    public function guess(int $tokenPosition)
    {
        $this->makeSureGuessIsValid($tokenPosition);
        $this->rememberGuess($tokenPosition);
        $this->updateStateIfWonOrLost($tokenPosition);
    }

    private function makeSureGuessIsValid(int $tokenPosition)
    {
        $this->makeSureGuessIsInRange($tokenPosition);
        $this->makeSureStateAllowsGuessing();
        $this->makeSureTokenIsNotAlreadyGuessed($tokenPosition);
    }

    private function makeSureGuessIsInRange(int $tokenPosition)
    {
        if ($tokenPosition < 1 || $tokenPosition > static::BOARD_TOKENS) {
            throw new UnexpectedValueException();
        }
    }

    private function makeSureStateAllowsGuessing()
    {
        if ($this->state !== static::STATE_CONTINUES) {
            throw new GameAlreadyEndedException();
        }
    }

    private function makeSureTokenIsNotAlreadyGuessed(int $tokenPosition)
    {
        if (in_array($tokenPosition, $this->guesses)) {
            throw new TokenAlreadyGuessedException();
        }
    }

    private function rememberGuess(int $tokenPosition)
    {
        $this->guesses[] = $tokenPosition;
    }

    private function updateStateIfWonOrLost(int $tokenPosition)
    {
        if ($this->hasHit($tokenPosition)) {
            $this->state = static::STATE_WON;
            return;
        }

        if ($this->hasReachedAttemptsLimit()) {
            $this->state = static::STATE_LOST;
        }
    }

    private function hasHit(int $tokenPosition): bool
    {
        return $tokenPosition === $this->winningToken;
    }

    private function hasReachedAttemptsLimit(): bool
    {
        return count($this->guesses) === static::MAX_ATTEMPTS;
    }

    public function state(): string
    {
        return $this->state;
    }
}
