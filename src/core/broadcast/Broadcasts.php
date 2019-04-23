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
        "First" => "{NAME_TAG_FORMAT}" . TextFormat::GRAY . " has joined the GratonePix community!",
        "Normal" => "{NAME_TAG_FORMAT}" . TextFormat::GRAY . " has joined the Server"
    ];
    const DEATHS = [
        "Contact" => "{NAME_TAG_FORMAT}" . TextFormat::GRAY . " was killed by {BLOCK}",
        "Kill" => "{NAME_TAG_FORMAT}" . TextFormat::GRAY . " was killed by {KILLER}",
        "projectile" => "{NAME_TAG_FORMAT}" . TextFormat::GRAY . " was killed by {KILLER}",
        "Suffocation" => "{NAME_TAG_FORMAT}" . TextFormat::GRAY . " suffocated",
        "Starvation" => "{NAME_TAG_FORMAT}" . TextFormat::GRAY . " starved to death",
        "Fall" => "{NAME_TAG_FORMAT}" . TextFormat::GRAY . " fell from a high distance",
        "Fire" => "{NAME_TAG_FORMAT}" . TextFormat::GRAY . " went up in flames",
        "On-Fire" => "{NAME_TAG_FORMAT}" . TextFormat::GRAY . " burned",
        "Lava" => "{NAME_TAG_FORMAT}" . TextFormat::GRAY . " tried to swim in lava",
        "Drowning" => "{NAME_TAG_FORMAT}" . TextFormat::GRAY . " rowned",
        "Explosion" => "{NAME_TAG_FORMAT}" . TextFormat::GRAY . " exploded",
        "Void" => "{NAME_TAG_FORMAT}" . TextFormat::GRAY . " fell into the void",
        "Suicide" => "{NAME_TAG_FORMAT}" . TextFormat::GRAY . " committed suicide",
        "Magic" => "{NAME_TAG_FORMAT}" . TextFormat::GRAY . " was killed by magic",
        "Normal" => "{NAME_TAG_FORMAT}" . TextFormat::GRAY . " died"
    ];
    const QUITS = [
        "Normal" => "{NAME_TAG_FORMAT}" . TextFormat::GRAY . " has left the Server"
    ];
    const DIMENSIONS = [
        "Change" => ""
    ];
    const KICKS = [
        "Outdated" => [
            "Client" => "{PREFIX}Your Minecraft client is outdated",
            "Server" => "{PREFIX}This server is outdated"
        ],
        "Whitelisted" => "{PREFIX}This server is whitelisted",
        "Full" => "{PREFIX}This server is full {ONLINE_PLAYERS}/{MAX_PLAYERS}"
    ];
}