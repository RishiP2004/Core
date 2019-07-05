<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;
use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class Transfer extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("transfer", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.defaults.transfer.command");
        $this->setUsage("<ip> <port> [player]");
        $this->setDescription("Transfer yourself or a Player");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 2) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /transfer" . " " . $this->getUsage());
            return false;
        }
        if(!is_int($args[0])) {
            $sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " must be an Integer");
            return false;
        }
        if(is_float($args[1])) {
            $sender->sendMessage($this->core->getErrorPrefix() . $args[1] . " must be an Integer");
            return false;
        }
        if(isset($args[2])) {
			if(!$sender->hasPermission($this->getPermission() . ".other")) {
				$sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
				return false;
			}
            $player = $this->core->getServer()->getPlayer($args[2]);

            if(!$player instanceof CorePlayer) {
                $sender->sendMessage($this->core->getErrorPrefix() . $args[2] . " is not Online");
                return false;
            } else {
                $player->transfer($args[0], $args[1], $sender->getName() . " Transferred you to IP: " . $args[0] . " and Port: " . $args[1]);
				$sender->sendMessage($this->core->getPrefix() . "Transferring " . $player->getName() . " to IP: " . $args[0] . " and Port: " . $args[1]);
                return true;
            }
        }
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
        } else {
            $sender->transfer($args[0], $args[1], "Transferring to IP: " . $args[0] . " and Port: " . $args[1]);
            return true;
        }
    }
}