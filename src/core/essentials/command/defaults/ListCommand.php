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

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class ListCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("list", $GPCore);
        $this->GPCore = $GPCore;

        $this->setPermission("GPCore.Essentials.Defaults.Command.List");
        $this->setUsage("[server]");
        $this->setDescription("See all Online Players of a Server or the Network");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        } else {
            if(isset($args[0])) {
				switch(strtolower($args[0])) {
					case "lobby":
						$sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Online Players in Lobby:");
						$sender->sendMessage(implode(", ", $this->GPCore->getNetwork()->getServerFromString("Lobby")->getOnlinePlayers()));
					break;
					case "factions":
						$sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Online Players in Factions:");
						$sender->sendMessage(implode(", ", $this->GPCore->getNetwork()->getServerFromString("Factions")->getOnlinePlayers()));
					break;
					default:
						$sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "No such Server in the GratonePix Network");
					break;
				}
			}
			$sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "All Online Players:");
			$sender->sendMessage(implode(", ", $this->GPCore->getNetwork()->getTotalOnlinePlayers()));
            return true;
        }
    }
}
