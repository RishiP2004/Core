<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;
use core\CorePlayer;

use core\essentials\Essentials;

use pocketmine\Server;
use pocketmine\Player;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\utils\TextFormat;

class Tell extends PluginCommand {
	private $manager;

	public function __construct(Essentials $manager) {
		parent::__construct("tell", Core::getInstance());

		$this->manager = $manager;

        $this->setPermission("core.essentials.defaults.command.tell");
        $this->setUsage("<player> [msg]");
		$this->setAliases(["msg", "message", "whisper"]);
        $this->setDescription("Tell a Player something");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage(Core::ERROR_PREFIX . "Usage: /tell " . $this->getUsage());
            return false;
        } else {
            $player = Server::getInstance()->getPlayer($args[1]);

            if(!$player instanceof CorePlayer) {
                $sender->sendMessage(Core::ERROR_PREFIX . $args[3] . " is not Online");
                return false;
			}
			if($player->hasPermission($this->getPermission() . ".block")) {
				$sender->sendMessage(Core::ERROR_PREFIX . $player->getName() . " has Messages Blocked");
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