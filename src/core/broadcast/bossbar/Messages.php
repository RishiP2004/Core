<?php

declare(strict_types = 1);

namespace core\broadcast\bossbar;

use pocketmine\utils\TextFormat;

interface Messages {
    const MODE = 0;

    const HEAD_MESSAGE = "{PREFIX}";
    const NOT_REGISTERED_MESSAGE = "{PREFIX}";

    const CHANGING = [
        "time" => 45,
        "messages" => [
            "{0%}" . TextFormat::GRAY . "Better than the rest!",
            "{25%}" . TextFormat::GRAY . "Coming in 2022!",
            "{50%}" . TextFormat::GRAY . "Testing Stage!",
            "{75%}" . TextFormat::GRAY . "Woops!",
            "{100%}" . TextFormat::GRAY . "BETA!"
        ],
    ];
    const WORLDS = [
	"spawn"
    ];
}