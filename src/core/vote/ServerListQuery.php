<?php

declare(strict_types = 1);

namespace core\vote;

class ServerListQuery {
    public $status = [];
    
    public function __construct($check, $claim) {
        $this->status = ["Check" => ["Url" => $check, "Code" => 0], "Claim" => ["Url" => $claim, "Code" => 0]];
    }
    
    public function getCheckURL() {
        return $this->status["Check"]["Url"];
    }
    
    public function getClaimURL() {
        return $this->status["Claim"]["Url"];
    }
    
    public function setVoted(bool $value) {
        return $this->status["Check"]["Code"] = $value;
    }
    
    public function hasVoted() : bool {
        return $this->status["Check"]["Code"] === 1;
    }
    
    public function setClaimed(bool $value) {
        return $this->status["Claim"]["Code"] = $value;
    }
    
    public function hasClaimed() : bool {
        return $this->status["Claim"]["Code"] === 1;
    }
}
