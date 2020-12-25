<?php

declare(strict_types = 1);

namespace core\essentials\command;

use core\Core;
use core\CorePlayer;

use core\essentials\Essentials;

use pocketmine\Server;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class Ping extends PluginCommand {
    private $manager;

    public function __construct(Essentials $manager) {
        parent::__construct("ping", Core::getInstance());

        $this->manager = $manager;

        $this->setPermission("core.essentials.command.ping");
        $this->setUsage("[player]");
        $this->setDescription("Check yours or a Player's Ping");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(isset($args[0])) {
            if(!$sender->hasPermission($this->getPermission() . ".other")) {
                $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
                return false;
            }
            $player = Server::getInstance()->getPlayer($args[0]);

            if(!$player instanceof CorePlayer) {
                $sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not Online");
                return false;      
            } else {
                $sender->sendMessage(Core::PREFIX . $player->getName() . "'s Ping is: " . $player->getPing());
                return true;
            }
        }
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You must be a Player to use this Command");
            return false;
        } else {
            $sender->sendMessage(Core::PREFIX. "Your Ping is: " . $sender->getPing());
            return true;
        }
    }
}