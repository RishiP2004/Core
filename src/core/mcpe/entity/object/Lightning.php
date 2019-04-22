<?php

namespace core\mcpe\entity\object;

use core\mcpe\entity\monster\walking\Creeper;

use pocketmine\entity\{
	Entity,
	Living
};

use pocketmine\item\Item;

use pocketmine\block\Liquid;

use pocketmine\math\{
	Vector3,
	AxisAlignedBB
};

use pocketmine\event\entity\EntityDamageByEntityEvent;

use pocketmine\network\mcpe\protocol\PlaySoundPacket;

class Lightning extends Entity {
	public const NETWORK_ID = self::LIGHTNING_BOLT;

	public $doneDamage = false;

	public $width = 0.3, $length = 0.9, $height = 1.8;

	protected $age = 0;

	public function getName() : string {
		return "Lightning";
	}

	public function onUpdate(int $currentTick) : bool {
		if(!$this->doneDamage) {
			$this->doneDamage = true;
			$fire = Item::get(Item::FIRE)->getBlock();
			$oldBlock = $this->getLevel()->getBlock($this);

			if($oldBlock instanceof Liquid){

			} else if($oldBlock->isSolid()) {
				$v3 = new Vector3($this->x, $this->y + 1, $this->z);
			} else {
				$v3 = new Vector3($this->x, $this->y, $this->z);
			}
			$fire->setDamage(11);

			if(isset($v3)) {
				$this->getLevel()->setBlock($v3, $fire);
			}
			foreach($this->level->getNearbyEntities($this->growAxis($this->boundingBox, 6, 6, 6), $this) as $entity) {
				if($entity instanceof Living){
					$distance = $this->distance($entity);
					$distance = ($distance > 0 ? $distance : 1);
					$k = 5;
					$damage = $k / $distance;
					$ev = new EntityDamageByEntityEvent($this, $entity, 16, $damage);

					$entity->attack($ev);
					$entity->setOnFire(mt_rand(3, 8));
				}
				if($entity instanceof Creeper) {
					$entity->ignite();
				}
			}
			$pk = new PlaySoundPacket();
			$pk->soundName = "ambient.weather.lightning.impact";
			$pk->x = $this->getX();
			$pk->y = $this->getY();
			$pk->z = $this->getZ();
			$pk->volume = 500;
			$pk->pitch = 1;

			foreach($this->level->getPlayers() as $player) {
				$player->dataPacket($pk);
			}
		}
		if($this->age > 6 * 20) {
			$this->flagForDespawn();
		}
		return parent::onUpdate($currentTick);
	}

	private function growAxis(AxisAlignedBB $axis, $x, $y, $z) {
		return new AxisAlignedBB($axis->minX - $x, $axis->minY - $y, $axis->minZ - $z, $axis->maxX + $x, $axis->maxY + $y, $axis->maxZ + $z);
	}
}