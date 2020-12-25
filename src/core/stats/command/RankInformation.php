<?php

declare(strict_types = 1);

namespace core\stats\command;

use core\Core;

use core\stats\Stats;

use core\stats\rank\Rank;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\utils\TextFormat;

class RankInformation extends PluginCommand {
    private $manager;

    public function __construct(Stats $manager) {
        parent::__construct("rankinformation", Core::getInstance());

        $this->manager = $manager;

        $this->setAliases(["rankinfo"]);
        $this->setPermission("core.stats.command.rankinformation");
        $this->setUsage("<rank>");
        $this->setDescription("Check a Rank's Information");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
			$sender->sendMessage(Core::ERROR_PREFIX . "Usage: /rankinformation " . $this->getUsage());
            return false;
        }
        $rank = $this->manager->getRank($args[0]);

        if(!$rank instanceof Rank) {
            $sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not a valid Rank");
            return false;
        } else {
            $sender->sendMessage(Core::PREFIX . "Rank Information about " . $rank->getName() . ":");
            $sender->sendMessage(TextFormat::GRAY . "Chat Format: " . $rank->getChatFormat());
            $sender->sendMessage(TextFormat::GRAY . "NameTag Format: " . $rank->getNameTagFormat());
						
			$permissions = [];
			
			foreach($rank->getPermissions() as $permission) {
				$permissions[] = $permission;
			}
            $sender->sendMessage(TextFormat::GRAY . "Permissions: " . implode(", ", $permissions));
			
			$inheritedPermissions = [];
			
			foreach($rank->getInheritedPermissions() as $inheritedPermission) {
				$inheritedPermissions[] = $inheritedPermission;
			}
            $sender->sendMessage(TextFormat::GRAY . "Inherited Permissions: " . implode(", ", $inheritedPermissions));
			
			$inheritances = [];
			
			foreach($rank->getInheritance() as $inheritance) {
				$inheritances[] = $inheritance;
			}
            $sender->sendMessage(TextFormat::GRAY . "Inheritances: " . implode(", ", $inheritances));
            $sender->sendMessage(TextFormat::GRAY . "Value: " . $rank->getValue());
            return true;
        }
    }
}