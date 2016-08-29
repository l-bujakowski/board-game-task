<?php
namespace spec\BoardGame;

use BoardGame\Game;
use BoardGame\GameAlreadyEndedException;
use BoardGame\Timer;
use BoardGame\TimerObserver;
use BoardGame\TokenAlreadyGuessedException;
use BoardGame\WinningTokenResolver;
use DateInterval;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use UnexpectedValueException;

class GameSpec extends ObjectBehavior
{
    function let(WinningTokenResolver $winningTokenResolver, Timer $timer)
    {
        $winningTokenResolver->resolve()->willReturn(1);
        $this->beConstructedThrough('startNew', [$winningTokenResolver, $timer]);
    }

    function it_can_be_started()
    {
        $this->state()->shouldBe(Game::STATE_CONTINUES);
    }

    function it_is_won_after_hit_on_first_guess(WinningTokenResolver $winningTokenResolver)
    {
        $winningTokenResolver->resolve()->willReturn(5);
        $this->guess(5);
        $this->state()->shouldBe(Game::STATE_WON);
    }

    function it_continues_after_miss(WinningTokenResolver $winningTokenResolver)
    {
        $winningTokenResolver->resolve()->willReturn(5);
        $this->guess(3);
        $this->state()->shouldBe(Game::STATE_CONTINUES);
    }

    function it_is_won_after_hit_on_subsequent_guess(WinningTokenResolver $winningTokenResolver)
    {
        $winningTokenResolver->resolve()->willReturn(5);
        $this->guessSequence([2, 3, 11, 5]);
        $this->state()->shouldBe(Game::STATE_WON);
    }

    function it_is_lost_after_five_misses(WinningTokenResolver $winningTokenResolver)
    {
        $winningTokenResolver->resolve()->willReturn(10);
        $this->guessSequence([17, 3, 6, 18, 11]);
        $this->state()->shouldBe(Game::STATE_LOST);
    }

    function it_is_won_after_hit_on_fifth_guess(WinningTokenResolver $winningTokenResolver)
    {
        $winningTokenResolver->resolve()->willReturn(5);
        $this->guessSequence([2, 3, 11, 4, 5]);
        $this->state()->shouldBe(Game::STATE_WON);
    }

    function it_sets_timer_for_60_seconds(Timer $timer)
    {
        $this->shouldHaveType(TimerObserver::class);
        $timer->setFor(Argument::allOf(
            Argument::type(DateInterval::class),
            Argument::that(function($value) {
                return $value->s === Game::TIME_LIMIT;
            })
        ))->shouldHaveBeenCalled();
    }

    function it_is_a_timer_observer(Timer $timer)
    {
        $this->shouldHaveType(TimerObserver::class);
        $timer->register($this)->shouldHaveBeenCalled();
    }

    function it_stops_timer_when_won(WinningTokenResolver $winningTokenResolver, Timer $timer)
    {
        $this->setupWonGame($winningTokenResolver);
        $timer->stop()->shouldHaveBeenCalled();
    }

    function it_stops_timer_when_lost(WinningTokenResolver $winningTokenResolver, Timer $timer)
    {
        $this->setupLostGame($winningTokenResolver);
        $timer->stop()->shouldHaveBeenCalled();
    }

    function it_ends_on_timer_timeout()
    {
        $this->timeout();
        $this->state()->shouldReturn(Game::STATE_TIMEOUT);
    }

    function it_throws_exception_when_trying_to_guess_already_guessed_token()
    {
        $this->guess(3);
        $this->shouldThrow(TokenAlreadyGuessedException::class)->during('guess', [3]);
    }

    function it_throws_exception_when_trying_to_guess_but_game_is_already_won(
        WinningTokenResolver $winningTokenResolver
    ) {
        $this->setupWonGame($winningTokenResolver);
        $this->shouldThrow(GameAlreadyEndedException::class)->during('guess', [18]);
    }

    function it_throws_exception_when_trying_to_guess_but_game_is_already_lost(
        WinningTokenResolver $winningTokenResolver
    ) {
        $this->setupLostGame($winningTokenResolver);
        $this->shouldThrow(GameAlreadyEndedException::class)->during('guess', [18]);
    }

    function it_throws_exception_when_trying_to_guess_but_game_has_ended_due_to_timeout() {
        $this->timeout();
        $this->shouldThrow(GameAlreadyEndedException::class)->during('guess', [18]);
    }

    function it_throws_exception_when_trying_to_guess_token_out_of_board_range()
    {
        $this->shouldThrow(UnexpectedValueException::class)->during('guess', [-1]);
        $this->shouldThrow(UnexpectedValueException::class)->during('guess', [0]);
        $this->shouldThrow(UnexpectedValueException::class)->during('guess', [Game::BOARD_TOKENS + 1]);
    }

    private function guessSequence(array $guesses)
    {
        foreach ($guesses as $guess) {
            $this->guess($guess);
        }
    }

    private function setupWonGame(WinningTokenResolver $winningTokenResolver)
    {
        $winningTokenResolver->resolve()->willReturn(5);
        $this->guess(5);
    }

    private function setupLostGame(WinningTokenResolver $winningTokenResolver)
    {
        $winningTokenResolver->resolve()->willReturn(5);
        $this->guessSequence([17, 3, 6, 18, 11]);
    }
}
