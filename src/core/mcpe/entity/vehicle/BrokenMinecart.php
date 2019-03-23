<?php

namespace core\mcpe\entity\vehicle;

use core\utils\{
    Orientation,
    Math
};

use pocketmine\math\Vector3;

use pocketmine\Player;

use pocketmine\item\Item;

use pocketmine\block\{
    Block,
    Rail
};

class BrokenMinecart extends Vehicle {
    const NETWORK_ID = self::MINECART;

    const TYPE_NORMAL = 1;
    const TYPE_CHEST = 2;
    const TYPE_HOPPER = 3;
    const TYPE_TNT = 4;

    const STATE_INITIAL = 0;
    const STATE_ON_RAIL = 1;
    const STATE_OFF_RAIL = 2;

    public $height = 0.7;
    public $width = 0.98;
    public $drag = 0.1;
    public $gravity = 0.5;
    public $isMoving = false;
    public $moveSpeed = 0.4;

    private $state = BrokenMinecart::STATE_INITIAL;
    private $direction = -1;
    private $moveVector = [];

    public function initEntity() : void {
        $this->setMaxHealth(1);
        $this->setHealth($this->getMaxHealth());

        $this->moveVector[Vector3::SIDE_NORTH] = new Vector3(-1, 0, 0);
        $this->moveVector[Vector3::SIDE_SOUTH] = new Vector3(1, 0, 0);
        $this->moveVector[Vector3::SIDE_EAST] = new Vector3(0, 0, -1);
        $this->moveVector[Vector3::SIDE_WEST] = new Vector3(0, 0, 1);

        parent::initEntity();
    }

    public function getName() : string {
        return "Minecart";
    }

    public function getType() : int {
        return self::TYPE_NORMAL;
    }

    public function onInteract(Player $player, Item $item, int $slot, Vector3 $clickPos) : bool {
        if($this->linkedEntity !== null) {
            return false;
        }
        return parent::mountEntity($player);
    }

    public function onUpdate($currentTick) : bool {
        if($this->closed) {
            return false;
        }
        $tickDiff = $currentTick - $this->lastUpdate;

        if($tickDiff <= 1) {
            return false;
        }
        $this->lastUpdate = $currentTick;

        $this->timings->startTiming();

        parent::onUpdate($currentTick);

        if($this->isAlive()) {
            $player = $this->getLinkedEntity();

            if($player instanceof Player) {
                if($this->state === BrokenMinecart::STATE_INITIAL){
                    $this->checkIfOnRail();
                } else if($this->state === BrokenMinecart::STATE_ON_RAIL) {
                    $this->forwardOnRail($player);
                    $this->updateMovement();
                }
            }
        }
        $this->timings->stopTiming();
        return true;
    }

    private function checkIfOnRail() {
        for($y = -1; $y !== 2 and $this->state === BrokenMinecart::STATE_INITIAL; $y++) {
            $positionToCheck = $this->temporalVector->setComponents($this->x, $this->y + $y, $this->z);
            $block = $this->level->getBlock($positionToCheck);

            if($this->isRail($block)) {
                $minecartPosition = $positionToCheck->floor()->add(0.5, 0, 0.5);

                $this->setPosition($minecartPosition);
                $this->state = BrokenMinecart::STATE_ON_RAIL;
            }
        }
        if($this->state !== BrokenMinecart::STATE_ON_RAIL){
            $this->state = BrokenMinecart::STATE_OFF_RAIL;
        }
    }

    private function isRail(Block $rail) {
        return ($rail !== null and in_array($rail->getId(), [Block::RAIL, Block::ACTIVATOR_RAIL, Block::DETECTOR_RAIL, Block::POWERED_RAIL]));
    }

    private function forwardOnRail(Player $player) {
        if($this->direction === -1) {
            $candidateDirection = $player->getDirection();
        } else {
            $candidateDirection = $this->direction;
        }
        $rail = $this->getCurrentRail();

        if($rail !== null) {
            $railType = $rail->getDamage();
            $nextDirection = $this->getDirectionToMove($railType, $candidateDirection);

            if($nextDirection !== -1) {
                $this->direction = $nextDirection;
                $moved = $this->checkForVertical($railType, $nextDirection);

                if(!$moved) {
                    return $this->moveIfRail();
                } else {
                    return true;
                }
            } else {
                $this->direction = -1;
            }
        } else {
            $this->state = BrokenMinecart::STATE_INITIAL;
        }
        return false;
    }

    private function getCurrentRail() {
        $block = $this->getLevel()->getBlock($this);

        if($this->isRail($block)) {
            return $block;
        }
        $down = $this->temporalVector->setComponents($this->x, $this->y - 1, $this->z);
        $block = $this->getLevel()->getBlock($down);

        if($this->isRail($block)) {
            return $block;
        }
        return null;
    }

    private function getDirectionToMove($railType, $candidateDirection){
        switch($railType) {
            case Rail::STRAIGHT_NORTH_SOUTH:
            case Orientation::ASCENDING_NORTH:
            case Orientation::ASCENDING_SOUTH:
                switch($candidateDirection) {
                    case Vector3::SIDE_NORTH:
                    case Vector3::SIDE_SOUTH:
                        return $candidateDirection;
                }
            break;
            case Orientation::STRAIGHT_EAST_WEST:
            case Orientation::ASCENDING_EAST:
            case Orientation::ASCENDING_WEST:
                switch($candidateDirection){
                    case Vector3::SIDE_WEST:
                    case Vector3::SIDE_EAST:
                        return $candidateDirection;
                }
            break;
            case Orientation::CURVED_SOUTH_EAST:
                switch($candidateDirection){
                    case Vector3::SIDE_SOUTH:
                    case Vector3::SIDE_EAST:
                        return $candidateDirection;
                    case Vector3::SIDE_NORTH:
                        return $this->checkForTurn($candidateDirection, Vector3::SIDE_EAST);
                    case Vector3::SIDE_WEST:
                        return $this->checkForTurn($candidateDirection, Vector3::SIDE_SOUTH);
                }
            break;
            case Orientation::CURVED_SOUTH_WEST:
                switch($candidateDirection){
                    case Vector3::SIDE_SOUTH:
                    case Vector3::SIDE_WEST:
                        return $candidateDirection;
                    case Vector3::SIDE_NORTH:
                        return $this->checkForTurn($candidateDirection, Vector3::SIDE_WEST);
                    case Vector3::SIDE_EAST:
                        return $this->checkForTurn($candidateDirection, Vector3::SIDE_SOUTH);
                }
            break;
            case Orientation::CURVED_NORTH_WEST:
                switch($candidateDirection){
                    case Vector3::SIDE_NORTH:
                    case Vector3::SIDE_WEST:
                        return $candidateDirection;
                    case Vector3::SIDE_SOUTH:
                        return $this->checkForTurn($candidateDirection, Vector3::SIDE_WEST);
                    case Vector3::SIDE_EAST:
                        return $this->checkForTurn($candidateDirection, Vector3::SIDE_NORTH);
                }
            break;
            case Orientation::CURVED_NORTH_EAST:
                switch($candidateDirection){
                    case Vector3::SIDE_NORTH:
                    case Vector3::SIDE_EAST:
                        return $candidateDirection;
                    case Vector3::SIDE_SOUTH:
                        return $this->checkForTurn($candidateDirection, Vector3::SIDE_EAST);
                    case Vector3::SIDE_WEST:
                        return $this->checkForTurn($candidateDirection, Vector3::SIDE_NORTH);
                }
            break;
        }
        return -1;
    }

    private function checkForTurn($currentDirection, $newDirection) {
        switch($currentDirection) {
            case Vector3::SIDE_NORTH:
                $diff = $this->x - $this->getFloorX();

                if($diff !== 0 and $diff <= .5){
                    $dx = ($this->getFloorX() + .5) - $this->x;

                    $this->move($dx, 0, 0);
                    return $newDirection;
                }
            break;
            case Vector3::SIDE_SOUTH:
                $diff = $this->x - $this->getFloorX();
                if($diff !== 0 and $diff >= .5){
                    $dx = ($this->getFloorX() + .5) - $this->x;

                    $this->move($dx, 0, 0);
                    return $newDirection;
                }
            break;
            case Vector3::SIDE_EAST:
                $diff = $this->z - $this->getFloorZ();

                if($diff !== 0 and $diff <= .5){
                    $dz = ($this->getFloorZ() + .5) - $this->z;

                    $this->move(0, 0, $dz);
                    return $newDirection;
                }
            break;
            case Vector3::SIDE_WEST:
                $diff = $this->z - $this->getFloorZ();

                if($diff !== 0 and $diff >= .5){
                    $dz = $dz = ($this->getFloorZ() + .5) - $this->z;

                    $this->move(0, 0, $dz);
                    return $newDirection;
                }
            break;
        }
        return $currentDirection;
    }

    private function checkForVertical($railType, $currentDirection) {
        switch($railType){
            case Orientation::ASCENDING_NORTH:
                switch($currentDirection){
                    case Vector3::SIDE_NORTH:
                        $diff = $this->x - $this->getFloorX();

                        if($diff !== 0 and $diff <= .5) {
                            $dx = ($this->getFloorX() - .1) - $this->x;

                            $this->move($dx, 1, 0);
                            return true;
                        }
                    break;
                    case Vector3::SIDE_SOUTH:
                        $diff = $this->x - $this->getFloorX();

                        if($diff !== 0 and $diff >= .5) {
                            $dx = ($this->getFloorX() + 1) - $this->x;

                            $this->move($dx, -1, 0);
                            return true;
                        }
                    break;
                }
                break;
            case Orientation::ASCENDING_SOUTH:
                switch($currentDirection) {
                    case Vector3::SIDE_SOUTH:
                        $diff = $this->x - $this->getFloorX();

                        if($diff !== 0 and $diff >= .5) {
                            $dx = ($this->getFloorX() + 1) - $this->x;

                            $this->move($dx, 1, 0);
                            return true;
                        }
                    break;
                    case Vector3::SIDE_NORTH:
                        $diff = $this->x - $this->getFloorX();

                        if($diff !== 0 and $diff <= .5) {
                            $dx = ($this->getFloorX() - .1) - $this->x;

                            $this->move($dx, -1, 0);
                            return true;
                        }
                    break;
                }
                break;
            case Orientation::ASCENDING_EAST:
                switch($currentDirection) {
                    case Vector3::SIDE_EAST:
                        $diff = $this->z - $this->getFloorZ();

                        if($diff !== 0 and $diff <= .5) {
                            $dz = ($this->getFloorZ() - .1) - $this->z;

                            $this->move(0, 1, $dz);
                            return true;
                        }
                    break;
                    case Vector3::SIDE_WEST:
                        $diff = $this->z - $this->getFloorZ();

                        if($diff !== 0 and $diff >= .5) {
                            $dz = ($this->getFloorZ() + 1) - $this->z;

                            $this->move(0, -1, $dz);
                            return true;
                        }
                    break;
                }
                break;
            case Orientation::ASCENDING_WEST:
                switch($currentDirection) {
                    case Vector3::SIDE_WEST:
                        $diff = $this->z - $this->getFloorZ();

                        if($diff !== 0 and $diff >= .5){
                            $dz = ($this->getFloorZ() + 1) - $this->z;

                            $this->move(0, 1, $dz);
                            return true;
                        }
                    break;
                    case Vector3::SIDE_EAST:
                        $diff = $this->z - $this->getFloorZ();

                        if($diff !== 0 and $diff <= .5) {
                            $dz = ($this->getFloorZ() - .1) - $this->z;

                            $this->move(0, -1, $dz);
                            return true;
                        }
                    break;
                }
            break;
        }
        return false;
    }

    private function moveIfRail() {
        $nextMoveVector = $this->moveVector[$this->direction];
        $nextMoveVector = $nextMoveVector->multiply($this->moveSpeed);
        $newVector = $this->add($nextMoveVector->x, $nextMoveVector->y, $nextMoveVector->z);
        $possibleRail = $this->getCurrentRail();

        if(in_array($possibleRail->getId(), [Block::RAIL, Block::ACTIVATOR_RAIL, Block::DETECTOR_RAIL, Block::POWERED_RAIL])){
            $this->moveUsingVector($newVector);
            return true;
        }
        return false;
    }

    private function moveUsingVector(Vector3 $desiredPosition) {
        $dx = $desiredPosition->x - $this->x;
        $dy = $desiredPosition->y - $this->y;
        $dz = $desiredPosition->z - $this->z;

        $this->move($dx, $dy, $dz);
    }

    public function getNearestRail() : Rail {
        $minX = Math::floorFloat($this->boundingBox->minX);
        $minY = Math::floorFloat($this->boundingBox->minY);
        $minZ = Math::floorFloat($this->boundingBox->minZ);
        $maxX = Math::ceilFloat($this->boundingBox->maxX);
        $maxY = Math::ceilFloat($this->boundingBox->maxY);
        $maxZ = Math::ceilFloat($this->boundingBox->maxZ);
        $rails = [];

        for($z = $minZ; $z <= $maxZ; ++$z) {
            for($x = $minX; $x <= $maxX; ++$x) {
                for($y = $minY; $y <= $maxY; ++$y) {
                    $block = $this->level->getBlock($this->temporalVector->setComponents($x, $y, $z));

                    if(in_array($block->getId(), [Block::RAIL, Block::ACTIVATOR_RAIL, Block::DETECTOR_RAIL, Block::POWERED_RAIL])) {
                        $rails[] = $block;
                    }
                }
            }
        }
        $minDistance = PHP_INT_MAX;
        $nearestRail = null;

        foreach($rails as $rail) {
            $dis = $this->distance($rail);

            if($dis < $minDistance) {
                $nearestRail = $rail;
                $minDistance = $dis;
            }
        }
        return $nearestRail;
    }
}