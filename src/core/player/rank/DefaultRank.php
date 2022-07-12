<?php

declare(strict_types = 1);

namespace core\player\rank;

use core\player\CorePlayer;

use pocketmine\utils\TextFormat;

class DefaultRank {
	public const PLAYER = 0;

    public const DEFAULT_PERMISSIONS = [];

    protected string $basicTagFormat;

   //protected string $factionTagSuffix;

    public function __construct() {
        $this->basicTagFormat = "{color} {player}";

        //$this->factionTagSuffix = "\n§4[§6{faction} §4■§4]";
    }

    public function getName() : string {
    	return "Player";
	}

	public function getIdentifier() : int {
		return self::PLAYER;
	}
	
	public function getColor() {
    	return TextFormat::WHITE;
	}

	public function getFormat() : string {
		return TextFormat::WHITE . "Player";
	}

	public function getChatFormat() : string {
        return TextFormat::GRAY . "[" . $this->getFormat() . TextFormat::GRAY . "]" . TextFormat::RESET .  "{DISPLAY_NAME}" . TextFormat::LIGHT_PURPLE . ": " . TextFormat::GRAY . "{MESSAGE}";
    }

	public function getNameTagFormat() : string {
		return TextFormat::GRAY . "[" . $this->getFormat() . TextFormat::GRAY . "]" . TextFormat::RESET .  "{DISPLAY_NAME}";
	}

	public function getValue() : int {
		return self::PLAYER;
	}

    public function getChatFormatFor(CorePlayer $from, string $message, array $args = []) : string {
		return str_replace(["{DISPLAY_NAME}", "{MESSAGE}"], [$from->getNameTag(), TextFormat::clean($message)], $this->getChatFormat());
    }

    public function getNameTagFormatFor(CorePlayer $player) : string {
		return str_replace(["{player}", "{color}"], [$player->getName(), $this->getFormat()], $this->basicTagFormat);
    }

    public function getPermissions() : array {
        return self::DEFAULT_PERMISSIONS;
    }

    public function getChatTime() : float {
    	return 3.0;
	}
}
