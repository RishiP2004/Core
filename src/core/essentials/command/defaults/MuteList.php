<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;

use core\essentials\Essentials;

use core\essentials\permission\MuteEntry;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\utils\TextFormat;

class MuteList extends PluginCommand {
	private $manager;

	public function __construct(Essentials $manager) {
		parent::__construct("mutelist", Core::getInstance());

		$this->manager = $manager;

        $this->setPermission("core.essentials.defaults.command.mutelist");
        $this->setUsage("<players : ips>");
        $this->setDescription("Lists all the Players/IP Addresses Muted from the Network");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage(Core::ERROR_PREFIX . "Usage: /mutelist " . $this->getUsage());
            return false;
        } else {
            switch(strtolower($args[0])) {
                case "players":
                    $list = $this->manager->getNameMutes()->getEntries();
                    $message = implode(", ", array_map(function(MuteEntry $entry) {
                        return $entry->getName();
                    }, $list));

                    $sender->sendMessage(Core::PREFIX . "Muted Players (x" . count($list)  . "):");
                    $sender->sendMessage(TextFormat::GRAY . $message);
                break;
                case "ips":
                    $list = $this->manager->getIpMutes()->getEntries();
                    $message = implode(", ", array_map(function(MuteEntry $entry) {
                        return $entry->getName();
                    }, $list));

                    $sender->sendMessage(Core::PREFIX . "Muted IPs (x" . count($list)  . "):");
                    $sender->sendMessage(TextFormat::GRAY . $message);
                break;
            }
            return true;
        }
    }
}