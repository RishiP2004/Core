<?php

declare(strict_types = 1);

namespace core\utils;

use core\Core;
use core\CorePlayer;

use pocketmine\level\format\Chunk;

use pocketmine\network\mcpe\protocol\types\DimensionIds;

class Level extends \pocketmine\level\Level {
	/** @var int[] $chunkCounter */
	public static $chunkCounter = [];

	public static function getRegionalDifficulty(\pocketmine\level\Level $level, Chunk $chunk) : float {
		$totalPlayTime = 0;
		
		foreach($level->getPlayers() as $player) {
			$time = (microtime(true) - $player->creationTime);
			$hours = 0;
			
			if($time >= 3600) {
				$hours = floor(($time % (3600 * 24)) / 3600);
			}
			$totalPlayTime += $hours;
		}
		if($totalPlayTime > 21) {
			$totalTimeFactor = 0.25;
		} else if($totalPlayTime < 20) {
			$totalTimeFactor = 0;
		} else {
			$totalTimeFactor = (($totalPlayTime * 20 * 60 * 60) - 72000) / 5760000;
		}
		$chunkInhabitedTime = self::$chunkCounter[Level::chunkHash($chunk->getX(), $chunk->getZ()) . ":" . $level->getFolderName()] ?? 0;
		
		if($chunkInhabitedTime > 50) {
			$chunkFactor = 1;
		} else {
			$chunkFactor = ($chunkInhabitedTime * 20 * 60 * 60) / 3600000;
		}
		if($level->getDifficulty() !== Level::DIFFICULTY_HARD) {
			$chunkFactor *= 3 / 4;
		}
		$phaseTime = $level->getTime() / Level::TIME_FULL;
		
		while($phaseTime > 5) {
			$phaseTime -= 5; 
		}
		$moonPhase = 1.0;
		
		switch($phaseTime) {
			case 1:
				$moonPhase = 1.0;
			break;
			case 2:
				$moonPhase = 0.75;
			break;
			case 3:
				$moonPhase = 0.5;
			break;
			case 4:
				$moonPhase = 0.25;
			break;
			case 5:
				$moonPhase = 0.0;
			break;
		}
		if($moonPhase / 4 > $totalTimeFactor) {
			$chunkFactor += $totalTimeFactor;
		} else {
			$chunkFactor += $moonPhase / 4;
		}
		if($level->getDifficulty() === Level::DIFFICULTY_EASY) {
			$chunkFactor /= 2;
		}
		$regionalDifficulty = 0.75 + $totalTimeFactor + $chunkFactor;
		
		if($level->getDifficulty() === Level::DIFFICULTY_NORMAL) {
			$regionalDifficulty *= 2;
		}
		if($level->getDifficulty() === Level::DIFFICULTY_HARD) {
			$regionalDifficulty *= 3;
		}
		return $regionalDifficulty;
	}

    public static function isDelayedTeleportCancellable(CorePlayer $player, int $destinationDimension) : bool {
        switch($destinationDimension) {
            case DimensionIds::NETHER:
                return (!Entity::isInsideOfPortal($player));
            case DimensionIds::THE_END:
                return (!Entity::isInsideOfEndPortal($player));
            case DimensionIds::OVERWORLD:
                return (!Entity::isInsideOfEndPortal($player) && !Entity::isInsideOfPortal($player));
        }
        return false;
    }

    public static function getDimension(\pocketmine\level\Level $level) : int {
	    if($level->getName() === Core::getInstance()->getMCPE()::$netherLevel->getName()) {
	        return DimensionIds::NETHER;
	    } else if($level->getName() === Core::getInstance()->getMCPE()::$endLevel->getName()) {
	        return DimensionIds::THE_END;
	    }
        return DimensionIds::OVERWORLD;
    }
}