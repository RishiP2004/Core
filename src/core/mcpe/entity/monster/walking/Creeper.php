<?php

namespace core\mcpe\entity\monster\walking;

use core\CorePlayer;

use core\mcpe\entity\{
	CreatureBase,
	MonsterBase,
	Interactable,
	ClimbingTrait
};

use core\mcpe\object\Lightning;

use pocketmine\entity\{
	Explosive,
	Entity
};

use pocketmine\event\entity\{
	ExplosionPrimeEvent,
	EntityDamageEvent,
	EntityDamageByEntityEvent
};

use pocketmine\level\{
	Explosion,
	Position
};

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\math\AxisAlignedBB;

use pocketmine\item\{
	Item,
	FlintSteel
};

class Creeper extends MonsterBase implements Explosive, Interactable {
    use ClimbingTrait;

    public const NETWORK_ID = self::CREEPER;

    public $width = 0.7, $height = 1.7;

    protected $bombTime = 30, $speed = 0.9;

    protected $charged = false;

    private $startExplosion = false,  $ignited = false;

    public function initEntity() : void {
        parent::initEntity();
    }

    public function getName() : string {
        return "Creeper";
    }

    public function onUpdate(int $currentTick) : bool {
        if($this->closed) {
            return false;
        }
        if($this->attackTime > 0) {
            return parent::onUpdate($currentTick);
        } else {
            if($this->moveTime <= 0 and !$this->target instanceof Entity and $this->isTargetValid($this->target)) {
                $x = $this->target->x - $this->x;
                $y = $this->target->y - $this->y;
                $z = $this->target->z - $this->z;
                $diff = abs($x) + abs($z);

                if($diff > 0) {
                    if(!$this->startExplosion) {
                        $this->motion->x = $this->speed * 0.15 * ($x / $diff);
                        $this->motion->z = $this->speed * 0.15 * ($z / $diff);
                    }
                    $this->yaw = rad2deg(-atan2($x / $diff, $z / $diff)); // TODO: desync head with body when AI improves
                }
                $this->pitch = $y == 0 ? 0 : rad2deg(-atan2($y, sqrt($x * $x + $z * $z)));

                if($this->distance($this->target) <= 0) {
                    $this->target = null;
                }
            } else if($this->target instanceof Entity and $this->isTargetValid($this->target)) {
                $this->moveTime = 0;

                if($this->target->distance($this) <= 3) {
                    $this->startExplosion = true;
                }
                if(!$this->startExplosion and !$this->ignited) {
                    $this->bombTime = 30;
                    $this->setGenericFlag(self::DATA_FLAG_IGNITED, false);
                }
                $x = $this->target->x - $this->x;
                $y = $this->target->y - $this->y;
                $z = $this->target->z - $this->z;
                $diff = abs($x) + abs($z);

                if($diff > 0) {
                    if(!$this->startExplosion) {
                        $this->motion->x = $this->speed * 0.15 * ($x / $diff);
                        $this->motion->z = $this->speed * 0.15 * ($z / $diff);
                    }
                    $this->yaw = rad2deg(-atan2($x / $diff, $z / $diff)); // TODO: desync head with body when AI improves
                }
                $this->pitch = $y == 0 ? 0 : rad2deg(-atan2($y, sqrt($x * $x + $z * $z)));
            } else if($this->moveTime <= 0) {
                $this->moveTime = 100;
                // TODO: random target position
            }
        }
        $tickDiff = $currentTick - $this->lastUpdate;

        if($this->ignited or $this->startExplosion) {
            $this->setGenericFlag(self::DATA_FLAG_IGNITED, true);

            $this->startExplosion = true;
            $this->bombTime -= $tickDiff;

            if($this->bombTime <= 0 and $this->isAlive()) {
                $this->explode();
                return false;
            }
        } else {
            $this->bombTime += $tickDiff;

            $this->setGenericFlag(self::DATA_FLAG_IGNITED, false);

            if($this->bombTime >= 30) {
                $this->bombTime = 30;
                $this->startExplosion = false;
            }
        }
        return parent::onUpdate($currentTick);
    }

    public function explode() {
        $this->server->getPluginManager()->callEvent($event = new ExplosionPrimeEvent($this, $this->charged ? 6 : 3));

        if(!$event->isCancelled()) {
            $explosion = new Explosion($this, $event->getForce(), $this);

            $event->setBlockBreaking(true); // TODO: mob griefing gamerule?

            if($event->isBlockBreaking()) {
                $explosion->explodeA();
            }
            $explosion->explodeB();
        }
        $this->flagForDespawn();
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
        if(isset($this->target)) {
            $this->speed = 1.2;
        } else {
            $this->speed = 0.9;
        }
        $hasUpdate = parent::entityBaseTick($tickDiff);

        if($this->moveTime > 0) {
            $this->moveTime -= $tickDiff;
        }
        return $hasUpdate;
    }

    public function onPlayerLook(CorePlayer $player) : void {
        if($player->canInteract($this, $player->isCreative() ? 13 : 7) and $player->getInventory()->getItemInHand() instanceof FlintSteel) {
            $player->getDataPropertyManager()->setString(self::DATA_INTERACTIVE_TAG, "Ignite");
        }
    }

    public function attack(EntityDamageEvent $source) : void {
        parent::attack($source);

        if(!$this->target instanceof CorePlayer) {
            if($source instanceof EntityDamageByEntityEvent) {
                $damager = $source->getDamager();

                if($damager instanceof CorePlayer and $damager->isSurvival() and $this->distance($damager) <= 16 and abs($this->y - $damager->y) <= 4) {
                    $this->target = $damager;
                } else if(!$damager instanceof CorePlayer and $this->distance($damager) <= 16 and abs($this->y - $damager->y) <= 4) {
                    $this->target = $damager;
                }
            }
        }
    }

    public static function spawnMob(Position $spawnPos, ?CompoundTag $spawnData = null) : ?CreatureBase {
        $width = 0.7;
        $height = 1.7;
        $boundingBox = new AxisAlignedBB(0, 0, 0, 0, 0, 0);
        $halfWidth = $width / 2;

        $boundingBox->setBounds($spawnPos->x - $halfWidth, $spawnPos->y, $spawnPos->z - $halfWidth, $spawnPos->x + $halfWidth, $spawnPos->y + $height, $spawnPos->z + $halfWidth);
        // TODO: work on logic here more
        if(!$spawnPos->isValid() or !$spawnPos->level->getBlock($spawnPos->subtract(0, 1), true, false)->isSolid() or $spawnPos->level->getFullLight($spawnPos) > 7) {
            return null;
        }
        $nbt = self::createBaseNBT($spawnPos);

        if(isset($spawnData)) {
            $nbt = $spawnData->merge($nbt);

            $nbt->setInt("id", self::NETWORK_ID);
        }
        /** @var self $entity */
        $entity = self::createEntity("Creeper", $spawnPos->level, $nbt);
        return $entity;
    }

    public static function spawnFromSpawner(Position $spawnPos, ?CompoundTag $spawnData = null) : ?CreatureBase {
        return parent::spawnFromSpawner($spawnPos, $$spawnData);
    }

    public function onCollideWithEntity(Entity $entity) : void {
        if($entity instanceof Lightning) {
            $this->setCharged();
        }
    }

    public function ignite() : void {
        $this->ignited = true;

        $this->scheduleUpdate();
    }

    public function isCharged() : bool {
        return $this->charged;
    }

    public function setCharged(bool $charged = true) : self {
        $this->charged = $charged;

        $this->setGenericFlag(self::DATA_FLAG_POWERED, $charged);
        return $this;
    }

    public function onPlayerInteract(CorePlayer $player) : void {
        if($player->getInventory()->getItemInHand() instanceof FlintSteel) {
            $this->ignited = true;

            $this->scheduleUpdate();
        }
    }

	public function getDrops() : array {
		return [
			Item::get(Item::GUNPOWDER, 0, mt_rand(0, 2))
		];
	}
}