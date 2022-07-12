<?php

declare(strict_types = 1);

namespace core\utils;

use pocketmine\world\Position;

use pocketmine\math\Vector3;

use pocketmine\network\mcpe\protocol\{
	AddActorPacket,
	ActorEventPacket
};
use pocketmine\world\World;

final class WorldUtils {
	/** @var int[] $chunkCounter */
	public static $chunkCounter = [];

    public static function getExplosionAffectedBlocks(Position $center, float $size) {
        if($size < 0.1) {
            return false;
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
                            $block = $center->getWorld()->getBlock($vBlock);

                            if($block->getId() !== 0) {
                                if($blastForce > 0) {
                                    $blastForce -= ($block->getBlastResistance() / 5 + 0.3) * $stepLen;

                                    if(!isset($affectedBlocks[$index = World::blockHash($block->x, $block->y, $block->z)])) {
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