<?php

namespace core\stats\rank;

use pocketmine\utils\TextFormat;

class Administrator extends Rank {
    public function __construct() {
        parent::__construct("Administrator");
    }

    public function getChatFormat() : string {
        return TextFormat::BOLD . TextFormat::DARK_AQUA . "[ADMINISTRATOR] " . TextFormat::RESET . "{DISPLAY_NAME}" . TextFormat::GREEN . ": " . TextFormat::GRAY . "{MESSAGE}";
    }

    public function getNameTagFormat() : string {
        return TextFormat::BOLD . TextFormat::DARK_AQUA . "[ADMINISTRATOR] " . TextFormat::RESET . "{DISPLAY_NAME}";
    }

    public function getPermissions() : array {
        return [];
    }

    public function getInheritance() : Rank {
        return new Staff();
    }

    public function getValue() : int {
        return self::STAFF;
    }

    public function getChatTime() : int {
        return 0;
    }
}