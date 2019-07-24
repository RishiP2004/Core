<?php

declare(strict_types = 1);

namespace core\world;

use core\Core;

use core\world\area\{
    Area,
    Lobby,
    Factions,
    FactionsWarzone
};

use pocketmine\level\Position;

class World {
    private $core;

    public $areas = [];

    public $players = [], $muted = [];

    public function __construct(Core $core) {
        $this->core = $core;

        $this->initArea(new Lobby());
        $this->initArea(new Factions());
        $this->initArea(new FactionsWarzone());
		$core->getServer()->getPluginManager()->registerEvents(new WorldListener($core), $core);
    }

    public function initArea(Area $area) {
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
            if($area->getPosition1()->getLevel() === $position->getLevel()) {
                $area1 = $area->getPosition1();
                $area2 = $area->getPosition2();
                $x = array_flip(range($area1->getX(), $area2->getX()));

                if(isset($x[$position->x])) {
                    $y = array_flip(range($area1->getY(), $area2->getY()));

                    if(isset($y[$position->y])) {
                        $z = array_flip(range($area1->getZ(), $area2->getZ()));

                        if(isset($z[$position->z])) {
                            return $this->getArea($name);
                        }
                    }
                }
            }
        }
        return null;
    }
}