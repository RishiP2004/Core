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

use GPCore\Stats\Objects\GPPlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class TakeCoinsCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("takecoins", $GPCore);

        $this->GPCore = $GPCore;

        $this->setPermission("GPCore.Stats.Command.TakeCoins");
        $this->setUsage("<player> <amount>");
        $this->setDescription("Take Coins from a Player");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 2) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Usage: /takecoins" . " " . $this->getUsage());
            return false;
        }
		$user = $this->GPCore->getStats()->getGPUser($args[0]);
		
		if(!$user->hasAccount()) {
			$sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[0] . " is not a valid Player");
			return false;
		}
        if(!is_numeric($args[1])) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[1] . " is not a valid Number");
            return false;
        }
        if(is_float($args[1])) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[1] . " must be an Integer");
            return false;
        }
        if($user->getCoins() - $args[1] < 0) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $user->getUsername() . " will have less than 0 Coins");
            return false;
        } else {
            $user->setCoins($user->getCoins() - $args[1]);

            $player = $user->getGPPlayer();
		
			if($player instanceof GPPlayer) {
				$player->sendMessage($this->GPCore->getBroadcast()->getPrefix() . $sender->getName() . " Took " . $this->GPCore->getStats()->getEconomyUnit("Coins") . $args[1] . " from you");
			}
            $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Took away " . $this->GPCore->getStats()->getEconomyUnit("Coins") . $args[1] . " from " . $user->getUsername());
            return true;
        }
    }
}