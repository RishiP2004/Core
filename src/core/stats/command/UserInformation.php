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

use pocketmine\utils\TextFormat;

class UserInformationCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("userinformation", $GPCore);

        $this->GPCore = $GPCore;

        $this->setAliases(["userinfo"]);
        $this->setPermission("GPCore.Stats.Command.UserInformation");
        $this->setUsage("[player]");
        $this->setDescription("Check a Player's Information");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(isset($args[0])) {
			$user = $this->GPCore->getStats()->getGPUser($args[0]);
		
			if(!$user->hasAccount()) {
				$sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[0] . " is not a valid Player");
				return false;
            } else {
				$sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . $user->getUsername() . "'s Information:");
				$sender->sendMessage(TextFormat::GRAY . "Register Date: " . $user->getRegisterDate());
				
				switch($this->GPCore->getNetwork()->getServerFromIp($sender->getServer()->getIp())->getName()) {
					case "Lobby":
						$sender->sendMessage(TextFormat::GRAY . "Lobby Register Date: " . $user->getRegisterDate("GPL"));
					break;
					case "Factions":
						$sender->sendMessage(TextFormat::GRAY . "Factions Register Date: " . $user->getRegisterDate("GPF"));
					break;
				}
				$sender->sendMessage(TextFormat::GRAY . "Xuid: " . $user->getXuid());
				$sender->sendMessage(TextFormat::GRAY . "Ip: " . $user->getIp());
				$sender->sendMessage(TextFormat::GRAY . "Country: " . $user->getCountry());
                return true;
            }
        }
        if(!$sender instanceof GPPlayer) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
        } else {
            $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Your Information:");
				$sender->sendMessage(TextFormat::GRAY . "Register Date: " . $sender->getGPUser()->getRegisterDate());
				
				switch($this->GPCore->getNetwork()->getServerFromIp($sender->getServer()->getIp())->getName()) {
					case "Lobby":
						$sender->sendMessage(TextFormat::GRAY . "Lobby Register Date: " . $sender->getGPUser()->getRegisterDate("GPL"));
					break;
					case "Factions":
					$sender->sendMessage(TextFormat::GRAY . "Factions Register Date: " . $sender->getGPUser()->getRegisterDate("GPF"));
					break;
				}
				$sender->sendMessage(TextFormat::GRAY . "Xuid: " . $sender->getGPUser()->getXuid());
				$sender->sendMessage(TextFormat::GRAY . "Ip: " . $sender->getGPUser()->getIp());
				$sender->sendMessage(TextFormat::GRAY . "Country: " . $sender->getGPUser()->getCountry());
            return true;
        }
    }
}