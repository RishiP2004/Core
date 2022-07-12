<?php

declare(strict_types = 1);

namespace core\world;

use core\Core;

use core\utils\Manager;

use core\world\area\Area;

use pocketmine\world\Position;

class WorldManager extends Manager {
    public static ?self $instance = null;

    public array $areas = [];

    public array $players = [], $muted = [];

    public function init() : void {
    	self::$instance = $this;

		$this->registerListener(new WorldListener($this), Core::getInstance());
	}

    public static function getInstance() : self {
    	return self::$instance;
	}

	public function initArea(Area $area) : void {
        $this->areas[$area->getName()] = $area;
    }
    /**
     * @return Area[]
     */
    public function getAreas() : array {
        return $this->areas;
    }

    public function getArea(string $area) : ?Area {
        $lowerKeys = array_change_key_case($this->areas, CASE_LOWER);

        if(isset($lowerKeys[strtolower($area)])) {
            return $lowerKeys[strtolower($area)];
        }
        return null;
    }

    public function getAreaFromPosition(Position $position) : ?Area {
		foreach($this->getAreas() as $name => $area) {
            if($area->getPosition1()->getWorld() === $position->getWorld()) {
                $area1 = $area->getPosition1();
                $area2 = $area->getPosition2();
                $x = array_flip(range($area1->getX(), $area2->getX()));

                if(isset($x[$position->getX()])) {
                    $y = array_flip(range($area1->getY(), $area2->getY()));

                    if(isset($y[$position->getY()])) {
                        $z = array_flip(range($area1->getZ(), $area2->getZ()));

                        if(isset($z[$position->getZ()])) {
                            return $this->getArea($name);
                        }
                    }
                }
            }
        }
        return null;
    }
}