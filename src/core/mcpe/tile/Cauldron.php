<?php

declare(strict_types = 1);

namespace core\mcpe\tile;

use pocketmine\tile\Spawnable;

use pocketmine\utils\Color;

use pocketmine\nbt\tag\{
	CompoundTag,
	IntTag,
	LongTag,
	ShortTag,
	ByteTag
};

class Cauldron extends Spawnable {
	public const TAG_POTION_ID = "PotionId";
	public const TAG_SPLASH_POTION = "SplashPotion";
	public const TAG_CUSTOM_COLOR = "CustomColor";

	protected $potionID = -1;

	protected $splashPotion = false;
	/** @var Color */
	protected $customColor = null;

	public function isSplashPotion(): bool{
		return $this->splashPotion;
	}

	public function setSplashPotion(bool $splashPotion) : void {
		$this->splashPotion = $splashPotion;

		$this->onChanged();
	}

	public function getCustomColor() : ?Color {
		return $this->customColor;
	}

	public function setCustomColor(Color $customColor) : void {
		$this->customColor = $customColor;
		$this->onChanged();
	}

	public function resetCustomColor() : void {
		$this->customColor = null;

		$this->onChanged();
	}

	public function resetPotion() : void {
		$this->setPotionID(-1);
	}

	public function hasCustomColor() : bool {
		return $this->customColor instanceof Color;
	}

	public function hasPotion() : bool {
		return $this->getPotionID() != -1;
	}

	public function getPotionID() : int {
		return $this->potionID;
	}

	public function setPotionID(int $potionID) : void {
		$this->potionID = $potionID;
		$this->onChanged();
	}

	protected function writeSaveData(CompoundTag $nbt) : void {
		$this->applyBaseNBT($nbt);
	}

	private function applyBaseNBT(CompoundTag $nbt) : void {
		$nbt->setShort(self::TAG_POTION_ID, $this->potionID);
		$nbt->setByte(self::TAG_SPLASH_POTION, (int)$this->splashPotion);
		if($this->customColor instanceof Color){
			$nbt->setInt(self::TAG_CUSTOM_COLOR, $this->customColor->toARGB());
		}else{
			if($nbt->hasTag(self::TAG_CUSTOM_COLOR, IntTag::class)){
				$nbt->removeTag(self::TAG_CUSTOM_COLOR);
			}
		}
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void {
		$this->applyBaseNBT($nbt);
	}

	protected function readSaveData(CompoundTag $nbt) : void {
		if($nbt->hasTag(self::TAG_POTION_ID, LongTag::class)) {
			$this->potionID = $nbt->getLong(self::TAG_POTION_ID, $this->potionID);

			$nbt->removeTag(self::TAG_POTION_ID);
		}

		if(!$nbt->hasTag(self::TAG_POTION_ID, ShortTag::class)) {
			$nbt->setShort(self::TAG_POTION_ID, $this->potionID);
		}
		$this->potionID = $nbt->getShort(self::TAG_POTION_ID, $this->potionID);

		if(!$nbt->hasTag(self::TAG_SPLASH_POTION, ByteTag::class)) {
			$nbt->setByte(self::TAG_SPLASH_POTION, (int) $this->splashPotion);
		}
		$this->splashPotion = (bool) $nbt->getByte(self::TAG_SPLASH_POTION, (int) $this->splashPotion);

		if($nbt->hasTag(self::TAG_CUSTOM_COLOR, IntTag::class)) {
			$this->customColor = Color::fromARGB($nbt->getInt(self::TAG_CUSTOM_COLOR));
		}
	}
}