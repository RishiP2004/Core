<?php

namespace core\anticheat;

interface Cheats {
    const MAX_CONCURRENT_EXPLOSIONS = 4;
    const AUTO_CLICK_AMOUNT = 40;

    const PROXY_URL = "http://v2.api.iphub.info/ip/{ADDRESS}";
    const PROXY_KEY = "MzE5MDpkQnZxZElnd2NIQ0ZkWUF6SEtseFBhdzVKOHJGTkxGMw";

    const LAG_CLEAR_TIME = [
        "hours" => 1,
        "minutes" => 30
    ];
}