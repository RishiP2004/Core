<?php

declare(strict_types = 1);

namespace core\anticheat\command\subCommand;

use core\Core;

use core\anticheat\command\args\CheatArgument;

use core\player\traits\PlayerCallTrait;

use core\player\command\args\OfflinePlayerArgument;

use core\social\Access;

use CortexPE\Commando\BaseSubCommand;

use pocketmine\console\ConsoleCommandSender;

use pocketmine\Server;

use pocketmine\command\CommandSender;

class ReportSubCommand extends BaseSubCommand {
	use PlayerCallTrait;

	public function prepare() : void {
		$this->setPermission("cheat.subcommand.report");
		$this->registerArgument(0, new OfflinePlayerArgument("player"));
		$this->registerArgument(1, new CheatArgument("cheat"));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		$this->getCoreUser($args["player"]->getName(), function($user) use ($sender, $args) {
			if(is_null($user)) {
				$sender->sendMessage(Core::ERROR_PREFIX . $args["player"]->getName() . " is not a valid Player");
				return false;
			} else {
				if(empty(Access::WEB_HOOK_URL)) {
					Server::getInstance()->getCommandMap()->dispatch(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), "discord " . $user->getName() . " was Reported by " . $sender->getName() . " for " . $args['cheat']->getName());
				}
				Server::getInstance()->getCommandMap()->dispatch(new ConsoleCommandSender(Server::getInstance(), Server::getInstance()->getLanguage()), "chat say staff " . $user->getName() . " was Reported by " . $sender->getName() . " for " . $args['cheat']->getName());
				$sender->sendMessage(Core::PREFIX . "Thanks for Reporting " . $user->getName() . " for " . $args['cheat']->getName());
				return true;
			}
		});
	}
}