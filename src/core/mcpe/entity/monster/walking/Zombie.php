<?php

namespace core\mcpe\entity\monster\walking;

use core\Core;
use core\CorePlayer;

use core\mcpe\entity\{
    MonsterBase,
    InventoryHolder,
    ItemHolderTrait,
    AgeableTrait,
    ClimbingTrait,
    CreatureBase
};

use pocketmine\entity\Ageable;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\level\{
    Level,
    Position
};

use pocketmine\level\biome\Biome;

use pocketmine\block\Water;

use pocketmine\entity\{
    Effect,
    Entity
};

use pocketmine\event\entity\{
    EntityDamageEvent,
    EntityDamageByEntityEvent
};

use pocketmine\Player;

use pocketmine\network\mcpe\protocol\{
    EntityEventPacket,
    TakeItemEntityPacket
};

use pocketmine\item\Item;

class Zombie extends MonsterBase implements Ageable, InventoryHolder {
    use ItemHolderTrait, AgeableTrait, ClimbingTrait;

    const NETWORK_ID = self::ZOMBIE;

    public $width = 0.6, $height = 1.95;

    protected $attackDelay;

    protected $speed = 1.2;

    public function initEntity(CompoundTag $tag) : void {
        if(mt_rand(1, 100) < 6) {
            $this->setBaby();

            if(mt_rand(1, 100) <= 15) {
                // TODO: zombie jockey
            }
        }
        if(mt_rand(1, 100) >= 80) {
            if((bool) mt_rand(0, 1)) {
                $this->equipRandomItems();
            } else {
                $this->equipRandomArmour();
            }
        }
        parent::initEntity($tag);
    }

    public function getName() : string {
        return "Zombie";
    }

    public function entityBaseTick(int $tickDiff = 1) : bool {
        if($this->target === null) {
            foreach($this->hasSpawned as $player) {
                if($player->isSurvival() and $this->distance($player) <= 16 and $this->hasLineOfSight($player)) {
                    $this->target = $player;
                }
            }
        } else if($this->target instanceof CorePlayer) {
            if($this->target->isCreative() or !$this->target->isAlive()) {
                $this->target = null;
            }
        }
        $hasUpdate = parent::entityBaseTick($tickDiff);

        if($this->moveTime > 0) {
            $this->moveTime -= $tickDiff;
        }
        $time = $this->getLevel()->getTime() % Level::TIME_FULL;

        if(!$this->isOnFire() and ($time < Level::TIME_NIGHT or $time > Level::TIME_SUNRISE) and $this->level->getBlockSkyLightAt($this->getFloorX(), $this->getFloorY(), $this->getFloorZ()) >= 15) {
            $this->setOnFire(2);
        }
        if($this->isOnFire() and $this->level->getBlock($this, true, false) instanceof Water) { // TODO: check weather
            $this->extinguish();
        }
        $this->attackDelay += $tickDiff;

        if(!$this->hasEffect(Effect::WATER_BREATHING) and $this->isUnderwater()) {
            $hasUpdate = true;
            $airTicks = $this->getAirSupplyTicks() - $tickDiff;

            // TODO: drowned transformation
            if($airTicks <= -20) {
                $airTicks = 0;
                $event = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_DROWNING, 2);

                $this->attack($event);
            }
            $this->setAirSupplyTicks($airTicks);
        } else {
            $this->setAirSupplyTicks(300);
        }
        return $hasUpdate;
    }

    public function equipRandomItems() : void {
        //TODO random enchantments and random item (iron sword or iron shovel or iron axe)
    }


    public function equipRandomArmour() : void {
        //TODO random enchantments and random armour
    }

    public function onUpdate(int $currentTick) : bool {
        if($this->isFlaggedForDespawn() or $this->closed) {
            return false;
        }
        if($this->attackTime > 0) {
            return parent::onUpdate($currentTick);
        } else {
            if($this->moveTime <= 0 and $this->isTargetValid($this->target) and !$this->target instanceof Entity) {
                $x = $this->target->x - $this->x;
                $y = $this->target->y - $this->y;
                $z = $this->target->z - $this->z;
                $diff = abs($x) + abs($z);

                if($diff > 0) {
                    $this->motion->x = $this->speed * 0.15 * ($x / $diff);
                    $this->motion->z = $this->speed * 0.15 * ($z / $diff);
                    $this->yaw = rad2deg(-atan2($x / $diff, $z / $diff)); // TODO: desync head with body when AI improves
                }
                $this->pitch = $y == 0 ? 0 : rad2deg(-atan2($y, sqrt($x * $x + $z * $z)));

                if($this->distance($this->target) <= 0) {
                    $this->target = null;
                }
            } else if($this->target instanceof Entity and $this->isTargetValid($this->target)) {
                $this->moveTime = 0;
                $x = $this->target->x - $this->x;
                $y = $this->target->y - $this->y;
                $z = $this->target->z - $this->z;
                $diff = abs($x) + abs($z);

                if($diff > 0) {
                    $this->motion->x = $this->speed * 0.15 * ($x / $diff);
                    $this->motion->z = $this->speed * 0.15 * ($z / $diff);
                    $this->yaw = rad2deg(-atan2($x / $diff, $z / $diff)); // TODO: desync head with body when AI improves
                }
                $this->pitch = $y == 0 ? 0 : rad2deg(-atan2($y, sqrt($x * $x + $z * $z)));
            } else if($this->moveTime <= 0) {
                $this->moveTime = 100;
                // TODO: random target position
            }
        }
        return parent::onUpdate($currentTick);
    }

    public function onCollideWithPlayer(Player $player) : void {
        if($this->target === $player and $this->attackDelay > 10) {
            $this->attackDelay = 0;
            $damage = 2;

            switch($this->getLevel()->getDifficulty()) {
                case Level::DIFFICULTY_EASY:
                    $damage = 2;
                break;
                case Level::DIFFICULTY_NORMAL:
                    $damage = 3;
                break;
                case Level::DIFFICULTY_HARD:
                    $damage = 4;
            }
            if($this->mainHand !== null) {
                $damage = $this->mainHand->getAttackPoints();
            }
            $packet = new EntityEventPacket();
            $packet->entityRuntimeId = $this->id;
            $packet->event = EntityEventPacket::ARM_SWING;

            $this->server->broadcastPacket($this->hasSpawned, $packet);
            $player->attack(new EntityDamageByEntityEvent($this, $player, EntityDamageByEntityEvent::CAUSE_ENTITY_ATTACK, $damage));
        }
    }

    public static function spawnMob(Position $spawnPos, ?CompoundTag $spawnData = null) : ?CreatureBase {
        $nbt = self::createBaseNBT($spawnPos);

        if(isset($spawnData)) {
            $nbt = $spawnData->merge($nbt);

            $nbt->setInt("id", self::NETWORK_ID);
        }
        if($spawnPos->level->getBiomeId($spawnPos->x, $spawnPos->z) === Biome::DESERT and mt_rand(1, 100) > 80) {
            /** @var Husk $entity */
            $entity = self::createEntity(Husk::NETWORK_ID, $spawnPos->level, $nbt);
        } else {
            /** @var self $entity */
            $entity = self::createEntity(self::NETWORK_ID, $spawnPos->level, $nbt);
        }
        // TODO: work on logic here more
        if(!$spawnPos->isValid() or count($entity->getBlocksAround()) > 1 or $spawnPos->level->getFullLight($spawnPos) > $entity->spawnLight) {
            $entity->flagForDespawn();
            return null;
        } else {
            $entity->spawnToAll();
            return $entity;
        }
    }

    public function onCollideWithEntity(Entity $entity) : void {
        if($this->target === $entity and $this->attackDelay > 10) {
            $this->attackDelay = 0;
            $damage = 2;

            switch($this->getLevel()->getDifficulty()) {
                case Level::DIFFICULTY_EASY:
                    $damage = 2;
                break;
                case Level::DIFFICULTY_NORMAL:
                    $damage = 3;
                break;
                case Level::DIFFICULTY_HARD:
                    $damage = 4;
            }
            if($this->mainHand !== null) {
                $damage = $this->mainHand->getAttackPoints();
            }
            $packet = new EntityEventPacket();
            $packet->entityRuntimeId = $this->id;
            $packet->event = EntityEventPacket::ARM_SWING;

            $this->server->broadcastPacket($this->hasSpawned, $packet);
            $entity->attack(new EntityDamageByEntityEvent($this, $entity, EntityDamageByEntityEvent::CAUSE_ENTITY_ATTACK, $damage));
        }
        if($entity instanceof \core\mcpe\entity\projectile\Item) {
            if($entity->getPickupDelay() > 0 or !$this instanceof InventoryHolder or $this->level->getDifficulty() <= Level::DIFFICULTY_EASY) {
                return;
            }
            $chance = \core\utils\Level::getRegionalDifficulty($this->level, $this->chunk);

            if($chance < 50) {
                return;
            }
            $item = $entity->getItem();

            if(!$this->checkItemValueToMainHand($item) and !$this->checkItemValueToOffHand($item)) {
                return;
            }
            $packet = new TakeItemEntityPacket();
            $packet->eid = $this->getId();
            $packet->target = $this->getId();

            $this->server->broadcastPacket($this->getViewers(), $packet);
            $this->setDropAll();
            $this->setPersistence(true);

            if($this->checkItemValueToMainHand($item)) {
                $this->mainHand = clone $item;
            } else if($this->checkItemValueToOffHand($item)) {
                $this->offHand = clone $item;
            }
        }
    }

    public function checkItemValueToMainHand(Item $item) : bool {
        // TODO: Implement checkItemValueToMainHand() method.
        return true;
    }

    public function checkItemValueToOffHand(Item $item) : bool {
        // TODO: Implement checkItemValueToOffHand() method.
        return true;
    }

    public function getXpDropAmount() : int {
        if($this->baby) {
            $exp = 12;
        } else {
            $exp = 5;
        }
        foreach($this->getArmorInventory()->getContents() as $piece) {
            $exp += mt_rand(1, 3);
        }
        return $exp;
    }

    public function getDrops() : array {
        $drops = [];

        if(Core::getInstance()->getMCPE()->drops()) {
            $drops = [
                Item::get(Item::ROTTEN_FLESH, 0, mt_rand(0, 2))
            ];
            if(mt_rand(0, 199) < 5) {
                switch(mt_rand(0, 2)) {
                    case 0:
                        $drops[] = Item::get(Item::IRON_INGOT, 0, 1);
                    break;
                    case 1:
                        $drops[] = Item::get(Item::CARROT, 0, 1);
                    break;
                    case 2:
                        $drops[] = Item::get(Item::POTATO, 0, 1);
                    break;
                }
            }
            if($this->dropAll) {
                $drops = array_merge($drops, $this->armorInventory->getContents());
            } else if(mt_rand(1, 100) <= 8.5) {
                if(!empty($this->armorInventory->getContents())) {
                    $drops[] = $this->armorInventory->getContents()[array_rand($this->armorInventory->getContents())];
                }
            }
        }
        return $drops;
    }
}