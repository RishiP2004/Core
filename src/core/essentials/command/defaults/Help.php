<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;

use pocketmine\command\{
    PluginCommand,
    CommandSender,
    Command
};

use pocketmine\utils\TextFormat;

class Help extends PluginCommand {
    private $core;
    
    public function __construct(Core $core) {
        parent::__construct("help", $core);
       
        $this->core = $core;
       
        $this->setPermission("core.essentials.defaults.help.command");
        $this->setUsage("[page]");
        $this->setDescription("Check all the Commands on the Server");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }	
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
			$sender->sendMessage($this->core->getPrefix() . "Help (" . $pageNumber . "/" . count($commands) . ")");
			
			if(isset($commands[$pageNumber - 1])) {
				foreach($commands[$pageNumber - 1] as $command) {
					$sender->sendMessage(TextFormat::GRAY . "/" . $command->getName() . ": " . $command->getDescription());
				}
			}
			return true;
		} else {
			$cmd = $sender->getServer()->getCommandMap()->getCommand(strtolower($command));

			if(!$cmd instanceof Command) {
				$sender->sendMessage($this->core->getErrorPrefix() . "No Help for the Command: " . strtolower($command));
				return false;
			} else {
				if($cmd->testPermissionSilent($sender)) {
					$message = $this->core->getErrorPrefix() . "Help for Command: /" . $cmd->getName();
					$message .= TextFormat::GRAY . "Description: " . $cmd->getDescription() . "\n";
					$message .= TextFormat::GRAY . "Usage: " . implode("\n", explode("\n", $cmd->getUsage())) . "\n";
					$sender->sendMessage($message);
				}
				return true;
			}
        }
    }
}