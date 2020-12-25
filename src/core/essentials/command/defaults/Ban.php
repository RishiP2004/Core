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

use pocketmine\utils\TextFormat;

class Ban extends PluginCommand {
    private $manager;

    public function __construct(Essentials $manager) {
        parent::__construct("ban", Core::getInstance());

        $this->manager = $manager;

        $this->setPermission("core.essentials.defaults.command.ban");
        $this->setUsage("<player> [time] [reason]");
        $this->setDescription("Ban a Player");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage(Core::ERROR_PREFIX . "Usage: /ban " . $this->getUsage());
            return false;
        }
		Stats::getInstance()->getCoreUser($args[0], function($user) use ($sender, $args) {
			if(is_null($user)) {
				$sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not a valid Player");
				return false;
			}
			$banList = $this->manager->getNameBans();
			
			if($banList->isBanned($user->getName())) {
				$sender->sendMessage(Core::ERROR_PREFIX . $user->getName() . " is already Banned");
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
				$banList->addBan($user->getName(), $reason, $expires, $sender->getName());

				$player = Server::getInstance()->getPlayer($user->getName());

				if($player instanceof CorePlayer) {
					$player->kick(Core::PREFIX . "You have been Banned By: " . $sender->getName() . "\n" . TextFormat::GRAY . "Reason: " . $reason . "\n" . TextFormat::GRAY . "Expires: " . $expire);
				}
				$sender->sendMessage(Core::PREFIX . "You have Banned " . $user->getName() . " for the Reason: " . $reason . ". Expires: " . $expire);
				Server::getInstance()->broadcastMessage(Core::PREFIX . $user->getName() . " has been Banned by " . $sender->getName() . " for the Reason: "  . $reason . ". Expires: " . $expire);
				return true;
			}
		});
		return false;
    }
}