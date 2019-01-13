<?php
/**
 *    ___________________________
 *   /  _____/\______   \_   ___ \  ___________   ____
 *  /   \  ___ |     ___/    \  \/ /  _ \_  __ \_/ __ \
 *  \    \_\  \|    |   \     \___(  <_> )  | \/\  ___/
 *   \______  /|____|    \______  /\____/|__|    \___  >
 *          \/                  \/                   \/
 */
namespace GPCore\Essentials\Commands;

use GPCore\GPCore;

use GPCore\Stats\Objects\GPPlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class ClearInventoryCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("clearinventory", $GPCore);

        $this->GPCore = $GPCore;

		$this->setAliases(["ci"]);
        $this->setPermission("GPCore.Essentials.Command.ClearInventory");
        $this->setUsage("[player]");
        $this->setDescription("Clear your or a Player's Inventory");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(isset($args[0])) {
            if(!$sender->hasPermission($this->getPermission() . ".Other")) {
                $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
                return false;
            }
            $player = $this->GPCore->getServer()->getPlayer($args[0]);

            if(!$player instanceof GPPlayer) {
                $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[0] . " is not Online");
                return false;
			}
            if(!$player->getGPUser()->hasAccount()) {
                $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[0] . " is not a valid Player");
                return false;
            }
			if($player->getGamemode() === GPPlayer::SPECTATOR) {
				$sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $player->getName() . " is in " . $this->GPCore->getServer()->getGamemodeString($args[0]->getGamemode()) . " Mode");
				return false;
            } else {
				$player->getInventory()->clearAll();
				$sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Cleared " . $player->getName() . "'s Inventory");
				$player->sendMessage($this->GPCore->getBroadcast()->getPrefix() . $sender->getName() . " Cleared your Inventory");
                return true; 
            }
        }
        if(!$sender instanceof GPPlayer) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
        } else {
			$sender->getInventory()->clearAll();
			$sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Cleared your Inventory");
            return true;
        }
    }
}