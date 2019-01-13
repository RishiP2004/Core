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

class BanListCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("banlist", $GPCore);

        $this->GPCore = $GPCore;

        $this->setPermission("GPCore.Essentials.Defaults.Command.BanList");
        $this->setUsage("<players : ips>");
        $this->setDescription("Lists all the Players/IP Addresses Banned from the Network");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Usage: /banlist" . " " . $this->getUsage());
            return false;
        } else {
            switch(strtolower($args[0])) {
                case "players":
                    $list = $sender->getServer()->getNameBans()->getEntries();
                    $message = implode(", ", array_map(function(BanEntry $entry) {
                        return $entry->getName();
                    }, $list));

                    $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Banned Players " . count($list)  . ":");
                    $sender->sendMessage(TextFormat::GRAY . $message);
                break;
                case "ips":
                    $list = $sender->getServer()->getIPBans()->getEntries();
                    $message = implode(", ", array_map(function(BanEntry $entry) {
                        return $entry->getName();
                    }, $list));

                    $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Banned IPs " . count($list)  . ":");
                    $sender->sendMessage(TextFormat::GRAY . $message);
                break;
            }
            return true;
        }
    }
}