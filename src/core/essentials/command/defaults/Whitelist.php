<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\utils\TextFormat;

class Whitelist extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("whitelist", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.defaults.information.command");
        $this->setUsage("<reload : on : off : list : add <player> : remove <player>>");
        $this->setDescription("Whitelist Command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /whitelist " . $this->getUsage());
            return false;
        } else {
            switch(strtolower($args[0])) {
				case "reload":
                    $this->core->getServer()->reloadWhitelist();
                    $sender->sendMessage($this->core->getPrefix() . "Reloaded the Whitelist");
                break;
                case "on":
                    $this->core->getServer()->setConfigBool("whitelist", true);
                    $sender->sendMessage($this->core->getPrefix() . "Turned the Whitelist On");
                break;
                case "off":
                    $this->core->getServer()->setConfigBool("whitelist", false);
                    $sender->sendMessage($this->core->getPrefix() . "Turned the Whitelist Off");
                break;
                case "list":
                    $entries = $sender->getServer()->getWhitelisted()->getAll(true);
                    $message = \implode($entries, ", ");

                    $sender->sendMessage($this->core->getPrefix() . "Whitelisted Players " . count($entries)  . ":");
                    $sender->sendMessage(TextFormat::GRAY . $message);
                break;
                case "add":
                    if(count($args) < 1) {
                        $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /whitelist" . " " . $this->getUsage());
                        return false;
                    } else {
                        $sender->getServer()->getOfflinePlayer($args[1])->setWhitelisted(true);
                        $sender->sendMessage($this->core->getPrefix() . "Added " . $args[1] . " to the Whitelist");
                        $this->core->getServer()->broadcastMessage($this->core->getPrefix() . $args[1] . " has been Added to the Whitelist by " . $sender->getName());
                    }
                break;
                case "remove":
                    if(count($args) < 1) {
                        $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /whitelist" . " " . $this->getUsage());
                        return false;
                    } else {
                        $sender->getServer()->getOfflinePlayer($args[1])->setWhitelisted(false);
                        $sender->sendMessage($this->core->getPrefix() . "Removed " . $args[1] . " from the Whitelist");
                        $this->core->getServer()->broadcastMessage($this->core->getPrefix() . $args[1] . " has been Removed from the Whitelist by " . $sender->getName());
                    }
                break;
            }
            return true;
        }
    }
}