<?php

declare(strict_types = 1);

namespace core\stats\rank;

use pocketmine\utils\TextFormat;

class Universal extends Rank {
    public function __construct() {
        parent::__construct("Universal");

        $this->setPaidPrice(10);
    }

	public function getFormat() : string {
		return TextFormat::BOLD . TextFormat::RED . "UNIVERSAL" . TextFormat::RESET;
	}

	public function getChatFormat() : string {
		return $this->getFormat() . "{DISPLAY_NAME}" . TextFormat::LIGHT_PURPLE . ": " . TextFormat::GRAY . "{MESSAGE}";
	}

	public function getNameTagFormat() : string {
		return $this->getFormat() . "{DISPLAY_NAME}";
	}

	public function getPermissions() : array {
		return [
			"core.stats.chat.time",
			"core.essentials.command.fly"
		];
	}

    public function getInheritance() : ?Rank {
        return new Eonive();
    }

    public function getValue() : int {
        return self::PAID;
    }

    public function getChatTime() : float {
    	return 1;
    }
}