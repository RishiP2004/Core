<?php

namespace core\stats;

interface Statistics {
    const BOUNDS_64_64 = 0;
    const BOUNDS_64_32 = self::BOUNDS_64_64;
    const BOUNDS_128_128 = 1;

    const DISABLE_CUSTOM_SKINS = false;
    const DISABLE_CUSTOM_CAPES = false;
    const DISABLE_CUSTOM_GEOMETRY = true;
    const DISABLE_INGAME_SKIN_CHANGE = true;
    const DISABLE_TRANSPARENT_SKINS = true;

    const ALLOWED_TRANSPARECNY_PERCENTAGE = 5;

    const AFK_AUTO_SET = 300;
    const AFK_AUTO_KICK = 300;

    const TYPES = [
        "Coins",
        "Balance"
    ];
    const UNITS = [
        "Coins" => "©",
        "Balance" => "$"
    ];
    const DEFAULTS = [
        "Coins" => 0,
        "Balance" => 0
    ];
    const MAXIMUMS = [
        "Coins" => 10000,
        "Balance" => 100000000
    ];
    const TOP_SHOWN_PER_PAGE = [
        "Coins" => 5,
        "Balance" => 5
    ];
}