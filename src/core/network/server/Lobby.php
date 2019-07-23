<?php

declare(strict_types = 1);

namespace core\network\server;

class Lobby extends Server {
    public function __construct() {
        parent::__construct("Lobby");
    }

    public function getIp() : string {
        return "lobby.gratonepix.me";
    }

    public function getPort() : int {
        return 19132;
    }

    public function getIcon() : string {
        return "";
    }
}