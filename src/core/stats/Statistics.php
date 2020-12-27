<?php

declare(strict_types = 1);

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

	const COIN_VALUE = 1000;
    const COIN_UNIT = "©";
    const DEFAULT_COINS = 0;
    const MAX_COINS = 10000;
}