<?php

declare(strict_types = 1);

namespace core\network;

interface Networking {
	const RESTART = 240;
	const COUNTDOWN_START = 10;
	const BROADCAST_INTERVAL = 20;

    const MEMORY_LIMIT = "1200P";
    const DISPLAY_TYPE = NetworkManager::CHAT;

    const RESTART_ON_OVERLOAD = true;

    const MESSAGES = [
        "broadcast" => "{PREFIX}Server will restart in {FORMATTED_TIME}",
        "countdown" => "{PREFIX}Server restarting in {SECOND} seconds...",
    ];
}
