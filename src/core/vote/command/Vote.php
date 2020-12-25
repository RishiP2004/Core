<?php

declare(strict_types = 1);

namespace vote\command;

use core\Core;
use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\utils\TextFormat;

class Vote extends PluginCommand {
    private $manager;
    
    public function __construct(\core\vote\Vote $manager) {
        parent::__construct("vote", Core::getInstance());
        
        $this->manager = $manager;
        
        $this->setPermission("core.vote.command");
        $this->setUsage("[top]");
        $this->setDescription("Vote Command");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(isset($args[0]) === "top") {
			$voters = $this->manager->getTopVoters();
			$i = 1;

			$sender->sendMessage(Core::PREFIX . "Top Voters this Month:");

			foreach($voters as $vote) {
				$sender->sendMessage(TextFormat::GRAY . "#" . $i . ". " . $vote["nickname"] . ": " . $vote["votes"]);
				$i++;
			}
        	return true;
		}
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You must be a Player to use this Command");
            return false;
        }
        if(in_array($sender->getName(), $this->manager->queue)) {
            $sender->sendMessage(Core::ERROR_PREFIX . "We are currently checking Vote lists for you");
            return false;
		}
		if($sender->getCoreUser()->getServer()->getName() === "Lobby") {
			$sender->sendMessage(Core::ERROR_PREFIX . "Run this command on the Gamemode you want to claim rewards");
			return false;
        } else {
            $sender->getCoreUser()->vote();
            return true;
        }
    }
}