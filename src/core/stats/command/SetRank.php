<?php
/**
 *    ___________________________
 *   /  _____/\______   \_   ___ \  ___________   ____
 *  /   \  ___ |     ___/    \  \/ /  _ \_  __ \_/ __ \
 *  \    \_\  \|    |   \     \___(  <_> )  | \/\  ___/
 *   \______  /|____|    \______  /\____/|__|    \___  >
 *          \/                  \/                   \/
 */
namespace GPCore\Stats\Commands;

use GPCore\GPCore;

use GPCore\Stats\Objects\{
    GPPlayer,
    Rank
};

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class SetRankCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("setrank", $GPCore);

        $this->GPCore = $GPCore;

        $this->setPermission("GPCore.Stats.Command.SetRank");
        $this->setUsage("<player> <rank>");
        $this->setDescription("Set a Player's Rank");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 2) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Usage: /setrank" . " " . $this->getUsage());
            return false;
        }
		$user = $this->GPCore->getStats()->getGPUser($args[0]);
		
		if(!$user->hasAccount()) {
			$sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[0] . " is not a valid Player");
			return false;
		}
        $rank = $this->GPCore->getStats()->getRankFromString($args[1]);

        if(!$rank instanceof Rank) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $rank->getName() . " is not a valid Rank");
            return false;
        } else {
            $user->setRank($rank);

            $player = $user->getGPPlayer();
		
			if($player instanceof GPPlayer) {
				$player->sendMessage($this->GPCore->getBroadcast()->getPrefix() . $sender->getName() . " set your Rank to " . $rank->getName());
			}
            $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Set " . $user->getUsername() . "'s Rank to " . $rank->getName());
            return true;
        }
    }
}