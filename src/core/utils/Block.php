<?php

namespace core\utils;

class Block extends \pocketmine\block\Block {
    public static function isRailBlock($block) : bool {
        if(is_null($block)) {
            return false;
        }
        $id = $block;

        if($block instanceof Block) {
            $id = $block->getId();
        }
        switch($id) {
            case Block::RAIL:
            case Block::POWERED_RAIL:
            case Block::ACTIVATOR_RAIL:
            case Block::DETECTOR_RAIL:
            return true;
            default:
            return false;
        }
    }
}