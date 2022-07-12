<?php

declare(strict_types = 1);

namespace core\player\command;

use core\Core;

use core\player\command\args\RankArgument;

use CortexPE\Commando\BaseCommand;

use pocketmine\command\CommandSender;

use pocketmine\utils\TextFormat;

class RankInformationCommand extends BaseCommand {
	public function prepare() : void {
		$this->setPermission("rank.command");
		$this->registerArgument(0, new RankArgument("rank", true));
	}

    public function onRun(CommandSender $sender, string $commandLabel, array $args) : void {
        if(!$args["rank"] == 0) {
            $sender->sendMessage(Core::ERROR_PREFIX . $args["rank"] . " is not a valid Rank");
            return;
        }
        $sender->sendMessage(Core::PREFIX . "Rank Information about " . $args['rank']->getName() . ":");
        $sender->sendMessage(TextFormat::GRAY . "Chat Format: " . $args['rank']->getChatFormat());
        $sender->sendMessage(TextFormat::GRAY . "NameTag Format: " . $args['rank']->getNameTagFormat());
						
        $permissions = [];
			
        foreach($args["rank"]->getPermissions() as $permission) {
        	$permissions[] = $permission;
        }
        $sender->sendMessage(TextFormat::GRAY . "Permissions: " . implode(", ", $permissions));
			
        $inheritedPermissions = [];
			
        if(!is_null($args["rank"]->getInheritance())) {
        	foreach($args["rank"]->getInheritedPermissions() as $inheritedPermission) {
        		$inheritedPermissions[] = $inheritedPermission;
        	}
        	$sender->sendMessage(TextFormat::GRAY . "Inherited Permissions: " . implode(", ", $inheritedPermissions));

        	$inheritances = [];
				
        	foreach($args["rank"]->getInheritance() as $inheritance) {
        		$inheritances[] = $inheritance;
        	}
        	$sender->sendMessage(TextFormat::GRAY . "Inheritances: " . implode(", ", $inheritances));
        }
        $sender->sendMessage(TextFormat::GRAY . "Value: " . $args["rank"]->getValue());
    }
}