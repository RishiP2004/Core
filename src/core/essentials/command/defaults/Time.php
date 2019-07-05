<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;
use core\CorePlayer;

use core\utils\PocketMine;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\level\Level;

class Time extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("time", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.defaults.teleport.command");
        $this->setUsage("<add <amount> : set <day : night> : start : stop : query>");
        $this->setDescription("Lists all the Players/IP Addresses Banned from the Network");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /banlist" . " " . $this->getUsage());
            return false;
        } else {
            switch(strtolower($args[0])) {
                case "add":
                    if(!$sender->hasPermission($this->getPermission() . ".add")){
                        $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
                        return false;
                    }
                    $value = PocketMine::getInteger($sender, $args[1], 0);

                    foreach($sender->getServer()->getLevels() as $level) {
                        $level->setTime($level->getTime() + $value);
                    }
                    $sender->sendMessage($this->core->getPrefix() . "Added " . $value . " to the Time");
                    $this->core->getServer()->broadcastMessage($this->core->getPrefix() . $sender->getName() . " Added " . $value . " to the Time");
                break;
                case "set":
                    if(!$sender->hasPermission($this->getPermission() . ".set")){
                        $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
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
                    $sender->sendMessage($this->core->getPrefix() . "Set the Time to " . $value);
                    $this->core->getServer()->broadcastMessage($this->core->getPrefix() . $sender->getName() . " Set the Time to " . $value);
                break;
                case "start":
                    if(!$sender->hasPermission($this->getPermission() . ".start")){
                        $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
                        return false;
                    }
                    foreach($sender->getServer()->getLevels() as $level) {
                        $level->startTime();
                    }
                    $sender->sendMessage($this->core->getPrefix() . "Started the Time");
                    $this->core->getServer()->broadcastMessage($this->core->getPrefix() . $sender->getName() . " Started the Time");
                break;
                case "stop":
                    if(!$sender->hasPermission($this->getPermission() . ".stop")){
                        $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
                        return false;
                    }
                    foreach($sender->getServer()->getLevels() as $level) {
                        $level->stopTime();
                    }
                    $sender->sendMessage($this->core->getPrefix() . "Stopped the Time");
                    $this->core->getServer()->broadcastMessage($this->core->getPrefix() . $sender->getName() . " Stopped the Time");
                break;
                case "query":
                    if(!$sender->hasPermission($this->getPermission() . ".query")){
                        $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
                        return false;
                    }
                    if($sender instanceof CorePlayer) {
                        $level = $sender->getLevel();
                    } else {
                        $level = $sender->getServer()->getDefaultLevel();
                    }
                    $sender->sendMessage($this->core->getPrefix() . "Time: " . $level->getTime());
                break;
            }
            return true;
        }
    }
}