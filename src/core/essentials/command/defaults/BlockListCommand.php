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

use pocketmine\permission\BanEntry;

use pocketmine\utils\TextFormat;

class BlockListCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("blocklist", $GPCore);

        $this->GPCore = $GPCore;

        $this->setPermission("GPCore.Essentials.Defaults.Command.BlockList");
        $this->setUsage("<players : ips>");
        $this->setDescription("Lists all the Players/IP Addresses Blocked from the Network");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Usage: /blocklist" . " " . $this->getUsage());
            return false;
        } else {
            switch(strtolower($args[0])) {
                case "players":
                    $list = $this->GPCore->getEssentials()->getDefaults()->getNameBlocks()->getEntries();
                    $message = implode(", ", array_map(function(BanEntry $entry) {
                        return $entry->getName();
                    }, $list));

                    $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Blocked Players " . count($list)  . ":");
                    $sender->sendMessage(TextFormat::GRAY . $message);
                break;
                case "ips":
                    $list = $this->GPCore->getEssentials()->getDefaults()->getIPBlocks()->getEntries();
                    $message = implode(", ", array_map(function(BanEntry $entry) {
                        return $entry->getName();
                    }, $list));

                    $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Blocked IPs " . count($list)  . ":");
                    $sender->sendMessage(TextFormat::GRAY . $message);
                break;
            }
            return true;
        }
    }
}
