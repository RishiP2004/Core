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

use GPCore\Stats\Objects\Rank;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\utils\TextFormat;

class RankInformationCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("rankinformation", $GPCore);

        $this->GPCore = $GPCore;

        $this->setAliases(["rankinfo"]);
        $this->setPermission("GPCore.Stats.Command.RankInformation");
        $this->setUsage("<rank>");
        $this->setDescription("Check a Rank's Information");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
			$sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Usage: /rankinformation" . " " . $this->getUsage());
            return false;
        }
        $rank = $this->GPCore->getStats()->getRankFromString($args[0]);

        if(!$rank instanceof Rank) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[0] . " is not a valid Rank");
            return false;
        } else {
            $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Rank Information about " . $rank->getName() . ":");
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
			
			foreach($rank->getInheritances() as $inheritance) {
				$inheritances[] = $inheritance;
			}
            $sender->sendMessage(TextFormat::GRAY . "Inheritances: " . implode(", ", $inheritances));
            $sender->sendMessage(TextFormat::GRAY . "Value: " . $rank->getValue());
            return true;
        }
    }
}