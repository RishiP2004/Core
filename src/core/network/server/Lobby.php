<?php

namespace core\network\server;

class Lobby extends Server {
    public function __construct() {
        parent::__construct("Lobby");

        $this->query();
    }

    public function getIp() : string {
        return "lobby.athena.me";
    }

    public function getPort() : int {
        return 19132;
    }

    public function getIcon() : string {
        return "";
    }

    public function isWhitelisted() : bool {
        return true;
    }
}