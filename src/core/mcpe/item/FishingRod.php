<?php

namespace core\mcpe\item;

use core\CorePlayer;

use core\mcpe\FishingLootTable;

use core\mcpe\entity\projectile\FishingHook;

use pocketmine\item\{
	Durable,
	Item
};

use pocketmine\item\enchantment\Enchantment;

use pocketmine\Player;

use pocketmine\math\Vector3;

use pocketmine\entity\Entity;

use pocketmine\entity\projectile\Projectile;

use pocketmine\event\entity\ProjectileLaunchEvent;

use pocketmine\level\sound\LaunchSound;

use pocketmine\block\Block;

class FishingRod extends Durable {
	public function __construct($meta = 0) {
		parent::__construct(Item::FISHING_ROD, $meta, "Fishing Rod");
	}

	public function getProjectileEntityType() : string {
		return "FishingHook";
	}

	public function getThrowForce() : float {
		return 1.6;
	}

	public function getMaxStackSize() : int {
		return 1;
	}

	public function getMaxDurability() : int {
		return 355;
	}

	public function onClickAir(Player $player, Vector3 $directionVector) : bool {
		if($player instanceof CorePlayer) {
			if($player->isFishing()) {
				$nbt = Entity::createBaseNBT($player->add(0, $player->getEyeHeight(), 0), $directionVector, $player->yaw, $player->pitch);
				/** @var FishingHook $projectile */
				$projectile = Entity::createEntity($this->getProjectileEntityType(), $player->getLevel(), $nbt, $player);

				if($projectile !== null) {
					$projectile->setMotion($projectile->getMotion()->multiply($this->getThrowForce()));
				}
				if($projectile instanceof Projectile) {
					$player->getServer()->getPluginManager()->callEvent($ev = new ProjectileLaunchEvent($projectile));

					if($ev->isCancelled()) {
						$projectile->flagForDespawn();
					} else {
						$projectile->spawnToAll();
						$player->getLevel()->addSound(new LaunchSound($player), $player->getViewers());
					}
				}
				if($this->hasEnchantments()) {
					foreach(Item::getEnchantments($this) as $enchantment) {
						switch($enchantment->getId()) {
							case Enchantment::LURE:
								$divisor = $enchantment->getLevel() * 0.50;
								$rand = intval(round($divisor)) + 3;
							break;
						}
					}
				}
				$projectile->attractTimer = $rand * 20;

				$player->fishingHook = $projectile;
				$player->setFishing(true);
			} else {
				$projectile = $player->fishingHook;

				if($projectile instanceof FishingHook) {
					$player->setFishing(false);

					if($player->getLevel()->getBlock($projectile->asVector3())->getId() === Block::WATER or $player->getLevel()->getBlock($projectile)->getId() === Block::WATER) {
						$damage = 5;
					} else {
						$damage = mt_rand(10, 15); //TODO: Implement entity / block collision properly
					}
					$this->applyDamage($damage);

					if($projectile->coughtTimer > 0) {
						$lvl = 0;

						if($this->hasEnchantments()){
							if($this->hasEnchantment(Enchantment::LUCK_OF_THE_SEA)){
								$lvl = $this->getEnchantment(Enchantment::LUCK_OF_THE_SEA)->getLevel();
							}
						}
						$item = FishingLootTable::getRandom($lvl);
						$player->getInventory()->addItem($item);

						$player->addXp(mt_rand(1, 6));
					}
				}
			}
		}
		return true;
	}
}