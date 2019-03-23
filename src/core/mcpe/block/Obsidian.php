<?php

namespace core\mcpe\block;

use pocketmine\math\Vector3;

use pocketmine\item\Item;

use pocketmine\Player;

use pocketmine\block\Block;

class Obsidian extends \pocketmine\block\Obsidian {
    protected $id = self::OBSIDIAN;
    /** @var Vector3 */
    private $temporalVector = null;

    public function __construct($meta = 0) {
        $this->meta = $meta;
        $this->temporalVector = new Vector3(0, 0, 0);
    }

    public function onBreak(Item $item, Player $player = null) : bool {
        parent::onBreak($item);

        for($i = 0; $i <= 6; $i++) {
            if($this->getSide($i)->getId() == self::PORTAL) {
                break;
            }
            if($i === 6) {
                return false;
            }
        }
        $block = $this->getSide($i);

        if($this->getLevel()->getBlock($this->temporalVector->setComponents($block->x - 1, $block->y, $block->z))->getId() === Block::PORTAL or $this->getLevel()->getBlock($this->temporalVector->setComponents($block->x + 1, $block->y, $block->z))->getId() === Block::PORTAL) {
            for($x = $block->x; $this->getLevel()->getBlock($this->temporalVector->setComponents($x, $block->y, $block->z))->getId() === Block::PORTAL; $x++) {
                for($y = $block->y; $this->getLevel()->getBlock($this->temporalVector->setComponents($x, $y, $block->z))->getId() === Block::PORTAL; $y++) {
                    $this->getLevel()->setBlock($this->temporalVector->setComponents($x, $y, $block->z), new Block(0));
                }
                for($y = $block->y - 1; $this->getLevel()->getBlock($this->temporalVector->setComponents($x, $y, $block->z))->getId() === Block::PORTAL; $y--) {
                    $this->getLevel()->setBlock($this->temporalVector->setComponents($x, $y, $block->z), new Block(0));
                }
            }
            for($x = $block->x - 1; $this->getLevel()->getBlock($this->temporalVector->setComponents($x, $block->y, $block->z))->getId() === Block::PORTAL; $x--) {
                for($y = $block->y; $this->getLevel()->getBlock($this->temporalVector->setComponents($x, $y, $block->z))->getId() === Block::PORTAL; $y++) {
                    $this->getLevel()->setBlock($this->temporalVector->setComponents($x, $y, $block->z), new Block(0));
                }
                for($y = $block->y - 1; $this->getLevel()->getBlock($this->temporalVector->setComponents($x, $y, $block->z))->getId() === Block::PORTAL; $y--) {
                    $this->getLevel()->setBlock($this->temporalVector->setComponents($x, $y, $block->z), new Block(0));
                }
            }
        } else {
            for($z = $block->z; $this->getLevel()->getBlock($this->temporalVector->setComponents($block->x, $block->y, $z))->getId() === Block::PORTAL; $z++) {
                for($y = $block->y; $this->getLevel()->getBlock($this->temporalVector->setComponents($block->x, $y, $z))->getId() === Block::PORTAL; $y++) {
                    $this->getLevel()->setBlock($this->temporalVector->setComponents($block->x, $y, $z), new Block(0));
                }
                for($y = $block->y - 1; $this->getLevel()->getBlock($this->temporalVector->setComponents($block->x, $y, $z))->getId() === Block::PORTAL; $y--) {
                    $this->getLevel()->setBlock($this->temporalVector->setComponents($block->x, $y, $z), new Block(0));
                }
            }
            for($z = $block->z - 1; $this->getLevel()->getBlock($this->temporalVector->setComponents($block->x, $block->y, $z))->getId() === Block::PORTAL; $z--) {
                for($y = $block->y; $this->getLevel()->getBlock($this->temporalVector->setComponents($block->x, $y, $z))->getId() === Block::PORTAL; $y++) {
                    $this->getLevel()->setBlock($this->temporalVector->setComponents($block->x, $y, $z), new Block(0));
                }
                for($y = $block->y - 1; $this->getLevel()->getBlock($this->temporalVector->setComponents($block->x, $y, $z))->getId() === Block::PORTAL; $y--) {
                    $this->getLevel()->setBlock($this->temporalVector->setComponents($block->x, $y, $z), new Block(0));
                }
            }
        }
        return true;
    }
}