<?php

namespace core\network;

interface Networking {
    const COUNTDOWN_START = 10;
    const INTERVAL = 90;

    const MEMORY_LIMIT = "1200P";
    const COUNTDOWN_TYPE = Network::CHAT;

    const RESTART_ON_OVERLOAD = true;

    const MESSAGES = [
        "Broadcast" => "{PREFIX}Server will restart in {FORMATTED_TIME}",
        "Countdown" => "{PREFIX}Server restarting in {SECOND} seconds...",
    ];

    const SERVER_BACKUP = 180;
}
