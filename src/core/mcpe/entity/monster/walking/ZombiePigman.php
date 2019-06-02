<?php

declare(strict_types = 1);

namespace core\mcpe\entity\monster\walking;

use core\mcpe\entity\{
    MonsterBase,
    Collidable,
    CollisionCheckingTrait,
	InventoryHolder,
    ItemHolderTrait,
    AgeableTrait,
    CreatureBase
};

use pocketmine\entity\{
	Ageable,
	Entity
};

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\level\Position;

use pocketmine\item\Item;

class ZombiePigman extends MonsterBase implements Ageable, Collidable, InventoryHolder {
    use CollisionCheckingTrait, ItemHolderTrait, AgeableTrait;

    const NETWORK_ID = self::ZOMBIE_PIGMAN;

    public $width = 2.0, $height = 2.0;

    public function initEntity() : void {
		$this->mainHand = Item::get(Item::GOLD_SWORD);

        parent::initEntity();
    }

    public function getName() : string {
        return "Zombie Pigman";
    }

    public function entityBaseTick(int $tickDiff = 1) : bool {
        return parent::entityBaseTick($tickDiff);
    }

    public static function spawnMob(Position $spawnPos, ?CompoundTag $spawnData = null) : ?CreatureBase {
    }

    public static function spawnFromSpawner(Position $spawnPos, ?CompoundTag $spawnData = null) : ?CreatureBase {
    }

    public function onCollideWithEntity(Entity $entity) : void {
    }

	public function equipRandomItems() : void {
	}

	public function equipRandomArmour() : void {
	}

	public function checkItemValueToMainHand(Item $item) : bool {
	}

	public function checkItemValueToOffHand(Item $item) : bool {
	}

    public function getXpDropAmount() : int {
        if($this->isBaby()) {
            return 12;
        }
        $exp = 5;

        foreach($this->getArmorInventory()->getContents() as $peice) {
            $exp += mt_rand(1, 3);
        }
        return $exp;
    }

    public function getDrops() : array {
		$drops = parent::getDrops();
		//TODO chance drop item and armour
		return $drops;
    }
}