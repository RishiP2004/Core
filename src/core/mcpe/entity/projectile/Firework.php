<?php

namespace core\mcpe\entity\projectile;

use core\mcpe\item\Fireworks;

use pocketmine\entity\projectile\Projectile;

use pocketmine\level\Level;

use pocketmine\nbt\tag\{
	CompoundTag,
	ByteTag
};

use pocketmine\entity\{
	Entity,
	Living
};

use pocketmine\utils\Random;

use pocketmine\item\Item;

use pocketmine\Player;

use pocketmine\network\mcpe\protocol\{
	SetEntityDataPacket,
	LevelSoundEventPacket,
	EntityEventPacket
};

use pocketmine\event\entity\EntityDamageEvent;

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

		$random = $this->random ?? new Random();

		$this->setGenericFlag(self::DATA_FLAG_HAS_COLLISION, true);
		$this->setGenericFlag(self::DATA_FLAG_AFFECTED_BY_GRAVITY, true);

		if($this->fireworks instanceof Item) {
			$this->getDataPropertyManager()->setItem(16, Item::get($this->fireworks->getId(), $this->fireworks->getDamage(), $this->fireworks->getCount(), $this->fireworks->getCompoundTag()));
		} else {
			$this->getDataPropertyManager()->setItem(16, Item::get(Item::FIREWORKS));
		}
		$flyTime = 1;

		try {
			if(!is_null($this->namedtag->getCompoundTag(Fireworks::TAG_FIREWORKS))) {
				$fireworksNBT = $this->namedtag->getCompoundTag(Fireworks::TAG_FIREWORKS);

				if($fireworksNBT->hasTag(Fireworks::TAG_FLIGHT, ByteTag::class)){
					$flyTime = $fireworksNBT->getByte(Fireworks::TAG_FLIGHT, 1);
				}
			}
		} catch(\Exception $exception) {
			$this->server->getLogger()->debug($exception);
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
	/**
	 * @param Player[]|Player $player
	 * @param array $data Properly formatted entity data, defaults to everything
	 */
	public function sendData($player, array $data = null) : void {
		if(!is_array($player)) {
			$player = [$player];
		}
		$pk = new SetEntityDataPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->metadata = $data ?? $this->getDataPropertyManager()->getDirty();

		foreach($player as $p) {
			if($p === $this) {
				continue;
			}
			$p->dataPacket(clone $pk);
		}
		if($this instanceof Player) {
			$this->dataPacket($pk);
		}
	}

	public function despawnFromAll() : void {
		if(count($this->namedtag->getCompoundTag(Fireworks::TAG_FIREWORKS)->getListTag(Fireworks::TAG_EXPLOSIONS)) > 0) {
			foreach($this->getLevel()->getNearbyEntities($this->getBoundingBox()->expand(5, 5, 5)) as $entity) {
				if($entity instanceof Living) {
					$distance = $this->distance($entity);
					$distance = ($distance > 0 ? $distance : 1);
					$k = 22.5;
					$damage = $k / $distance;

					if($damage > 0) {
						$ev = new EntityDamageEvent($entity, EntityDamageEvent::CAUSE_CUSTOM, $damage);

						$entity->attack($ev);
					}
				}
			}
		}
		$this->broadcastEntityEvent(EntityEventPacket::FIREWORK_PARTICLES, 0);
		parent::despawnFromAll();
		$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_BLAST);
	}
}