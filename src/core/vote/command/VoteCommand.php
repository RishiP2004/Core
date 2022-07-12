<?php

declare(strict_types = 1);

namespace core\vote\command;

use core\Core;

use core\player\CorePlayer;

use core\vote\VoteManager;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\args\RawStringArgument;

use pocketmine\command\CommandSender;

use pocketmine\utils\TextFormat;

class VoteCommand extends BaseCommand {
	public function prepare() : void {
		$this->setPermission("vote.command");
		$this->registerArgument(0, new RawStringArgument("top", true));
	}

	public function onRun(CommandSender $sender, string $commandLabel, array $args) : void {
        if(isset($args["top"])) {
			$voters = VoteManager::getInstance()->getTopVoters();
			$i = 1;

			$sender->sendMessage(Core::PREFIX . "Top Voters this Month:");

			foreach($voters as $vote) {
				$sender->sendMessage(TextFormat::GRAY . "#" . $i . ". " . $vote["nickname"] . ": " . $vote["votes"]);
				$i++;
			}
        	return;
		}
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You must be a Player to use this Command");
            return;
        }
        if(in_array($sender->getName(), VoteManager::getInstance()->getQueue())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "We are currently checking vote lists for you");
            return;
		}
		if($sender->getCoreUser()->getServer()->getName() === "Lobby") {
			$sender->sendMessage(Core::ERROR_PREFIX . "Run this command on the gamemode you want to claim rewards!");
			return;
        }
		$sender->getCoreUser()->vote();
    }
}