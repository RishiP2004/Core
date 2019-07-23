<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\permission\BanEntry;

use pocketmine\utils\TextFormat;

class BanList extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("banlist", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.defaults.command.banlist");
        $this->setUsage("<players : ips>");
        $this->setDescription("Lists all the Players/Ip Addresses Banned from the Network");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /banlist " . $this->getUsage());
            return false;
        } else {
            switch(strtolower($args[0])) {
                case "players":
                    $list = $this->core->getEssentials()->getNameBans()->getEntries();
                    $message = implode(", ", array_map(function(BanEntry $entry) {
                        return $entry->getName();
                    }, $list));

                    $sender->sendMessage($this->core->getPrefix() . "Banned Players (x" . count($list)  . "):");
                    $sender->sendMessage(TextFormat::GRAY . $message);
                break;
                case "ips":
                    $list = $this->core->getEssentials()->getIPBans()->getEntries();
                    $message = implode(", ", array_map(function(BanEntry $entry) {
                        return $entry->getName();
                    }, $list));

                    $sender->sendMessage($this->core->getPrefix() . "Banned Ips (x" . count($list)  . "):");
                    $sender->sendMessage(TextFormat::GRAY . $message);
                break;
            }
            return true;
        }
    }
}