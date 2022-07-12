<?php

declare(strict_types = 1);

namespace core\essential\command\defaults;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\args\RawStringArgument;

use core\Core;

use core\player\CorePlayer;
use core\player\command\args\PlayerArgument;

use pocketmine\command\CommandSender;

use pocketmine\console\ConsoleCommandSender;

use pocketmine\utils\TextFormat;

class TellCommand extends BaseCommand {
	public function prepare() : void {
		$this->setPermission("tell.command");
		$this->registerArgument(0, new PlayerArgument("player"));
		$this->registerArgument(1, new RawStringArgument("message"));
	}

	public function onRun(CommandSender $sender, string $commandLabel, array $args) : void {
        if($args["player"]->getCoreUser()->hasDm()) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You cannot message that player, they have private messages disabled!");
            return;
        } else {
			if($sender instanceof ConsoleCommandSender) {
				$args["player"]->sendMessage("[CONSOLE] -> [" . $args['player']->getName() . "]: " . implode(" ", $args));
				return;
			} else if($sender instanceof CorePlayer) {
				$args["player"]->setRecentMessager($sender);
				$sender->setRecentMessager($args["player"]);
				//Logger::sendTellLog($sender->getName(), $player->getName(), $message);
				$sender->sendMessage(TextFormat::GRAY . "[" . $sender->getName() . "] -> [" . $args['player']->getDisplayName() . "]: " . implode(" ", $args));
				$name = $sender->getDisplayName();
				$args['player']->sendMessage("[" . $name . "] -> [" . $args['player']->getName() . "]: " . implode(" ", $args));
			}
		}
    }
}