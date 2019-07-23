<?php

declare(strict_types = 1);

namespace core\network\server;

use core\Core;

use core\mcpe\{
    MinecraftQuery,
    MinecraftQueryException
};

use core\network\FakePlayer;

abstract class Server {
    private $name = "";

    private $maxSlots = 10;

    private $onlinePlayers = [];

    private $online = false, $whiteListed = true;

    public function __construct(string $name) {
        $this->name = $name;
		$this->whitelisted = $this->isWhitelisted();
        
		$this->query();
    }

    public final function getName() : string {
        return $this->name;
    }

    public function query() {
        $minecraftQuery = new MinecraftQuery($this->getIp(), $this->getPort());

        try {
            if($info = $minecraftQuery->getInfo()) {
                $onlinePlayers = [];

                foreach($info as $key => $value) {
                    $onlinePlayers[$key] = new FakePlayer($value);
                }
                $this->maxSlots = $info["maxSlots"];
                $this->onlinePlayers = $onlinePlayers;
                $this->online = true;
            }
        } catch(MinecraftQueryException $exception) {
            Core::getInstance()->getServer()->getLogger()->error(Core::getInstance()->getErrorPrefix() . $exception->getMessage());
        }
    }

    public abstract function getIp() : string;

    public abstract function getPort() : int;

    public abstract function getIcon() : string;

    public abstract function isWhitelisted() : bool;
	
	public function setWhitelisted(bool $whitelisted = true) {
		$this->whitelisted = $whitelisted;
	}

    public function getMaxSlots() : int {
        return $this->maxSlots;
    }

    public function getOnlinePlayers() : array {
        return $this->onlinePlayers;
    }

    public function isOnline() : bool {
        return $this->online;
    }
}