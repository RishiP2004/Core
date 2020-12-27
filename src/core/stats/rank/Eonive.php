<?php

declare(strict_types = 1);

namespace core\stats\rank;

use pocketmine\utils\TextFormat;

class Eonive extends Rank {
    public function __construct() {
        parent::__construct("Eonive");

        $this->setPaidPrice(5);
    }

	public function getFormat() : string {
		return TextFormat::BOLD . TextFormat::AQUA . "EONIVE" . TextFormat::RESET;
	}

	public function getChatFormat() : string {
		return $this->getFormat() . "{DISPLAY_NAME}" . TextFormat::LIGHT_PURPLE . ": " . TextFormat::GRAY . "{MESSAGE}";
	}

	public function getNameTagFormat() : string {
		return $this->getFormat() . "{DISPLAY_NAME}";
	}

	public function getPermissions() : array {
		return [
			"lobby.essentials.staffpuncher",
			"core.essentials.command.chat.vip"
		];
	}

    public function getInheritance() : Rank {
        return new Hexcite();
    }

    public function getValue() : int {
        return self::PAID;
    }

    public function getChatTime() : float {
    	return 2;
    }
}