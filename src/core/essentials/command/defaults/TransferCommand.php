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

class TransferCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("transfer", $GPCore);

        $this->GPCore = $GPCore;

        $this->setPermission("GPCore.Essentials.Defaults.Command.Transfer");
        $this->setUsage("<ip> <port> [player]");
        $this->setDescription("Transfer yourself or a Player");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 2) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Usage: /transfer" . " " . $this->getUsage());
            return false;
        }
        if(is_float($args[1])) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[1] . " must be an Integer");
            return false;
        }
        if(isset($args[2])) {
			if(!$sender->hasPermission($this->getPermission() . ".Other")) {
				$sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
				return false;
			}
			$user = $this->GPCore->getStats()->getGPUser($args[2]);

			if(!$user->hasAccount()) {
				$sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[0] . " is not a valid Player");
				return false;
			}
            $player = $user->getGPPlayer();
			
            if(!$player instanceof GPPlayer) {
                $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $user->getUsername() . " is not Online");
                return false;
            } else {
                $player->transfer($args[0], $args[1], $sender->getName() . " Transferred you to IP: " . $args[0] . " and Port: " . $args[1]);
				$sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Transferring " . $user->getUsername() . " to IP: " . $args[0] . " and Port: " . $args[1]);
                return true;
            }
        }
        if(!$sender instanceof GPPlayer) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
        } else {
            $sender->transfer($args[0], $args[1], "Transferring to IP: " . $args[0] . " and Port: " . $args[1]);
            return true;
        }
    }
}