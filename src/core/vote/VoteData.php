<?php

declare(strict_types = 1);

namespace core\vote;

interface VoteData {
    const API_KEY = "";

    const VOTE_UPDATE = 180;

	const TOP_VOTERS_LIMIT = 50;

    const ITEMS = [
		"57:0:5",
		"264:0:32",
		"266:0:64",
		"42:0:10",
		"339:3000:1"
    ];
    const COMMANDS = [
        "kit VoteManager {PLAYER}",
		"givecoins {PLAYER} 10"
    ];
}