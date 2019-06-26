<?php

declare(strict_types = 1);

namespace core\stats\rank;

use pocketmine\utils\TextFormat;

class Athener extends Rank {
    public function __construct() {
        parent::__construct("Athener");

        $this->setPaidPrice(25);
    }

    public function getFormat() : string {
		return TextFormat::BOLD . TextFormat::BLUE . "ATHENER" . TextFormat::RESET;
    }

    public function getChatFormat() : string {
		return $this->getFormat() . "{DISPLAY_NAME}" . TextFormat::LIGHT_PURPLE . ": " . TextFormat::GRAY . "{MESSAGE}";
    }

    public function getNameTagFormat() : string {
		return $this->getFormat() . "{DISPLAY_NAME}";
    }

    public function getPermissions() : array {
    	return [];
    }

    public function getInheritance() : Rank {
        return new Pixelated();
    }

    public function getValue() : int {
        return self::PAID;
    }

    public function getChatTime() : float {
    	return 0;
    }
}