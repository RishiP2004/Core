<?php

declare(strict_types = 1);

namespace CortexPE\level\particle;

use pocketmine\level\particle\{
	GenericParticle,
	Particle
};

use pocketmine\math\Vector3;

class RocketParticle extends GenericParticle {
	public function __construct(Vector3 $pos) {
		parent::__construct($pos, Particle::TYPE_FIREWORK_WHITE, 0);
	}
}