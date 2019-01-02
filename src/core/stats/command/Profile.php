<?php

namespace core\stats\command;

use core\Core;
use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class Profile extends PluginCommand {
    private $core;
    
    public function __construct(Core $core) {
        parent::__construct("profile", $core);
       
        $this->core = $core;
       
        $this->setPermission("core.stats.command.profile");
        $this->setUsage("[player]");
        $this->setDescription("Check your or a Player's Profile");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
        }
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(isset($args[0])) {
            $user = $this->core->getStats()->getCoreUser($args[0]);

            if(!$user) {
                $sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Player");
                return false;
            } else {
                $sender->sendProfileForm($user);
                return true;
            }
        } else if(!isset($args[0])) {
            if(!$sender instanceof CorePlayer) {
                $sender->sendMessage($this->core->getErrorPrefix() . "You must be a Player to use this Command");
                return false;
            } else {
                $sender->sendProfileForm();
                return true;
            }
        }
        return false;
    }
}