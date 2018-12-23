<?php

namespace vote\command;

use core\Core;
use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class Vote extends PluginCommand {
    private $core;
    
    public function __construct(Core $core) {
        parent::__construct("vote", $core);
        
        $this->core = $core;
        
        $this->setPermission("core.vote.command");
        $this->setUsage("[player]");
        $this->setDescription("Vote Command");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(isset($args[0])) {
            $user = $this->core->getStats()->getCoreUser($args[0]);

            if(!$user) {
                $sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Player");
                return false;
            }
            if(in_array($user->getName(), $this->core->getVote()->queue)) {
                $sender->sendMessage($this->core->getErrorPrefix() . "We are currently checking Vote lists for " . $user->getName());
                return false;
			}
			if($user->getServer()->getName() === "Lobby") {
				$sender->sendMessage($this->core->getErrorPrefix() . "Run this command on the Gamemode you want to claim rewards");
				return false;
            } else {
                $user->vote();
                return true;
            }
        }
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
        }
        if(in_array($sender->getName(), $this->core->getVote()->queue)) {
            $sender->sendMessage($this->core->getErrorPrefix() . "We are currently checking Vote lists for you");
            return false;
		}
		if($sender->getCoreUser()->getServer()->getName() === "Lobby") {
			$sender->sendMessage($this->core->getErrorPrefix() . "Run this command on the Gamemode you want to claim rewards");
			return false;
        } else {
            $sender->getCoreUser()->vote();
            return true;
        }
    }
}