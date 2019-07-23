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

class MuteList extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("mutelist", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.defaults.command.mutelist");
        $this->setUsage("<players : ips>");
        $this->setDescription("Lists all the Players/IP Addresses Muted from the Network");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /mutelist " . $this->getUsage());
            return false;
        } else {
            switch(strtolower($args[0])) {
                case "players":
                    $list = $this->core->getEssentials()->getNameMutes()->getEntries();
                    $message = implode(", ", array_map(function(BanEntry $entry) {
                        return $entry->getName();
                    }, $list));

                    $sender->sendMessage($this->core->getPrefix() . "Muted Players (x" . count($list)  . "):");
                    $sender->sendMessage(TextFormat::GRAY . $message);
                break;
                case "ips":
                    $list = $this->core->getEssentials()->getIpMutes()->getEntries();
                    $message = implode(", ", array_map(function(BanEntry $entry) {
                        return $entry->getName();
                    }, $list));

                    $sender->sendMessage($this->core->getPrefix() . "Muted IPs (x" . count($list)  . "):");
                    $sender->sendMessage(TextFormat::GRAY . $message);
                break;
            }
            return true;
        }
    }
}