<?php

declare(strict_types = 1);

namespace core\level\particle;

use pocketmine\math\Vector3;

use pocketmine\level\particle\{
	GenericParticle,
	Particle
};

class MobSpellParticle extends GenericParticle {
	public function __construct(Vector3 $pos, $r = 0, $g = 0, $b = 0, $a = 255) {
		parent::__construct($pos, Particle::TYPE_MOB_SPELL, (($a & 0xff) << 24) | (($r & 0xff) << 16) | (($g & 0xff) << 8) | ($b & 0xff));
	}
}