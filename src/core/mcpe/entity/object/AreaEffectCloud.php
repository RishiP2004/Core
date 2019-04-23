<?php

declare(strict_types = 1);

namespace core\mcpe\entity\object;

use pocketmine\entity\{
	Entity,
	Living,
	EffectInstance
};

use pocketmine\level\particle\Particle;

use pocketmine\nbt\tag\{
	ShortTag,
	FloatTag,
	IntTag
};

use pocketmine\item\Potion;

use pocketmine\math\AxisAlignedBB;

class AreaEffectCloud extends Entity {
	public const NETWORK_ID = self::AREA_EFFECT_CLOUD;

	public const TAG_POTION_ID = "PotionId";
	public const TAG_AGE = "Age";
	public const TAG_RADIUS = "Radius";
	public const TAG_RADIUS_ON_USE = "RadiusOnUse";
	public const TAG_RADIUS_PER_TICK = "RadiusPerTick";
	public const TAG_WAIT_TIME = "WaitTime";
	public const TAG_TILE_X = "TileX";
	public const TAG_TILE_Y = "TileY";
	public const TAG_TILE_Z = "TileZ";
	public const TAG_DURATION = "Duration";
	public const TAG_DURATION_ON_USE = "DurationOnUse";

	public $width = 5, $length = 5, $height = 1;

	private $potionId = 0, $radius = 3, $tileX = 0, $tileY = 0, $tileZ = 0, $duration = 600, $durationOnUse = 0, $waitTime = 10;

	private $radiusOnUse = -0.5, $radiusPerTick = -0.005;

	protected $age = 0;

	public function initEntity() : void {
		parent::initEntity();

		if(!$this->namedtag->hasTag(self::TAG_POTION_ID, ShortTag::class)) {
			$this->namedtag->setShort(self::TAG_POTION_ID, $this->potionId);
		}
		$this->potionId = $this->namedtag->getShort(self::TAG_POTION_ID);

		if(!$this->namedtag->hasTag(self::TAG_RADIUS, FloatTag::class)) {
			$this->namedtag->setFloat(self::TAG_RADIUS, $this->radius);
		}
		$this->radius = $this->namedtag->getFloat(self::TAG_RADIUS);

		if(!$this->namedtag->hasTag(self::TAG_RADIUS_ON_USE, FloatTag::class)) {
			$this->namedtag->setFloat(self::TAG_RADIUS_ON_USE, $this->radiusOnUse);
		}
		$this->radiusOnUse = $this->namedtag->getFloat(self::TAG_RADIUS_ON_USE);

		if(!$this->namedtag->hasTag(self::TAG_RADIUS_PER_TICK, FloatTag::class)) {
			$this->namedtag->setFloat(self::TAG_RADIUS_PER_TICK, $this->radiusPerTick);
		}
		$this->radiusPerTick = $this->namedtag->getFloat(self::TAG_RADIUS_PER_TICK);

		if(!$this->namedtag->hasTag(self::TAG_WAIT_TIME, IntTag::class)) {
			$this->namedtag->setInt(self::TAG_WAIT_TIME, $this->WaitTime);
		}
		$this->waitTime = $this->namedtag->getInt(self::TAG_WAIT_TIME);

		if(!$this->namedtag->hasTag(self::TAG_TILE_X, IntTag::class)) {
			$this->namedtag->setInt(self::TAG_TILE_X, intval(round($this->getX())));
		}
		$this->tileX = $this->namedtag->getInt(self::TAG_TILE_X);

		if(!$this->namedtag->hasTag(self::TAG_TILE_Y, IntTag::class)) {
			$this->namedtag->setInt(self::TAG_TILE_Y, intval(round($this->getY())));
		}
		$this->tileY = $this->namedtag->getInt(self::TAG_TILE_Y);

		if(!$this->namedtag->hasTag(self::TAG_TILE_Z, IntTag::class)) {
			$this->namedtag->setInt(self::TAG_TILE_Z, intval(round($this->getZ())));
		}
		$this->tileZ = $this->namedtag->getInt(self::TAG_TILE_Z);

		if(!$this->namedtag->hasTag(self::TAG_DURATION, IntTag::class)) {
			$this->namedtag->setInt(self::TAG_DURATION, $this->Duration);
		}
		$this->duration = $this->namedtag->getInt(self::TAG_DURATION);

		if(!$this->namedtag->hasTag(self::TAG_DURATION_ON_USE, IntTag::class)) {
			$this->namedtag->setInt(self::TAG_DURATION_ON_USE, $this->DurationOnUse);
		}
		$this->durationOnUse = $this->namedtag->getInt(self::TAG_DURATION_ON_USE);

		$this->getDataPropertyManager()->setInt(self::DATA_AREA_EFFECT_CLOUD_PARTICLE_ID, Particle::TYPE_MOB_SPELL);
		$this->getDataPropertyManager()->setFloat(self::DATA_AREA_EFFECT_CLOUD_RADIUS, $this->radius);
		$this->getDataPropertyManager()->setInt(self::DATA_AREA_EFFECT_CLOUD_WAITING, $this->waitTime);
		$this->getDataPropertyManager()->setFloat(self::DATA_BOUNDING_BOX_HEIGHT, 1);
		$this->getDataPropertyManager()->setFloat(self::DATA_BOUNDING_BOX_WIDTH, $this->radius * 2);
		$this->getDataPropertyManager()->setByte(self::DATA_POTION_AMBIENT, 1);
	}

	public function getName() {
		return "Area Effect Cloud";
	}

	protected function applyGravity() : void {
	}

	public function entityBaseTick(int $tickDiff = 1) : bool {
		if($this->isFlaggedForDespawn()) {
			return false;
		}
		$this->timings->startTiming();

		$hasUpdate = parent::entityBaseTick($tickDiff);

		if($this->age > $this->duration or $this->potionId === 0 or $this->radius <= 0) {
			$this->flagForDespawn();

			$hasUpdate = true;
		} else {
			/** @var EffectInstance[] $effects */
			$effects = Potion::getPotionEffectsById($this->PotionId);

			if(count($effects) <= 0) {
				$this->flagForDespawn();
				$this->timings->stopTiming();
				return true;
			}
			$count = $r = $g = $b = $a = 0;

			foreach($effects as $effect) {
				$ecol = $effect->getColor();
				$r += $ecol->getR();
				$g += $ecol->getG();
				$b += $ecol->getB();
				$a += $ecol->getA();
				$count++;
			}
			$r /= $count;
			$g /= $count;
			$b /= $count;
			$a /= $count;

			$this->getDataPropertyManager()->setInt(self::DATA_POTION_COLOR, (($a & 0xff) << 24) | (($r & 0xff) << 16) | (($g & 0xff) << 8) | ($b & 0xff));

			$this->radius += $this->radiusPerTick;
			$this->getDataPropertyManager()->setFloat(self::DATA_BOUNDING_BOX_WIDTH, $this->radius * 2);

			if($this->waitTime > 0) {
				$this->waitTime--;
				$this->timings->stopTiming();
				return true;
			}
			$bb = new AxisAlignedBB($this->x - $this->radius, $this->y - 1, $this->z - $this->radius, $this->x + $this->radius, $this->y + 1, $this->z + $this->radius);
			$used = false;

			foreach($this->getLevel()->getCollidingEntities($bb, $this) as $collidingEntity) {
				if($collidingEntity instanceof Living && $collidingEntity->distanceSquared($this) <= $this->radius ** 2) {
					$used = true;

					foreach($effects as $eff) {
						$collidingEntity->addEffect($eff);
					}
				}
			}
			if($used) {
				$this->duration -= $this->durationOnUse;
				$this->radius += $this->radiusOnUse;
				$this->waitTime = 10;
			}
		}
		$this->getDataPropertyManager()->setFloat(self::DATA_AREA_EFFECT_CLOUD_RADIUS, $this->radius);
		$this->getDataPropertyManager()->setInt(self::DATA_AREA_EFFECT_CLOUD_WAITING, $this->waitTime);
		$this->timings->stopTiming();
		return $hasUpdate;
	}
}