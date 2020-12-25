<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;
use core\CorePlayer;

use core\essentials\Essentials;

use core\stats\Stats;

use core\utils\Math;

use pocketmine\Server;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class Block extends PluginCommand {
	private $manager;

	public function __construct(Essentials $manager) {
        parent::__construct("block", Core::getInstance());

        $this->manager = $manager;

        $this->setPermission("core.essentials.defaults.command.block");
        $this->setUsage("<player> [time] [reason]");
        $this->setDescription("Block a Player");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage(Core::ERROR_PREFIX . "Usage: /block " . $this->getUsage());
            return false;
        }
		Stats::getInstance()->getCoreUser($args[0], function($user) use ($sender, $args) {
			if(is_null($user)) {
				$sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not a valid Player");
				return false;
			}
			$blockList = $this->manager->getNameBlocks();

			if($blockList->isBanned($user->getName())) {
				$sender->sendMessage(Core::ERROR_PREFIX . $user->getName() . " is already Blocked");
				return false;
			} else {
				$expires = null;

				if(isset($args[1]) && $args[1] !== "i") {
					$expires = Math::expirationStringToTimer($args[1]);
				}
				$expire = $expires ?? "Not provided";

				if(isset($args[2])) {
					$reason = implode(" ", $args[2]);
				} else {
					$reason = "Not provided";
				}
				$blockList->addBan($user->getName(), $reason, $expires, $sender->getName());

				$player = Server::getInstance()->getPlayer($user->getName());

				if($player instanceof CorePlayer) {
					$player->sendMessage(Core::PREFIX . "You have been Blocked By: " . $sender->getName() . " for the Reason: " . $reason . ". Expires: " . $expire);
				}
				$sender->sendMessage(Core::PREFIX . "You have Blocked " . $user->getName() . " for the Reason: " . $reason . ". Expires: " . $expire);
				Server::getInstance()->broadcastMessage(Core::PREFIX . $user->getName() . " has been Blocked by " . $sender->getName() . " for the Reason: "  . $reason . ". Expires: " . $expire);
				return true;
			}
        });
		return false;
    }
}
