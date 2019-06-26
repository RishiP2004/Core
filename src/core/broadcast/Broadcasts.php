<?php

declare(strict_types = 1);

namespace core\broadcast;

use pocketmine\utils\TextFormat;

interface Broadcasts {
    const NAME = "{PREFIX}{SERVER_PREFIX}";

    const FORMATS = [
        "date_time" => "H:i:s",
        "broadcast" => "{PREFIX}" . TextFormat::GRAY . "{MESSAGE}"
    ];
    const AUTOS = [
        "message" => true,
        "popup" => false,
        "title" => false,
    ];
    const TIMES = [
        "message" => 45,
        "popup" => 45,
        "title" => 45,
    ];
    const DURATIONS = [
        "popup" => 5,
        "title" => 5
    ];
    const Messages = [
        "{PREFIX}" . "Daily Updates!",
        "{PREFIX}" . "Thanks for Playing!",
        "{PREFIX}" . "Follow us on twitter: @GratonePix"
    ];
    const POPUPS = [
        "",
        "",
        ""
    ];
    const TITLES = [
        "",
        "",
        ""
    ];
    const JOINS = [
        "first" => "{NAME_TAG_FORMAT}" . TextFormat::GRAY . " has joined the GratonePix community!",
        "normal" => "{NAME_TAG_FORMAT}" . TextFormat::GRAY . " has joined the Server"
    ];
    const DEATHS = [
        "contact" => "{NAME_TAG_FORMAT}" . TextFormat::GRAY . " was killed by {BLOCK}",
        "kill" => "{NAME_TAG_FORMAT}" . TextFormat::GRAY . " was killed by {KILLER}",
        "projectile" => "{NAME_TAG_FORMAT}" . TextFormat::GRAY . " was killed by {KILLER}",
        "suffocation" => "{NAME_TAG_FORMAT}" . TextFormat::GRAY . " suffocated",
        "starvation" => "{NAME_TAG_FORMAT}" . TextFormat::GRAY . " starved to death",
        "fall" => "{NAME_TAG_FORMAT}" . TextFormat::GRAY . " fell from a high distance",
        "fire" => "{NAME_TAG_FORMAT}" . TextFormat::GRAY . " went up in flames",
        "on-fire" => "{NAME_TAG_FORMAT}" . TextFormat::GRAY . " burned",
        "lava" => "{NAME_TAG_FORMAT}" . TextFormat::GRAY . " tried to swim in lava",
        "drowning" => "{NAME_TAG_FORMAT}" . TextFormat::GRAY . " rowned",
        "explosion" => "{NAME_TAG_FORMAT}" . TextFormat::GRAY . " exploded",
        "void" => "{NAME_TAG_FORMAT}" . TextFormat::GRAY . " fell into the void",
        "suicide" => "{NAME_TAG_FORMAT}" . TextFormat::GRAY . " committed suicide",
        "magic" => "{NAME_TAG_FORMAT}" . TextFormat::GRAY . " was killed by magic",
        "normal" => "{NAME_TAG_FORMAT}" . TextFormat::GRAY . " died"
    ];
    const QUITS = [
        "normal" => "{NAME_TAG_FORMAT}" . TextFormat::GRAY . " has left the Server"
    ];
    const DIMENSIONS = [
        "change" => ""
    ];
    const KICKS = [
        "outdated" => [
            "client" => "{PREFIX}Your Minecraft client is outdated",
            "server" => "{PREFIX}This server is outdated"
        ],
        "whitelisted" => "{PREFIX}This server is whitelisted",
        "full" => "{PREFIX}This server is full {ONLINE_PLAYERS}/{MAX_PLAYERS}"
    ];
}