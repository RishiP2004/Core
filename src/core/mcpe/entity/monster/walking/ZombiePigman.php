<?php

namespace core\mcpe\entity\monster\walking;

use core\Core;

use core\mcpe\entity\{
    MonsterBase,
    Collidable,
    CollisionCheckingTrait,
    ItemHolderTrait,
    AgeableTrait,
    CreatureBase
};

use pocketmine\entity\Ageable;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\level\Position;

use pocketmine\entity\Entity;

use pocketmine\item\Item;

class ZombiePigman extends MonsterBase implements Ageable, Collidable {
    use CollisionCheckingTrait, ItemHolderTrait, AgeableTrait;

    const NETWORK_ID = self::ZOMBIE_VILLAGER;

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
        // TODO: Implement spawnMob() method.
    }

    public static function spawnFromSpawner(Position $spawnPos, ?CompoundTag $spawnData = null) : ?CreatureBase {
        // TODO: Implement spawnFromSpawner() method.
    }

    public function onCollideWithEntity(Entity $entity) : void {
        // TODO: Implement onCollideWithEntity() method.
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
        $drops = [];

        if(Core::getInstance()->getMCPE()->drops()) {
            array_push($drops, Item::get(Item::ROTTEN_FLESH, 0, mt_rand(0, 2)));

            if(mt_rand(1, 1000) % 25 == 0){
                switch(mt_rand(1, 3)){
                    case 1:
                        array_push($drops, Item::get(Item::CARROT, 0, 1));
                    break;
                    case 2:
                        array_push($drops, Item::get(Item::POTATO, 0, 1));
                    break;
                    case 3:
                        array_push($drops, Item::get(Item::IRON_INGOT, 0, 1));
                    break;
                }
            }
        }
        return $drops;
    }
}