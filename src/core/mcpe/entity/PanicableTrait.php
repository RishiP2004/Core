<?php

namespace core\mcpe\entity;

trait PanicableTrait {
	protected $panicTime = 100;

	protected $inPanic = false;

	public function entityBaseTick(int $tickDiff = 1) : bool {
		if($this->panicTime -= $tickDiff <= 0) {
			$this->setPanic(false);
		}
		return parent::entityBaseTick($tickDiff);
	}

	public function setPanic(bool $panic = true) : void {
		$this->setSprinting($panic);

		$this->inPanic = $panic;

		if($panic) {
			$this->moveTime = 0;
		}
	}

	public function isInPanic() : bool {
		return $this->inPanic;
	}
}