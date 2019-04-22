<?php

namespace core\mcpe\item;

use core\mcpe\entity\projectile\Firework;

use pocketmine\item\Item;

use pocketmine\Player;

use pocketmine\block\Block;

use pocketmine\math\Vector3;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\utils\Random;

use pocketmine\entity\Entity;

use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

use pocketmine\event\entity\EntityDamageEvent;

class Fireworks extends Item {
	public const TAG_FIREWORKS = "Fireworks";
	public const TAG_EXPLOSIONS = "Explosions";
	public const TAG_FLIGHT = "Flight";

	public $spread = 5.0;

	public function __construct($meta = 0) {
		parent::__construct(Item::FIREWORKS, $meta, "Fireworks");
	}

	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : bool {
		if($this->getNamedTag()->hasTag(self::TAG_FIREWORKS, CompoundTag::class)) {
			$random = new Random();
			$yaw = $random->nextBoundedInt(360);
			$pitch = -1 * (float) (90 + ($random->nextFloat() * $this->spread - $this->spread / 2));
			$nbt = Entity::createBaseNBT($blockReplace->add(0.5, 0, 0.5), null, $yaw, $pitch);
			$tags = $this->getNamedTagEntry(self::TAG_FIREWORKS);

			if(!is_null($tags)) {
				$nbt->setTag($tags);
			}
			$level = $player->getLevel();
			$rocket = new Firework($level, $nbt, $player, $this, $random);

			$level->addEntity($rocket);

			if($rocket instanceof Entity) {
				if($player->isSurvival()) {
					--$this->count;
				}
				$rocket->spawnToAll();
				return true;
			}
		}
		return false;
	}

	public function onClickAir(Player $player, Vector3 $directionVector): bool{
		if(!$player->isOnGround()) {
			if($player->getGamemode() !== Player::CREATIVE && $player->getGamemode() !== Player::SPECTATOR) {
				$this->pop();
			}
			$damage = 0;
			$flight = 1;

			if($this->getNamedTag()->hasTag(self::TAG_FIREWORKS, CompoundTag::class)) {
				$fwNBT = $this->getNamedTag()->getCompoundTag(self::TAG_FIREWORKS);
				$flight = $fwNBT->getByte(self::TAG_FLIGHT);
				$explosions = $fwNBT->getListTag(self::TAG_EXPLOSIONS);

				if(count($explosions) > 0) {
					$damage = 7;
				}
			}
			$dir = $player->getDirectionVector();

			$player->setMotion($dir->multiply($flight * 1.25));
			$player->getLevel()->broadcastLevelSoundEvent($player->asVector3(), LevelSoundEventPacket::SOUND_LAUNCH);

			if($damage > 0) {
				$ev = new EntityDamageEvent($player, EntityDamageEvent::CAUSE_CUSTOM, 7);

				$player->attack($ev);
			}
		}
		return true;
	}
}