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
	
    public function getExplosionAffectedBlocks(Position $center, float $size) {
        if($size < 0.1) {
            return;
        }
        $affectedBlocks = [];
        $rays = 16;
        $stepLen = 0.3;
        $vector = new Vector3(0, 0, 0);
        $vBlock = new Vector3(0, 0, 0);
        $mRays = intval($rays - 1);

        for($i = 0; $i < $rays; ++$i) {
            for($j = 0; $j < $rays; ++$j) {
                for($k = 0; $k < $rays; ++$k) {
                    if($i === 0 or $i === $mRays or $j === 0 or $j === $mRays or $k === 0 or $k === $mRays) {
                        $vector->setComponents($i / $mRays * 2 - 1, $j / $mRays * 2 - 1, $k / $mRays * 2 - 1);
                        $vector->setComponents($vector->x / ($len = $vector->length()) * $stepLen, ($vector->y / $len) * $stepLen, ($vector->z / $len) * $stepLen);

                        $pointerX = $center->x;
                        $pointerY = $center->y;
                        $pointerZ = $center->z;

                        for($blastForce = $size * (mt_rand(700, 1300) / 1000); $blastForce > 0; $blastForce -= $stepLen * 0.75) {
                            $x = $pointerX;
                            $y = $pointerY;
                            $z = $pointerZ;
                            $vBlock->x = $pointerX >= $x ? $x : $x - 1;
                            $vBlock->y = $pointerY >= $y ? $y : $y - 1;
                            $vBlock->z = $pointerZ >= $z ? $z : $z - 1;

                            if($vBlock->y < 0 or $vBlock->y > 127) {
                                break;
                            }
                            $block = $center->level->getBlock($vBlock);

                            if($block->getId() !== 0) {
                                if($blastForce > 0) {
                                    $blastForce -= ($block->getBlastResistance() / 5 + 0.3) * $stepLen;

                                    if(!isset($affectedBlocks[$index = Level::blockHash($block->x, $block->y, $block->z)])) {
                                        $affectedBlocks[$index] = $block;
                                    }
                                }
                            }
                            $pointerX += $vector->x;
                            $pointerY += $vector->y;
                            $pointerZ += $vector->z;
                        }
                    }
                }
            }
        }
        return $affectedBlocks;
    }
}