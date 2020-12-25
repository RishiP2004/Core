<?php

declare(strict_types = 1);

namespace core\network;

use core\Core;

use core\utils\Math;

class Timer implements Networking {
    const NORMAL = 0;
    const OVERLOADED = 1;

    public $time = 0;

    public $paused = false;

    public function __construct() {
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
                foreach(\pocketmine\Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
                    if(\pocketmine\Server::getInstance()->getIp() === Network::getInstance()->getServer("Lobby")->getIp() and \pocketmine\Server::getInstance()->getPort() === 19132) {
                        $onlinePlayer->sendMessage(Core::PREFIX . "Server Restarted, you will be rejoined");
                    }
                    $onlinePlayer->sendMessage(Core::PREFIX . "Server Restarted, you will be transferred to the Lobby");
                }
                \pocketmine\Server::getInstance()->getLogger()->info(Core::PREFIX . "Server Restarted");
            break;
            case self::OVERLOADED:
                foreach(\pocketmine\Server::getInstance()->getOnlinePlayers() as $onlinePlayer) {
                    if(\pocketmine\Server::getInstance()->getIp() === Network::getInstance()->getServer("Lobby")->getIp() and \pocketmine\Server::getInstance()->getPort() === 19132) {
                        $onlinePlayer->sendMessage(Core::PREFIX . "Server Restarted because of Overload, you will be rejoined");
                    }
                    $onlinePlayer->sendMessage(Core::PREFIX . "Server Restarted because of Overload, you will be transferred to the Lobby");
                }
                \pocketmine\Server::getInstance()->getLogger()->info(Core::PREFIX . "Server Restarted because of Overload");
            break;
        }
        \pocketmine\Server::getInstance()->shutdown();
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
			Core::PREFIX,
            Math::getFormattedTime($this->getTime()),
            $time[0],
            $time[1],
            $time[2],
            $this->getTime()
        ], $message);

        switch($messageType) {
            case Network::CHAT:
                \pocketmine\Server::getInstance()->broadcastMessage($outMessage);
            break;
            case Network::POPUP:
                \pocketmine\Server::getInstance()->broadcastPopup($outMessage);
            break;
            case Network::TITLE:
                \pocketmine\Server::getInstance()->broadcastTitle($outMessage);
            break;
        }
    }
}