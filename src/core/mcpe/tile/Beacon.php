<?php

namespace core\mcpe\tile;

use core\mcpe\inventory\BeaconInventory;

use pocketmine\tile\Spawnable;

use pocketmine\inventory\InventoryHolder;

use pocketmine\nbt\tag\{
	CompoundTag,
	IntTag
};

use pocketmine\Player;

use pocketmine\item\Item;

use pocketmine\Server;

use pocketmine\block\Block;

use pocketmine\math\Vector3;

use pocketmine\level\Level;

use pocketmine\math\AxisAlignedBB;

use pocketmine\entity\{
	EffectInstance,
	Effect
};

class Beacon extends Spawnable implements InventoryHolder {
    public const BEACON = "Beacon";

	public const TAG_PRIMARY = "primary", TAG_SECONDARY = "secondary";
	/** @var BeaconInventory */
	private $inventory;
	/** @var CompoundTag */
	private $nbt;

	public function __construct(Level $level, CompoundTag $nbt) {
		if(!$nbt->hasTag(self::TAG_PRIMARY, IntTag::class)) {
			$nbt->setInt(self::TAG_PRIMARY, 0);
		}
		if(!$nbt->hasTag(self::TAG_SECONDARY, IntTag::class)) {
			$nbt->setInt(self::TAG_SECONDARY, 0);
		}
		$this->inventory = new BeaconInventory($this);
		
		parent::__construct($level, $nbt);
		
		$this->scheduleUpdate();
	}

	public function getInventory() {
		return $this->inventory;
	}

	public function saveNBT() : CompoundTag {
		return parent::saveNBT();
	}

	public function addAdditionalSpawnData(CompoundTag $nbt) : void {
		$nbt->setInt(self::TAG_PRIMARY, $this->getNBT()->getInt(self::TAG_PRIMARY));
		$nbt->setInt(self::TAG_SECONDARY, $this->getNBT()->getInt(self::TAG_SECONDARY));
	}

	public function getNBT() : CompoundTag {
		return $this->nbt;
	}

	public function updateCompoundTag(CompoundTag $nbt, Player $player) : bool {
		$this->setPrimaryEffect($nbt->getInt(self::TAG_PRIMARY));
		$this->setSecondaryEffect($nbt->getInt(self::TAG_SECONDARY));
		return true;
	}

	public function setPrimaryEffect(int $effectId) {
		$this->getNBT()->setInt(self::TAG_PRIMARY, $effectId);
	}

	public function setSecondaryEffect(int $effectId) {
		$this->getNBT()->setInt(self::TAG_SECONDARY, $effectId);
	}

	public function isPaymentItem(Item $item){ //ToDo: When FloatingInventory implemented, remove item
		return in_array($item->getId(), [
			Item::DIAMOND,
			Item::IRON_INGOT,
			Item::GOLD_INGOT,
			Item::EMERALD
		]);
	}

	public function isSecondaryAvailable() {
		return $this->getLayers() >= 4 && !$this->solidAbove();
	}

	public function getLayers() {
		$layers = 0;
		
		if($this->checkShape($this->getSide(0), 1)) {
			$layers++;
		} else {
			return $layers;
		}
		if($this->checkShape($this->getSide(0, 2), 2)) {
			$layers++;
		} else {
			return $layers;
		}
		if($this->checkShape($this->getSide(0, 3), 3)) {
			$layers++;
		} else {
			return $layers;
		}	
		if($this->checkShape($this->getSide(0, 4), 4)) {
			$layers++;
		}
		return $layers;
	}

	public function checkShape(Vector3 $pos, $layer = 1) {
		for($x = $pos->x - $layer; $x <= $pos->x + $layer; $x++) {
			for($z = $pos->z - $layer; $z <= $pos->z + $layer; $z++) {
				if(!in_array($this->getLevel()->getBlockIdAt($x, $pos->y, $z), [
					Block::DIAMOND_BLOCK,
					Block::IRON_BLOCK,
					Block::EMERALD_BLOCK,
					Block::GOLD_BLOCK
				])) {
					return false;
				}
			}
		}
		return true;
	}

	public function solidAbove() {
		if($this->y === $this->getLevel()->getHighestBlockAt($this->x, $this->z)) {
			return false;
		}
		for($i = $this->y; $i < Level::Y_MAX; $i++) {
			if(($block = $this->getLevel()->getBlock(new Vector3($this->x, $i, $this->z)))->isSolid() && !$block->getId() === Block::BEACON) {
				return true;
			}
		}
		return false;
	}

	public function isActive() {
		return !empty($this->getEffects()) && $this->checkShape($this->getSide(0), 1);
	}

	public function getEffects() {
		return [
			$this->getPrimaryEffect(),
			$this->getSecondaryEffect()
		];
	}

	public function getPrimaryEffect() {
		return $this->getNBT()->getInt(self::TAG_PRIMARY);
	}

	public function getSecondaryEffect() {
		return $this->getNBT()->getInt(self::TAG_SECONDARY);
	}

	public function getTierEffects() {
	}

	public function getEffectTier(int $tier) {
	}

	public function onUpdate() : bool {
		if((Server::getInstance()->getTick() % (20 * 4)) == 0) {
			if($this->getLevel() instanceof Level) {
				if(!Server::getInstance()->isLevelLoaded($this->getLevel()->getName()) or !$this->getLevel()->isChunkLoaded($this->x >> 4, $this->z >> 4)) {
					return false;
				}
				if(!empty($this->getEffects())) {
					$this->applyEffects($this);
				}
			}
		}
		return true;
	}

	public function applyEffects(Vector3 $pos) {
		$layers = $this->getLayers();

		foreach($this->getLevel()->getCollidingEntities(new AxisAlignedBB($pos->x - (10 + 10 * $layers), 0, $pos->z - (10 + 10 * $layers), $pos->x + (10 + 10 * $layers), Level::Y_MAX, $pos->z + (10 + 10 * $layers))) as $player) {
			foreach($this->getEffects() as $effectId) {
				if($this->isEffectAvailable($effectId) and $player instanceof Player) {
					$player->removeEffect($effectId);
					
					$effect = new EffectInstance(Effect::getEffect($effectId));
					$effect->setDuration(20 * 9 + $layers * 2 * 20);
					
					if($this->getSecondaryEffect() !== 0 && $this->getSecondaryEffect() !== Effect::REGENERATION) {
						$effect->setAmplifier(1);
					}
					$player->addEffect($effect);
				}
			}
		}
	}

	public function isEffectAvailable(int $effectId) {
		switch($effectId) {
			case Effect::SPEED:
			case Effect::HASTE:
				return $this->getLayers() >= 1 && !$this->solidAbove();
				break;
			case Effect::DAMAGE_RESISTANCE:
			case Effect::JUMP:
				return $this->getLayers() >= 2 && !$this->solidAbove();
				break;
			case Effect::STRENGTH:
				return $this->getLayers() >= 3 && !$this->solidAbove();
				break;
			case Effect::REGENERATION:
				return $this->getLayers() >= 4 && !$this->solidAbove();
				break;
			default:
				return false;
		}
	}

	protected function readSaveData(CompoundTag $nbt) : void {
		$this->nbt = $nbt;
	}

	protected function writeSaveData(CompoundTag $nbt) : void {
		$nbt->setInt(self::TAG_PRIMARY, $this->getNBT()->getInt(self::TAG_PRIMARY));
		$nbt->setInt(self::TAG_SECONDARY, $this->getNBT()->getInt(self::TAG_SECONDARY));
	}
}
