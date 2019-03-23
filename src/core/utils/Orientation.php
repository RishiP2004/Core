<?php

namespace core\utils;

use pocketmine\block\Rail;

use pocketmine\math\Vector3;

class Orientation {
    const STRAIGHT = 0;
    const ASCENDING = 1;
    const CURVED = 2;

    private $meta = 0;
    private $state = 0;
    /** @var int[] */
    private $connectingDirections;
    /** @var int|null */
    private $ascendingDirection;

    private function __construct(int $meta, int $state, int $from, int $to, ?int $ascendingDirection) {
        $this->meta = $meta;
        $this->state = $state;
        $this->connectingDirections[$from] = $from;
        $this->connectingDirections[$to] = $to;
        $this->ascendingDirection = $ascendingDirection;
    }

    public static function getMetadata() : array {
        $railMetadata = [];
        $railMetadata[] = new Orientation(0, self::STRAIGHT, Vector3::SIDE_NORTH, Vector3::SIDE_SOUTH, null);
        $railMetadata[] = new Orientation(1, self::STRAIGHT, Vector3::SIDE_EAST, Vector3::SIDE_WEST, null);
        $railMetadata[] = new Orientation(2, self::ASCENDING, Vector3::SIDE_EAST, Vector3::SIDE_WEST, Vector3::SIDE_EAST);
        $railMetadata[] = new Orientation(3, self::ASCENDING, Vector3::SIDE_EAST, Vector3::SIDE_WEST, Vector3::SIDE_WEST);
        $railMetadata[] = new Orientation(4, self::ASCENDING, Vector3::SIDE_NORTH, Vector3::SIDE_SOUTH, Vector3::SIDE_NORTH);
        $railMetadata[] = new Orientation(5, self::ASCENDING, Vector3::SIDE_NORTH, Vector3::SIDE_SOUTH, Vector3::SIDE_SOUTH);
        $railMetadata[] = new Orientation(6, self::CURVED, Vector3::SIDE_SOUTH, Vector3::SIDE_EAST, null);
        $railMetadata[] = new Orientation(7, self::CURVED, Vector3::SIDE_SOUTH, Vector3::SIDE_WEST, null);
        $railMetadata[] = new Orientation(8, self::CURVED, Vector3::SIDE_NORTH, Vector3::SIDE_WEST, null);
        $railMetadata[] = new Orientation(9, self::CURVED, Vector3::SIDE_NORTH, Vector3::SIDE_EAST, null);
        return $railMetadata;
    }

    public static function byMetadata(int $meta) : Orientation {
        if($meta < 0 || $meta >= count(Rail::$railMetadata)) {
            $meta = 0;
        }
        return Rail::$railMetadata[$meta];
    }

    public static function getNormalRail(int $face) : Orientation {
        switch($face) {
            case Vector3::SIDE_NORTH:
            case Vector3::SIDE_SOUTH:
                return Rail::$railMetadata[Rail::STRAIGHT_NORTH_SOUTH];
            case Vector3::SIDE_EAST:
            case Vector3::SIDE_WEST:
                return Rail::$railMetadata[Rail::STRAIGHT_EAST_WEST];
        }
        return Rail::$railMetadata[Rail::STRAIGHT_NORTH_SOUTH];
    }

    public static function getAscendingData(int $face): Orientation{
        switch($face) {
            case Vector3::SIDE_NORTH:
                return Rail::$railMetadata[Rail::ASCENDING_NORTH];
            case Vector3::SIDE_SOUTH:
                return Rail::$railMetadata[Rail::ASCENDING_SOUTH];
            case Vector3::SIDE_EAST:
                return Rail::$railMetadata[Rail::ASCENDING_EAST];
            case Vector3::SIDE_WEST:
                return Rail::$railMetadata[Rail::ASCENDING_WEST];
        }
        return Rail::$railMetadata[Rail::ASCENDING_EAST];
    }

    public static function getCurvedState(int $face1, int $face2) : self {
        $origin = [
            Rail::CURVED_SOUTH_EAST,
            Rail::CURVED_SOUTH_WEST,
            Rail::CURVED_NORTH_WEST,
            Rail::CURVED_NORTH_EAST
        ];
        foreach($origin as $side) {
            $o = Rail::$railMetadata[$side];

            if(isset($o->connectingDirections[$face1]) && isset($o->connectingDirections[$face2])){
                return $o;
            }
        }
        return Rail::$railMetadata[Rail::CURVED_SOUTH_EAST];
    }

    public static function getConnectedState(int $face1, int $face2) : self {
        $origin = Orientation::getHorizontalRails();

        foreach($origin as $side) {
            $o = Rail::$railMetadata[$side];

            if(isset($o->connectingDirections[$face1]) && isset($o->connectingDirections[$face2])){
                return $o;
            }
        }
        return Rail::$railMetadata[Rail::STRAIGHT_NORTH_SOUTH];
    }

    public static function getHorizontalRails() : array {
        return [
            Rail::STRAIGHT_NORTH_SOUTH,
            Rail::STRAIGHT_EAST_WEST,
            Rail::CURVED_SOUTH_EAST,
            Rail::CURVED_SOUTH_WEST,
            Rail::CURVED_NORTH_WEST,
            Rail::CURVED_NORTH_EAST
        ];
    }

    public function getDamage() : int {
        return $this->meta;
    }

    public function hasConnectingDirections(int... $faces) : bool {
        foreach($faces as $direction) {
            if(!isset($this->connectingDirections[$direction])){
                return false;
            }
        }
        return true;
    }

    public function connectingDirections() : array {
        return $this->connectingDirections;
    }

    public function ascendingDirection() {
        return $this->ascendingDirection;
    }

    public function isStraight() : bool {
        return $this->state === self::STRAIGHT;
    }

    public function isAscending() : bool {
        return $this->state === self::ASCENDING;
    }

    public function isCurved() : bool {
        return $this->state === self::CURVED;
    }
}