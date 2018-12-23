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

class PayCoinsCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("paycoins", $GPCore);

        $this->GPCore = $GPCore;

        $this->setPermission("GPCore.Stats.Command.PayCoins");
        $this->setUsage("<player> <amount>");
        $this->setDescription("Pay a Player Coins");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender instanceof GPPlayer) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
        }
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 2) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Usage: /paycoins" . " " . $this->getUsage());
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
        if($user->getCoins() + $args[1] > $this->GPCore->getStats()->getMaximumEconomy("Coins")) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $user->getUsername() . " will have over the Maximum amount of Coins");
            return false;
        }
        if($sender->getGPUser()->getCoins() < $args[1]) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have enough Coins");
            return false;
        } else {
            $user->setCoins($user->getCoins() + $args[1]);
            $sender->getGPUser()->setCoins($sender->getGPUser()->getCoins() - $args[1]);

            $player = $user->getGPPlayer();
		
			if($player instanceof GPPlayer) {
				$player->sendMessage($this->GPCore->getBroadcast()->getPrefix() . $sender->getName() . " paid you " . $this->GPCore->getStats()->getEconomyUnit("Coins") . $args[1]);
			}
            $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Paid " . $user->getUsername() . " " . $this->GPCore->getStats()->getEconomyUnit("Coins") . $args[1]);
            return true;
        }
    }
}