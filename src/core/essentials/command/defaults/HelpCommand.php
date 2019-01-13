<?php
/**
 *    ___________________________
 *   /  _____/\______   \_   ___ \  ___________   ____
 *  /   \  ___ |     ___/    \  \/ /  _ \_  __ \_/ __ \
 *  \    \_\  \|    |   \     \___(  <_> )  | \/\  ___/
 *   \______  /|____|    \______  /\____/|__|    \___  >
 *          \/                  \/                   \/
 */
namespace GPCore\Essentials\Defaults\Commands;

use GPCore\GPCore;

use pocketmine\command\{
    PluginCommand,
    CommandSender,
    Command
};

use pocketmine\utils\TextFormat;

class HelpCommand extends PluginCommand {
    private $GPCore;
    
    public function __construct(GPCore $GPCore) {
        parent::__construct("help", $GPCore);
       
        $this->GPCore = $GPCore;
       
        $this->setPermission("GPCore.Essentials.Defaults.Command.Help");
        $this->setUsage("[page]");
        $this->setDescription("Check all the Commands on the Server");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
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
			$sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Help (" . $pageNumber . "/" . count($commands) . ")");
			
			if(isset($commands[$pageNumber - 1])) {
				foreach($commands[$pageNumber - 1] as $command) {
					$sender->sendMessage(TextFormat::GRAY . "/" . $command->getName() . ": " . $command->getDescription());
				}
			}
			return true;
		} else {
			$cmd = $sender->getServer()->getCommandMap()->getCommand(strtolower($command));

			if(!$cmd instanceof Command) {
				$sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "No Help for the Command: " . strtolower($command));
				return false;
			} else {
				if($cmd->testPermissionSilent($sender)) {
					$message = $this->GPCore->getBroadcast()->getErrorPrefix() . "Help for Command: /" . $cmd->getName();
					$message .= TextFormat::GRAY . "Description: " . $cmd->getDescription() . "\n";
					$message .= TextFormat::GRAY . "Usage: " . implode("\n", explode("\n", $cmd->getUsage())) . "\n";
					$sender->sendMessage($message);
				}
				return true;
			}
        }
    }
}