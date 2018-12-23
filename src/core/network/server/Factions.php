<?php

namespace core\network\server;

class Factions extends Server {
    public function __construct() {
        parent::__construct("Factions");
    }

    public function getIp() : string {
        return "facs.athena.me";
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