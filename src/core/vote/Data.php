<?php

declare(strict_types = 1);

namespace core\vote;

interface Data {
    const API_KEY = "";
    
    const ITEMS_PER_VOTE = 10;
    
    const ITEMS = [
		"57:0:5",
		"264:0:32",
		"266:0:64",
		"42:0:10",
		"339:3000:1"
    ];
    const COMMANDS = [
        "kit Vote {PLAYER}",
		"givecoins {PLAYER} 10"
    ];
}