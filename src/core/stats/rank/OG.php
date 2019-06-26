<?php

declare(strict_types = 1);

namespace core\stats\rank;

use pocketmine\utils\TextFormat;

class OG extends Rank {
    public function __construct() {
        parent::__construct("OG");

        $this->setFreePrice(10000);
    }

	public function getFormat() : string {
		return TextFormat::BOLD . TextFormat::GOLD . "OG" . TextFormat::RESET;
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
		return new Hexcite();
	}

	public function getValue() : int {
		return self::FREE;
	}

	public function getChatTime() : float {
		return 1;
	}
}