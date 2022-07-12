<?php

declare(strict_types = 1);

namespace core\essential\command\defaults;

use core\Core;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\args\RawStringArgument;

use pocketmine\command\{
    CommandSender,
    Command
};

use pocketmine\utils\TextFormat;

class HelpCommand extends BaseCommand {
	public function prepare() : void {
		$this->setPermission("help.command");
		$this->registerArgument(0, new RawStringArgument("page", true));
	}

	public function onRun(CommandSender $sender, string $commandLabel, array $args) : void {
		if(count($args) === 0) {
			$command = "";
			$pageNumber = 1;
		} else if(is_numeric($args[count($args) - 1])) {
			$pageNumber = array_pop($args);
			
			if($pageNumber <= 0) {
				$pageNumber = 1;
			}
			$command = \implode(" ", $args);
		} else {
			$command = \implode(" ", $args);
			$pageNumber = 1;
		}
		$pageHeight = $sender->getScreenLineHeight();

		if($command === "") {
			/** @var Command[][] $commands */
			$commands = [];
			
			foreach($sender->getServer()->getCommandMap()->getCommands() as $command) {
				if($command->testPermissionSilent($sender)) {
					$commands[$command->getName()] = $command;
				}
			}
			ksort($commands, SORT_NATURAL | SORT_FLAG_CASE);
			
			$commands = array_chunk($commands, $pageHeight);
			$pageNumber = min(\count($commands), $pageNumber);
			
			if($pageNumber < 1) {
				$pageNumber = 1;
			}
			$sender->sendMessage(Core::PREFIX . "Help (" . $pageNumber . "/" . count($commands) . ")");
			
			if(isset($commands[$pageNumber - 1])) {
				foreach($commands[$pageNumber - 1] as $command) {
					$sender->sendMessage(TextFormat::GRAY . "/" . $command->getName() . ": " . $command->getDescription());
				}
			}
		} else {
			$cmd = $sender->getServer()->getCommandMap()->getCommand(strtolower($command));

			if(!$cmd instanceof Command) {
				$sender->sendMessage(Core::ERROR_PREFIX . "No Help for the Command: " . strtolower($command));
			} else {
				if($cmd->testPermissionSilent($sender)) {
					$message = Core::ERROR_PREFIX . "Help for Command: /" . $cmd->getName();
					$message .= TextFormat::GRAY . "Description: " . $cmd->getDescription() . "\n";
					$message .= TextFormat::GRAY . "Usage: " . implode("\n", explode("\n", $cmd->getUsage())) . "\n";
					$sender->sendMessage($message);
				}
			}
        }
    }
}