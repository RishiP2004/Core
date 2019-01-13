<?php
/**
 *    ___________________________
 *   /  _____/\______   \_   ___ \  ___________   ____
 *  /   \  ___ |     ___/    \  \/ /  _ \_  __ \_/ __ \
 *  \    \_\  \|    |   \     \___(  <_> )  | \/\  ___/
 *   \______  /|____|    \______  /\____/|__|    \___  >
 *          \/                  \/                   \/
 */
namespace GPCore\Essentials\Defaults\Commands;

use GPCore\GPCore;

use GPCore\Stats\Objects\GPPlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use GPCore\Utils\PocketMineUtils;

use pocketmine\level\Level;

class TimeCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("time", $GPCore);

        $this->GPCore = $GPCore;

        $this->setPermission("GPCore.Essentials.Defaults.Command.Time");
        $this->setUsage("<add <amount> : set <day : night> : start : stop : query>");
        $this->setDescription("Lists all the Players/IP Addresses Banned from the Network");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Usage: /banlist" . " " . $this->getUsage());
            return false;
        } else {
            switch(strtolower($args[0])) {
                case "add":
                    if(!$sender->hasPermission($this->getPermission() . ".Add")){
                        $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
                        return false;
                    }
                    $value = PocketMineUtils::getInteger($sender, $args[1], 0);

                    foreach($sender->getServer()->getLevels() as $level) {
                        $level->setTime($level->getTime() + $value);
                    }
                    $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Added " . $value . " to the Time");
                    $this->GPCore->getServer()->broadcastMessage($this->GPCore->getBroadcast()->getPrefix() . $sender->getName() . " Added " . $value . " to the Time");
                break;
                case "set":
                    if(!$sender->hasPermission($this->getPermission() . ".Set")){
                        $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
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
                    $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Set the Time to " . $value);
                    $this->GPCore->getServer()->broadcastMessage($this->GPCore->getBroadcast()->getPrefix() . $sender->getName() . " Set the Time to " . $value);
                break;
                case "start":
                    if(!$sender->hasPermission($this->getPermission() . ".Start")){
                        $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
                        return false;
                    }
                    foreach($sender->getServer()->getLevels() as $level) {
                        $level->startTime();
                    }
                    $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Started the Time");
                    $this->GPCore->getServer()->broadcastMessage($this->GPCore->getBroadcast()->getPrefix() . $sender->getName() . " Started the Time");
                break;
                case "stop":
                    if(!$sender->hasPermission($this->getPermission() . ".Stop")){
                        $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
                        return false;
                    }
                    foreach($sender->getServer()->getLevels() as $level) {
                        $level->stopTime();
                    }
                    $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Stopped the Time");
                    $this->GPCore->getServer()->broadcastMessage($this->GPCore->getBroadcast()->getPrefix() . $sender->getName() . " Stopped the Time");
                break;
                case "query":
                    if(!$sender->hasPermission($this->getPermission() . ".Query")){
                        $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
                        return false;
                    }
                    if($sender instanceof GPPlayer) {
                        $level = $sender->getLevel();
                    } else {
                        $level = $sender->getServer()->getDefaultLevel();
                    }
                    $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Time: " . $level->getTime());
                break;
            }
            return true;
        }
    }
}