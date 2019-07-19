<?php

declare(strict_types = 1);

namespace core\mcpe\item;

use core\Core;
use core\CorePlayer;

use core\mcpe\entity\projectile\Firework;

use core\mcpe\task\ElytraRocketBoostTracking;

use pocketmine\Player;

use pocketmine\item\Item;

use pocketmine\block\Block;

use pocketmine\math\Vector3;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\utils\Random;

use pocketmine\entity\Entity;

use pocketmine\event\entity\EntityDamageEvent;

use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

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
			$pitch = -1 * (float)(90 + ($random->nextFloat() * $this->spread - $this->spread / 2));
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
		$player = Core::getInstance()->getServer()->getPlayer($player);
		
		if($player instanceof CorePlayer) {
			if($player->usingElytra && !$player->isOnGround()) {
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
					
				Core::getInstance()->getScheduler()->scheduleRepeatingTask(new ElytraRocketBoostTracking($player, 6), 4);

				if($damage > 0) {
					$ev = new EntityDamageEvent($player, EntityDamageEvent::CAUSE_CUSTOM, 7); 
					
					$player->attack($ev);
				}
			}
		}
		return true;
	}
}