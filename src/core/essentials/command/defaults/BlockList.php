<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;

use core\essentials\Essentials;

use core\essentials\permission\BlockEntry;

use pocketmine\command\{
	PluginCommand,
	CommandSender
};

use pocketmine\utils\TextFormat;

class BlockList extends PluginCommand {
	private $manager;

	public function __construct(Essentials $manager) {
		parent::__construct("blocklist", Core::getInstance());

        $this->manager = $manager;

        $this->setPermission("core.essentials.defaults.command.blocklist");
        $this->setUsage("<players : ips>");
        $this->setDescription("Lists all the Players/Ip Addresses Blocked from the Network");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage(Core::ERROR_PREFIX . "Usage: /blocklist " . $this->getUsage());
            return false;
        } else {
            switch(strtolower($args[0])) {
                case "players":
                    $list = $this->manager->getNameBlocks()->getEntries();
                    $message = implode(", ", array_map(function(BlockEntry $entry) {
                        return $entry->getName();
                    }, $list));

                    $sender->sendMessage(Core::PREFIX . "Blocked Players (x" . count($list)  . "):");
                    $sender->sendMessage(TextFormat::GRAY . $message);
                break;
                case "ips":
                    $list = $this->manager->getIPBlocks()->getEntries();
                    $message = implode(", ", array_map(function(BlockEntry $entry) {
                        return $entry->getName();
                    }, $list));

                    $sender->sendMessage(Core::PREFIX . "Blocked Ips (x" . count($list)  . "):");
                    $sender->sendMessage(TextFormat::GRAY . $message);
                break;
            }
            return true;
        }
    }
}
