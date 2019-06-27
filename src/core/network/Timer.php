<?php

declare(strict_types = 1);

namespace core\network;

use core\Core;

use core\utils\Math;

class Timer implements Networking {
    private $core;

    const NORMAL = 0;
    const OVERLOADED = 1;

    public $time = 0;

    public $paused = false;

    public function __construct() {
        $this->core = Core::getInstance();

        $this->time = self::RESTART * 60;
    }

    public function addTime(int $seconds) {
        $this->setTime($this->getTime() + $seconds);
    }

    public function getTime() : int {
        return $this->time;
    }

    public function setTime(int $seconds) {
        $this->time = $seconds;
    }

    public function subtractTime(int $seconds) {
        $this->setTime($this->getTime() - $seconds);
    }

    public function initiateRestart(int $mode) {
        switch($mode) {
            case self::NORMAL:
                foreach($this->core->getServer()->getOnlinePlayers() as $onlinePlayer) {
                    if($this->core->getServer()->getIp() === $this->core->getNetwork()->getServer("Lobby")->getIp() and $this->core->getServer()->getPort() === 19132) {
                        $onlinePlayer->sendMessage($this->core->getPrefix() . "Server Restarted, you will be rejoined");
                    }
                    $onlinePlayer->sendMessage($this->core->getPrefix() . "Server Restarted, you will be transferred to the Lobby");
                }
                $this->core->getServer()->getLogger()->info($this->core->getPrefix() . "Server Restarted");
            break;
            case self::OVERLOADED:
                foreach($this->core->getServer()->getOnlinePlayers() as $onlinePlayer) {
                    if($this->core->getServer()->getIp() === $this->core->getNetwork()->getServer("Lobby")->getIp() and $this->core->getServer()->getPort() === 19132) {
                        $onlinePlayer->sendMessage($this->core->getPrefix() . "Server Restarted because of Overload, you will be rejoined");
                    }
                    $onlinePlayer->sendMessage($this->core->getPrefix() . "Server Restarted because of Overload, you will be transferred to the Lobby");
                }
                $this->core->getServer()->getLogger()->info($this->core->getPrefix() . "Server Restarted because of Overload");
            break;
        }
        $this->core->getServer()->shutdown();
    }

    public function isPaused() : bool {
        return $this->paused;
    }

    public function setPaused(bool $state) {
        $this->paused = $state;
    }

    public function broadcastTime($message, $messageType) {
        $time = Math::toArray($this->getTime());
        $outMessage = str_replace([
            "{PREFIX}",
            "{FORMATTED_TIME}",
            "{HOUR}",
            "{MINUTE}",
            "{SECOND}",
            "{TIME}"
        ], [
            $this->core->getPrefix(),
            Math::getFormattedTime($this->getTime()),
            $time[0],
            $time[1],
            $time[2],
            $this->getTime()
        ], $message);

        switch($messageType) {
            case Network::CHAT:
                $this->core->getServer()->broadcastMessage($outMessage);
            break;
            case Network::POPUP:
                $this->core->getServer()->broadcastPopup($outMessage);
            break;
            case Network::TITLE:
                $this->core->getServer()->broadcastTitle($outMessage);
            break;
        }
    }
}