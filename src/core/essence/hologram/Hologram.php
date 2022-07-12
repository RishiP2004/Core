<?php

declare(strict_types = 1);

namespace core\essence\hologram;

use core\player\CorePlayer;

use pocketmine\world\Position;
use pocketmine\world\particle\FloatingTextParticle;

abstract class Hologram {
    public FloatingTextParticle $particle;

    public function __construct(private string $name = "") {}

    public final function getName() : string {
        return $this->name;
    }

    public abstract function getPosition() : Position;

    public abstract function getText() : string;

    public abstract function getUpdateTime() : ?int;
	//TODO: NOT NEEDED? also maybe not use oop here..
	public abstract function spawnTo(?CorePlayer $player = null) : void;
	
    public function getParticle() : FloatingTextParticle {
    	return $this->particle;
	}

    public function update() : void {
    	$this->getParticle()->setText($this->getText());
	}
}