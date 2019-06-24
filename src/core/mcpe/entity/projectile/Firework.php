<?php

declare(strict_types = 1);

namespace core\mcpe\entity\projectile;

use core\mcpe\item\Fireworks;

use pocketmine\Player;

use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Projectile;

use pocketmine\level\Level;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\utils\Random;

use pocketmine\network\mcpe\protocol\{
	LevelSoundEventPacket,
	EntityEventPacket
};

use pocketmine\math\RayTraceResult;

class Firework extends Projectile {
	public const NETWORK_ID = self::FIREWORKS_ROCKET;

	public $width = 0.25, $height = 0.25, $gravity = 0.0, $drag = 0.01;

	public $random;
	public $fireworks;

	private $lifeTime = 0;

	public function __construct(Level $level, CompoundTag $nbt, Entity $shootingEntity = null, ?Fireworks $item = null, ?Random $random = null) {
		$this->random = $random;
		$this->fireworks = $item;

		parent::__construct($level, $nbt, $shootingEntity);
	}

	protected function initEntity() : void {
		parent::initEntity();

		$random = $this->random ?? new Random;

		$this->setGenericFlag(self::DATA_FLAG_HAS_COLLISION, true);
		$this->setGenericFlag(self::DATA_FLAG_AFFECTED_BY_GRAVITY, true);
		$this->getDataPropertyManager()->setItem(self::DATA_MINECART_DISPLAY_BLOCK, $this->item);

		$flyTime = 1;

		if($this->namedtag->hasTag("Fireworks")) {
			$flyTime = $this->namedtag->getCompoundTag("Fireworks")->getByte("Flight", 1);
		}
		$this->lifeTime = 20 * $flyTime + $random->nextBoundedInt(5) + $random->nextBoundedInt(7);
	}

	public function spawnTo(Player $player) : void {
		$this->setMotion($this->getDirectionVector());
		$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_LAUNCH);
		parent::spawnTo($player);

	}

	public function entityBaseTick(int $tickDiff = 1) : bool {
		if($this->lifeTime-- < 0) {
			$this->flagForDespawn();
			return true;
		} else {
			return parent::entityBaseTick($tickDiff);
		}
	}

	public function despawnFromAll() : void {
		$this->broadcastEntityEvent(EntityEventPacket::FIREWORK_PARTICLES);
		parent::despawnFromAll();
		$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_BLAST);
	}

	protected function onHitEntity(Entity $entityHit, RayTraceResult $hitResult) : void {
	}
}