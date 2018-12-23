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

class DeleteAccountCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("deleteaccount", $GPCore);

        $this->GPCore = $GPCore;

        $this->setAliases(["delaccount", "deleteacc"]);
        $this->setPermission("GPCore.Stats.Command.DeleteAccount");
        $this->setUsage("<player>");
        $this->setDescription("Delete a Player's Account");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Usage: /deleteaccount" . " " . $this->getUsage());
            return false;
        }
		$user = $this->GPCore->getStats()->getGPUser($args[0]);
		
		if(!$user->hasAccount()) {
			$sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[0] . " is not a valid Player");
			return false;
        } else {
			$user->removeAccount();
			$user->removeAccount("GPF");
			$user->removeAccount("GPL");

            $player = $user->getGPPlayer();
		
			if($player instanceof GPPlayer) {
				$player->kick($this->GPCore->getBroadcast()->getPrefix() . $sender->getName() . "Deleted your Account");
			}
            $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Deleted " . $user->getUsername() . "'s Account");
            return true;
        }
    }
}