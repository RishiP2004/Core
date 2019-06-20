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
    private $core;
    
    public function __construct(Core $core) {
        parent::__construct("vote", $core);
        
        $this->core = $core;
        
        $this->setPermission("core.vote.command");
        $this->setUsage("[top] : [player]");
        $this->setDescription("Vote Command");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(isset($args[0]) === "top") {
        	$voters = $this->core->getVote()->getTopVoters();
			$i = 1;

			$sender->sendMessage($this->core->getPrefix() . "Top Voters this Month:");

			foreach($voters as $vote) {
				$sender->sendMessage(TextFormat::GRAY . "#" . $i . ". " . $vote["nickname"] . ": " . $vote["votes"]);
				$i++;
			}
        	return true;
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