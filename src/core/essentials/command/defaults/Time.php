<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;
use core\CorePlayer;

use core\essentials\Essentials;

use core\utils\PocketMine;

use pocketmine\Server;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\level\Level;

class Time extends PluginCommand {
	private $manager;

	public function __construct(Essentials $manager) {
		parent::__construct("time", Core::getInstance());

		$this->manager = $manager;

        $this->setPermission("core.essentials.defaults.command.teleport");
        $this->setUsage("<add <amount> : set <day : night> : start : stop : query>");
        $this->setDescription("Lists all the Players/IP Addresses Banned from the Network");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage(Core::ERROR_PREFIX . "Usage: /banlist " . $this->getUsage());
            return false;
        } else {
            switch(strtolower($args[0])) {
                case "add":
                    if(!$sender->hasPermission($this->getPermission() . ".add")){
                        $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
                        return false;
                    }
                    $value = PocketMine::getInteger($sender, $args[1], 0);

                    foreach($sender->getServer()->getLevels() as $level) {
                        $level->setTime($level->getTime() + $value);
                    }
                    $sender->sendMessage(Core::PREFIX . "Added " . $value . " to the Time");
                    Server::getInstance()->broadcastMessage(Core::PREFIX . $sender->getName() . " Added " . $value . " to the Time");
                break;
                case "set":
                    if(!$sender->hasPermission($this->getPermission() . ".set")){
                        $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
                        return false;
                    }
                    $value = null;

                    switch(strtolower($args[1])) {
                        case "day":
                            $value = Level::TIME_DAY;
                        break;
                        case "night":
                            $value = Level::TIME_FULL;
                        break;
                        case "sunrise":
                            $value = Level::TIME_SUNRISE;
                        break;
                        case "sunset":
                            $value = Level::TIME_SUNSET;
                        break;
                    }
                    foreach($sender->getServer()->getLevels() as $level) {
                        $level->setTime($value);
                    }
                    $sender->sendMessage(Core::PREFIX . "Set the Time to " . $value);
                    Server::getInstance()->broadcastMessage(Core::PREFIX . $sender->getName() . " Set the Time to " . $value);
                break;
                case "start":
                    if(!$sender->hasPermission($this->getPermission() . ".start")){
                        $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
                        return false;
                    }
                    foreach($sender->getServer()->getLevels() as $level) {
                        $level->startTime();
                    }
                    $sender->sendMessage(Core::PREFIX . "Started the Time");
                    Server::getInstance()->broadcastMessage(Core::PREFIX . $sender->getName() . " Started the Time");
                break;
                case "stop":
                    if(!$sender->hasPermission($this->getPermission() . ".stop")){
                        $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
                        return false;
                    }
                    foreach($sender->getServer()->getLevels() as $level) {
                        $level->stopTime();
                    }
                    $sender->sendMessage(Core::PREFIX . "Stopped the Time");
                    Server::getInstance()->broadcastMessage(Core::PREFIX . $sender->getName() . " Stopped the Time");
                break;
                case "query":
                    if(!$sender->hasPermission($this->getPermission() . ".query")){
                        $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
                        return false;
                    }
                    if($sender instanceof CorePlayer) {
                        $level = $sender->getLevel();
                    } else {
                        $level = $sender->getServer()->getDefaultLevel();
                    }
                    $sender->sendMessage(Core::PREFIX . "Time: " . $level->getTime());
                break;
            }
            return true;
        }
    }
}