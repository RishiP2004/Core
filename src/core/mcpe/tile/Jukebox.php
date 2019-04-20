<?php

namespace core\mcpe\tile;

use core\Core;
use core\CorePlayer;

use core\mcpe\item\Record;

use pocketmine\tile\Spawnable;

use pocketmine\item\Item;

use pocketmine\nbt\tag\{
	CompoundTag,
	IntTag
};

use pocketmine\level\Level;

use pocketmine\math\Vector3;

use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

class Jukebox extends Spawnable {
	public const TAG_RECORD = "record";
	public const TAG_RECORD_ITEM = "recordItem";

	protected $record = 0;
	/** @var Item */
	protected $recordItem;

	private $loaded = false;
	/** @var CompoundTag */
	private $nbt;

	public function __construct(Level $level, CompoundTag $nbt) {
		parent::__construct($level, $nbt);

		if(!$nbt->hasTag(self::TAG_RECORD, IntTag::class)) {
			$nbt->setInt(self::TAG_RECORD, 0);
		}
		$this->record = $nbt->getInt(self::TAG_RECORD);

		if(!$nbt->hasTag(self::TAG_RECORD_ITEM, CompoundTag::class)) {
			$nbt->setTag((Item::get(Item::AIR, 0, 1))->nbtSerialize(-1, self::TAG_RECORD_ITEM));
		}
		$this->recordItem = Item::nbtDeserialize($nbt->getCompoundTag(self::TAG_RECORD_ITEM));
	}

	public function dropMusicDisc() {
		$this->getLevel()->dropItem($this->add(new Vector3(0.5, 0.5, 0.5)), new Item($this->getRecordItem()->getId()));

		$this->recordItem = Item::get(Item::AIR, 0, 1);

		$this->getLevel()->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_STOP_RECORD);
	}

	public function getRecordItem() : Item {
		return ($this->recordItem instanceof Item ? $this->recordItem : Item::get(Item::AIR, 0, 1));
	}

	public function setRecordItem(Record $disc) {
		$this->recordItem = $disc;
		$this->record = $disc->getRecordId();
	}

	public function setRecordId(int $recordId) {
		$this->record = $recordId;
	}

	public function onUpdate() : bool {
		if($this->recordItem instanceof Record && !$this->loaded) {
			$this->playMusicDisc();

			$this->loaded = true;
		}
		return true;
	}

	public function playMusicDisc() {
		$recordItem = $this->getRecordItem();

		if($recordItem instanceof Record) {
			if($recordItem->getSoundId() > 0) {
				$pk = new LevelSoundEventPacket();
				$pk->sound = $recordItem->getSoundId();
				$pk->position = $this->asVector3();

				$this->getLevel()->addChunkPacket($this->getX() >> 4, $this->getZ() >> 4, $pk);

				foreach($this->getLevel()->getEntities() as $entity) {
					if($entity->distance($this) <= 65) {
						if($entity instanceof CorePlayer) {
							$entity->sendPopup(Core::getInstance()->getPrefix() . "Now Playing : C418 - " . $recordItem->getRecordName());
						}
					}
				}
			}
		}
	}

	public function saveNBT() : CompoundTag {
		$this->getNBT()->setTag($this->getRecordItem()->nbtSerialize(-1, self::TAG_RECORD_ITEM));
		$this->getNBT()->setInt(self::TAG_RECORD, $this->getRecordId());
		return parent::saveNBT();
	}

	public function getNBT() : CompoundTag {
		return $this->nbt;
	}

	public function getRecordId() : int {
		return $this->record;
	}

	public function addAdditionalSpawnData(CompoundTag $nbt) : void {
		$nbt->setInt(self::TAG_RECORD, $this->getRecordId());

		$record = $this->getRecordItem() instanceof Item ? $this->getRecordItem() : Item::get(Item::AIR, 0, 1);

		$nbt->setTag($record->nbtSerialize(-1, self::TAG_RECORD_ITEM));
	}

	protected function readSaveData(CompoundTag $nbt) : void {
		$this->nbt = $nbt;
	}

	protected function writeSaveData(CompoundTag $nbt) : void {
		$nbt->setInt(self::TAG_RECORD, $this->getRecordId());

		$record = $this->getRecordItem() instanceof Item ? $this->getRecordItem() : Item::get(Item::AIR, 0, 1);

		$nbt->setTag($record->nbtSerialize(-1, self::TAG_RECORD_ITEM));
	}
}
