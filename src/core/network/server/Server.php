<?php

declare(strict_types = 1);

namespace core\network\server;

use core\Core;

use core\player\CorePlayer;

use core\network\mcpe\{
    MinecraftQuery,
    MinecraftQueryException
};
use core\player\FakePlayer;

use scoreboard\{
	Scoreboard,
	ScoreboardManager,
	ScoreboardAction
};

abstract class Server {//extends \Thread {
    private int $maxSlots = 10;

    private array $onlinePlayers = [];

    private bool $online = false;
	private bool $whitelisted = false;

    public function __construct(private string $name = "") {
		//$this->query();
    }

    public final function getName() : string {
        return $this->name;
    }

    public function query() : void {
        $minecraftQuery = new MinecraftQuery($this->getIp(), $this->getPort());

        try {
            if($info = $minecraftQuery->getInfo()) {
                $onlinePlayers = [];

                foreach($info as $key => $value) {
					//FAKE PLAYER
                    $onlinePlayers[$key] = new class($value) {
						public function __construct(private string $name = "") {}

						public function getName() : string {
							return $this->name;
						}

						public function isOnline() : bool {
							return false;
						}
					};
                }
                $this->maxSlots = $info["maxSlots"];
                $this->onlinePlayers = $onlinePlayers;
                $this->online = true;
            }
        } catch(MinecraftQueryException $exception) {
            Core::getInstance()->getServer()->getLogger()->error(Core::ERROR_PREFIX . $exception->getMessage());
        }
    }

    public abstract function getIp() : string;

    public abstract function getPort() : int;

    public abstract function getIcon() : string;

    public abstract function addHud(int $type, CorePlayer $player);

    public function removeHud(int $type, CorePlayer $player) : void {
		switch($type) {
			case CorePlayer::SCOREBOARD:
				if(ScoreboardManager::getId(Core::PREFIX . $this->getName()) !== null) {
					$scoreboard = new Scoreboard(Core::PREFIX . $this->getName(), ScoreboardAction::MODIFY);
				}
				$scoreboard->removeDisplay($player);
			break;
			case CorePlayer::POPUP:
			break;
		}
	}

    public function isWhitelisted() : bool {
		return $this->whitelisted;
	}
	
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