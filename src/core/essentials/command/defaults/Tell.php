<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;
use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\utils\TextFormat;

class Tell extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("tell", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.defaults.tell.command");
        $this->setUsage("<player> [msg]");
		$this->setAliases(["msg", "message", "whisper"]);
        $this->setDescription("Tell a Player something");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /tell" . " " . $this->getUsage());
            return false;
        } else {
            $player = $this->core->getServer()->getPlayer($args[1]);

            if(!$player instanceof CorePlayer) {
                $sender->sendMessage($this->core->getErrorPrefix() . $args[3] . " is not Online");
                return false;
			}
			if($player->hasPermission($this->getPermission() . ".block")) {
				$sender->sendMessage($this->core->getErrorPrefix() . $player->getName() . " has Messages Blocked");
				return false;
            } else {
				$sender->sendMessage(TextFormat::GRAY . "[" . $sender->getName() . "] -> [" . $player->getDisplayName() . "]: " . implode(" ", $args));
                $name = $sender instanceof Player ? $sender->getDisplayName() : $sender->getName();
				$player->sendMessage("[" . $name . "] -> [" . $player->getName() . "]: " . implode(" ", $args));
            }
            return true;
        }
    }
}