<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;
use core\CorePlayer;

use core\essentials\Essentials;

use scoreboard\{
	ScoreboardAction,
	ScoreboardManager
};

use pocketmine\Server;

use pocketmine\command\{
	PluginCommand,
	CommandSender
};

class Scoreboard extends PluginCommand {
	private $manager;

	public function __construct(Essentials $manager) {
		parent::__construct("scoreboard", Core::getInstance());

		$this->manager = $manager;

		$this->setAliases(["sb"]);
		$this->setPermission("core.essentials.defaults.command.scoreboard");
		$this->setUsage("<create : delete : add : remove : setLine : removeLine>");
		$this->setDescription("Scoreboard Command");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
		if(!$sender->hasPermission($this->getPermission())) {
			$sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
			return false;
		}
		if(count($args) < 1) {
			$sender->sendMessage(Core::ERROR_PREFIX . "Usage: /scoreboard " . $this->getUsage());
			return false;
		} else {
			switch(strtolower($args[0])) {
				case "create":
					if(count($args) < 4) {
						$sender->sendMessage(Core::ERROR_PREFIX . "Usage: /scoreboard create <title> <displaySlot (sidebar/list/belowname)> <sortOrder (0->ascending/1->descending)>");
						return false;
					}
					if(ScoreboardManager::getId($args[1]) !== null) {
						$sender->sendMessage(Core::ERROR_PREFIX . "This scoreboard named . " . $args[1]. " already exists");
						return false;
					}
					if((!(int) $args[3]) !== 0 or 1) {
						$sender->sendMessage(Core::ERROR_PREFIX . "The sort order needs to be 0/1");
						return false;
					}
					if((strtolower($args[2]) !== "sidebar" or "list" or "belowname")) {
						$sender->sendMessage(Core::ERROR_PREFIX . "Not a valid Display Slot");
						return false;
					} else {
						$scoreboard = new \scoreboard\Scoreboard($args[1], ScoreboardAction::CREATE);
						$scoreboard->create($args[2], (int) $args[3]);
						$sender->sendMessage(Core::PREFIX . "Successfully Created Scoreboard " . $args[1]);
						return true;
					}
				break;
				case "delete":
					if(count($args) < 2) {
						$sender->sendMessage(Core::ERROR_PREFIX . "Usage: /scoreboard delete <title>");
						return false;
					}
					if(ScoreboardManager::getId($args[1]) === null) {
						$sender->sendMessage(Core::ERROR_PREFIX . "There is no Scoreboard with the name " . $args[1]);
						return false;
					} else {
						$scoreboard = new \scoreboard\Scoreboard($args[1], ScoreboardAction::MODIFY);
						$scoreboard->delete();
						$sender->sendMessage(Core::PREFIX . "Successfully Deleted Scoreboard " . $args[1] . ".");
						return true;
					}
				break;
				case "add":
					if(count($args) < 3) {
						$sender->sendMessage(Core::ERROR_PREFIX . "Usage: /scoreboard add <player / all> <title>");
						return false;
					}
					if(ScoreboardManager::getId($args[2]) === null) {
						$sender->sendMessage(Core::ERROR_PREFIX . "There is no Scoreboard with the name " . $args[1]);
						return false;
					} else {
						$scoreboard = new \scoreboard\Scoreboard($args[2], ScoreboardAction::MODIFY);

						if($args[1] === "all") {
							foreach(Server::getInstance()->getOnlinePlayers() as $p) {
								if($p instanceof CorePlayer) {
									$scoreboard->addDisplay($p);
								}
							}
							$sender->sendMessage(Core::PREFIX . "Sent Scoreboard " . $args[2] . " to all the online players");
							return true;
						} else {
							$p = Server::getInstance()->getPlayer($args[1]);

							if(!$p instanceof CorePlayer) {
								$sender->sendMessage(Core::ERROR_PREFIX . $p . " is not Online");
							}
							$scoreboard->addDisplay($p);
							$sender->sendMessage(Core::PREFIX . "Sent " . $args[2] . " Scoreboard to  " . $p->getName());
							return true;
						}
					}
				break;
				case "setline":
					if(count($args) < 4) {
						$sender->sendMessage(Core::ERROR_PREFIX . "Usage: /scoreboard setLine <title of the scoreboard> <line> <message>");
						return false;
					}
					if(ScoreboardManager::getId($args[1]) === null) {
						$sender->sendMessage(Core::ERROR_PREFIX . "There is no Scoreboard with the name " . $args[1]);
						return false;
					}
					if(!is_numeric($args[2])) {
						$sender->sendMessage($args[2] . " is not a valid Number");
						return false;
					}
					if(!((int) $args[2] >= 1 && (int) $args[2] <= 15)) {
						$sender->sendMessage(Core::ERROR_PREFIX . $args[2] . " should be between 1 and 9");
						return false;
					} else {
						$scoreboard = new \scoreboard\Scoreboard($args[2], ScoreboardAction::MODIFY);
						$scoreboard->setLine((int) $args[2], implode(" ", array_slice($args, 3)));
						$sender->sendMessage(Core::PREFIX . "Set Line number " . $args[2] . " of Scoreboard " . $args[1]);
						return true;
					}
				break;
				case "removeLine":
					if(count($args) < 3) {
						$sender->sendMessage(Core::ERROR_PREFIX . "Usage: /scoreboard removemLine <title of the scoreboard> <line>");
						return false;
					}
					if(ScoreboardManager::getId($args[1]) === null) {
						$sender->sendMessage(Core::ERROR_PREFIX . "There is no Scoreboard with the name " . $args[1]);
						return false;
					}
					if(!is_numeric($args[2])) {
						$sender->sendMessage(Core::ERROR_PREFIX . $args[2] . " is not a valid Number");
						return false;
					}
					if(!((int) $args[2] >= 1 && (int) $args[2] <= 15)) {
						$sender->sendMessage(Core::ERROR_PREFIX. $args[2] . " should be between 1 and 9");
						return false;
					}
					if(!ScoreboardManager::entryExist((string) ScoreboardManager::getId($args[1]), $args[2])) {
						$sender->sendMessage(Core::ERROR_PREFIX . "Scoreboard " . $args[1] . "doesn't have line number " . $args[2]);
						return false;
					} else {
						$scoreboard = new \scoreboard\Scoreboard($args[1], ScoreboardAction::MODIFY);
						$scoreboard->removeLine($args[2]);
						$sender->sendMessage(Core::PREFIX . "Removed the Line " . $args[2] . " of the Scoreboard " . $args[2]);
						return true;
					}
				break;
				case "remove":
					if(count($args) < 3) {
						$sender->sendMessage(Core::ERROR_PREFIX . "Usage: /scoreboard remove <player / all> <title>");
						return false;
					}
					if(ScoreboardManager::getId($args[2]) === null) {
						$sender->sendMessage(Core::ERROR_PREFIX . "There is no Scoreboard with the name " . $args[1]);
						return false;
					} else {
						$scoreboard = new \scoreboard\Scoreboard($args[2], ScoreboardAction::MODIFY);

						if($args[1] === "all") {
							foreach(Server::getInstance()->getOnlinePlayers() as $p) {
								if($p instanceof CorePlayer) {
									$scoreboard->removeDisplay($p);
								}
							}
							$sender->sendMessage(Core::PREFIX . "Removed the display of the Scoreboard " . $args[2] . " for all the online players");
							return true;
						} else {
							$p = Server::getInstance()->getPlayer($args[1]);

							if(!$p instanceof CorePlayer) {
								$sender->sendMessage(Core::ERROR_PREFIX . $p . " is not a Online");
							}
							$scoreboard->removeDisplay($p);
							$sender->sendMessage(Core::PREFIX . "Removed the display of the Scoreboard " . $args[2] . " for " . $p->getName());
							return true;
						}
					}
				break;
				case "rename":
					if(count($args) < 3) {
						$sender->sendMessage(Core::ERROR_PREFIX . "Usage: /scoreboard rename <old name> <new name>");
						return false;
					}
					if(ScoreboardManager::getId($args[1]) === null) {
						$sender->sendMessage(Core::ERROR_PREFIX . "There is no Scoreboard with the name " . $args[1]);
						return false;
					} else {
						$scoreboard = new \scoreboard\Scoreboard($args[1], ScoreboardAction::MODIFY);
						$scoreboard->rename($args[1], $args[2]);
						$sender->sendMessage(Core::PREFIX . "Renamed the Scoreboard with name " . $args[1] . " to " . $args[2] . " and re sent it to all viewers");
						return true;
					}
				break;
				case "clearLines":
					if(count($args) < 2) {
						$sender->sendMessage(Core::ERROR_PREFIX . "Usage: /scoreboard clearLines <name>");
						return false;
					}
					if(ScoreboardManager::getId($args[1]) === null) {
						$sender->sendMessage(Core::ERROR_PREFIX . "There is no Scoreboard with the name " . $args[1]);
						return false;
					} else {
						$scoreboard = new \scoreboard\Scoreboard($args[1], ScoreboardAction::MODIFY);
						$scoreboard->removeLines();
						$sender->sendMessage(Core::PREFIX . "Cleared the Lines of the Scoreboard with name " . $args[1]);
						return true;
					}
				break;
			}
			return true;
		}
	}
}