<?php

declare(strict_types = 1);

namespace core\anticheat;

interface Cheats {
    const MAX_CONCURRENT_EXPLOSIONS = 4;
    const AUTO_CLICK_AMOUNT = 40;

    const LAG_CLEAR_TIME = [
        "hours" => 2,
        "minutes" => 30
    ];

    const MAX_ENTITIES = [
    	"animals" => 60,
		"monsters" => 60,
		"items" => 300
	];
}