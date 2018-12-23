<?php

namespace core\mcpe\entity\monster\flying;

use core\Core;

use core\mcpe\entity\{
    MonsterBase,
    Collidable,
    CollisionCheckingTrait,
    CreatureBase
};

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\level\Position;

use pocketmine\math\AxisAlignedBB;

use pocketmine\entity\Entity;

use pocketmine\item\Item;

class Blaze extends MonsterBase implements Collidable{
    use CollisionCheckingTrait;

    const NETWORK_ID = self::BLAZE;

    public $width = 1.25, $height = 1.5;

    public function initEntity(CompoundTag $tag) : void {
        parent::initEntity($tag);
    }

    public function getName() : string {
        return "Blaze";
    }


    public function entityBaseTick(int $tickDiff = 1) : bool {
        return parent::entityBaseTick($tickDiff);
    }

    public static function spawnMob(Position $spawnPos, ?CompoundTag $spawnData = null) : ?CreatureBase {
        $width = 1.25;
        $height = 1.5;
        $boundingBox = new AxisAlignedBB(0, 0, 0, 0, 0, 0);
        $halfWidth = $width / 2;

        $boundingBox->setBounds($spawnPos->x - $halfWidth, $spawnPos->y, $spawnPos->z - $halfWidth, $spawnPos->x + $halfWidth, $spawnPos->y + $height, $spawnPos->z + $halfWidth);

        // TODO: work on logic here more
        if($spawnPos->level === null or !empty($spawnPos->level->getCollisionBlocks($boundingBox, true)) or !$spawnPos->level->getBlock($spawnPos->subtract(0, 1), false, false)->isSolid()) {
            return null;
        }
        $nbt = self::createBaseNBT($spawnPos);

        if(isset($spawnData)) {
            $nbt = $spawnData->merge($nbt);

            $nbt->setInt("id", self::NETWORK_ID);
        }
        /** @var self $entity */
        $entity = self::createEntity("Blaze", $spawnPos->level, $nbt);
        return $entity;
    }

    public static function spawnFromSpawner(Position $spawnPos, ?CompoundTag $spawnData = null) : ?CreatureBase {
        // TODO: Implement spawnFromSpawner() method.
    }

    public function onCollideWithEntity(Entity $entity) : void {
        // TODO: Implement onCollideWithEntity() method.
    }

    public function getDrops() : array {
        if(Core::getInstance()->getMCPE()->drops()) {
            return [
                Item::get(Item::BLAZE_ROD, 0, mt_rand(0, 1))
            ];
        } else {
            return [];
        }
    }
}