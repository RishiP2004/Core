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

use pocketmine\utils\TextFormat;

class WhitelistCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("whitelist", $GPCore);

        $this->GPCore = $GPCore;

        $this->setPermission("GPCore.Essentials.Defaults.Command.Whitelist");
        $this->setUsage("<reload : on : off : list : add <player> : remove <player>>");
        $this->setDescription("Whitelist Command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Usage: /whitelist" . " " . $this->getUsage());
            return false;
        } else {
            switch(strtolower($args[0])) {
				case "reload":
                    $this->GPCore->getServer()->reloadWhitelist();
                    $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Reloaded the Whitelist");
                break;
                case "on":
                    $this->GPCore->getServer()->setConfigBool("whitelist", true);
                    $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Turned the Whitelist On");
                break;
                case "off":
                    $this->GPCore->getServer()->setConfigBool("whitelist", false);
                    $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Turned the Whitelist Off");
                break;
                case "list":
                    $entries = $sender->getServer()->getWhitelisted()->getAll(\true);
                    $message = \implode($entries, ", ");

                    $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Whitelisted Players " . count($entries)  . ":");
                    $sender->sendMessage(TextFormat::GRAY . $message);
                break;
                case "add":
                    if(count($args) < 2) {
                        $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Usage: /whitelist" . " " . $this->getUsage());
                        return false;
                    }
					$user = $this->GPCore->getStats()->getGPUser($args[0]);

					if(!$user->hasAccount()) {
						$sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[0] . " is not a valid Player");
						return false;
                    } else {
                        $sender->getServer()->getOfflinePlayer($args[1])->setWhitelisted(true);
                        $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Added " . $args[1] . " to the Whitelist");
                        $this->GPCore->getServer()->broadcastMessage($this->GPCore->getBroadcast()->getPrefix() . $args[0] . " has been Added to the Whitelist by " . $sender->getName());
                    }
                break;
                case "remove":
                    if(count($args) < 2) {
                        $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Usage: /whitelist" . " " . $this->getUsage());
                        return false;
                    }
					$user = $this->GPCore->getStats()->getGPUser($args[0]);

					if(!$user->hasAccount()) {
						$sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[0] . " is not a valid Player");
						return false;
                    } else {
                        $sender->getServer()->getOfflinePlayer($args[1])->setWhitelisted(false);
                        $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Removed " . $args[1] . " from the Whitelist");
                        $this->GPCore->getServer()->broadcastMessage($this->GPCore->getBroadcast()->getPrefix() . $args[0] . " has been Removed from the Whitelist by " . $sender->getName());
                    }
                break;
            }
            return true;
        }
    }
}