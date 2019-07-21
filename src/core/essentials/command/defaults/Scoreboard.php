<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;
use core\CorePlayer;

use core\mcpe\scoreboard\ScoreboardAction;

use pocketmine\command\{
	PluginCommand,
	CommandSender
};

class Scoreboard extends PluginCommand {
	private $core;

	public function __construct(Core $core) {
		parent::__construct("scoreboard", $core);

		$this->core = $core;

		$this->setAliases(["sb"]);
		$this->setPermission("core.essentials.defaults.scoreboard.command");
		$this->setUsage("<create : delete : add : remove : setLine : removeLine>");
		$this->setDescription("Scoreboard Command");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
		if(!$sender->hasPermission($this->getPermission())) {
			$sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
			return false;
		}
		if(count($args) < 1) {
			$sender->sendMessage($this->core->getErrorPrefix() . "Usage: /scoreboard " . $this->getUsage());
			return false;
		} else {
			switch(strtolower($args[0])) {
				case "create":
					if(count($args) < 4) {
						$sender->sendMessage($this->core->getErrorPrefix() . "Usage: /scoreboard create <title> <displaySlot (sidebar/list/belowname)> <sortOrder (0->ascending/1->descending)>");
						return false;
					}
					if($this->core->getMCPE()->getScoreboardManager()->getId($args[1]) !== null) {
						$sender->sendMessage($this->core->getErrorPrefix() . "This scoreboard named . " . $args[1]. " already exists");
						return false;
					}
					if(!is_numeric($args[3])) {
						$sender->sendMessage($this->core->getErrorPrefix() . "The sort order needs to be 0/1");
						return false;
					} else {
						$scoreboard = new \core\mcpe\scoreboard\Scoreboard($args[1], ScoreboardAction::CREATE);
						$scoreboard->create($args[2], $args[3]);
						$sender->sendMessage($this->core->getPrefix() . "Successfully Created Scoreboard " . $args[1]);
						return true;
					}
				break;
				case "delete":
					if(count($args) < 2) {
						$sender->sendMessage($this->core->getErrorPrefix() . "Usage: /scoreboard delete <title>");
						return false;
					}
					if($this->core->getMCPE()->getScoreboardManager()->getId($args[1]) === null) {
						$sender->sendMessage($this->core->getErrorPrefix() . "There is no Scoreboard with the name " . $args[1]);
						return false;
					} else {
						$scoreboard = new \core\mcpe\scoreboard\Scoreboard($args[1], ScoreboardAction::MODIFY);
						$scoreboard->delete();
						$sender->sendMessage($this->core->getPrefix() . "Successfully Deleted Scoreboard " . $args[1] . ".");
						return true;
					}
				break;
				case "add":
					if(count($args) < 3) {
						$sender->sendMessage($this->core->getErrorPrefix() . "Usage: /scoreboard add <player / all> <title>");
						return false;
					}
					if($this->core->getMCPE()->getScoreboardManager()->getId($args[2]) === null) {
						$sender->sendMessage($this->core->getErrorPrefix() . "There is no Scoreboard with the name " . $args[1]);
						return false;
					} else {
						$scoreboard = new \core\mcpe\scoreboard\Scoreboard($args[2], ScoreboardAction::MODIFY);

						if($args[1] === "all") {
							foreach($this->core->getServer()->getOnlinePlayers() as $p) {
								if($p instanceof CorePlayer) {
									$scoreboard->addDisplay($p);
								}
							}
							$sender->sendMessage($this->core->getPrefix() . "Sent Scoreboard" . $args[2] . " to all the online players");
							return true;
						} else {
							$p = $this->core->getServer()->getPlayer($args[1]);

							if(!$p instanceof CorePlayer) {
								$sender->sendMessage($this->core->getErrorPrefix() . $p . " is not Online");
							}
							$scoreboard->addDisplay($p);
							$sender->sendMessage($this->core->getPrefix() . "Sent " . $args[2] . " Scoreboard to  " . $p->getName());
							return true;
						}
					}
				break;
				case "setLine":
					if(count($args) < 4) {
						$sender->sendMessage($this->core->getErrorPrefix() . "Usage: /scoreboard setLine <title of the scoreboard> <line> <message>");
						return false;
					}
					if($this->core->getMCPE()->getScoreboardManager()->getId($args[1]) === null) {
						$sender->sendMessage($this->core->getErrorPrefix() . "There is no Scoreboard with the name " . $args[1]);
						return false;
					}
					if(!is_numeric($args[2])) {
						$sender->sendMessage($args[2] . " is not a valid Number");
						return false;
					}
					if(!((int) $args[2] >= 1 && (int) $args[2] <= 15)) {
						$sender->sendMessage($this->core->getErrorPrefix() . $args[2] . " should be between 1 and 9");
						return false;
					} else {
						$scoreboard = new \core\mcpe\scoreboard\Scoreboard($args[2], ScoreboardAction::MODIFY);
						$scoreboard->setLine((int) $args[2], implode(" ", array_slice($args, 3)));
						$sender->sendMessage($this->core->getPrefix() . "Set Line number " . $args[2] . " of Scoreboard " . $args[1]);
						return true;
					}
				break;
				case "removeLine":
					if(count($args) < 3) {
						$sender->sendMessage($this->core->getErrorPrefix() . "Usage: /scoreboard removemLine <title of the scoreboard> <line>");
						return false;
					}
					if($this->core->getMCPE()->getScoreboardManager()->getId($args[1]) === null) {
						$sender->sendMessage($this->core->getErrorPrefix() . "There is no Scoreboard with the name " . $args[1]);
						return false;
					}
					if(!is_numeric($args[2])) {
						$sender->sendMessage($this->core->getErrorPrefix() . $args[2] . " is not a valid Number");
						return false;
					}
					if(!((int) $args[2] >= 1 && (int) $args[2] <= 15)) {
						$sender->sendMessage($this->core->getErrorPrefix() . $args[2] . " should be between 1 and 9");
						return false;
					}
					if(!$this->core->getMCPE()->getScoreboardManager()->entryExist((string) $this->core->getMCPE()->getScoreboardManager()->getId($args[1]), $args[2])) {
						$sender->sendMessage($this->core->getErrorPrefix() . "Scoreboard " . $args[1] . "doesn't have line number " . $args[2]);
						return false;
					} else {
						$scoreboard = new \core\mcpe\scoreboard\Scoreboard($args[1], ScoreboardAction::MODIFY);
						$scoreboard->removeLine($args[2]);
						$sender->sendMessage($this->core->getPrefix() . "Removed the Line " . $args[2] . " of the Scoreboard " . $args[2]);
						return true;
					}
				break;
				case "remove":
					if(count($args) < 3) {
						$sender->sendMessage($this->core->getErrorPrefix() . "Usage: /scoreboard remove <player / all> <title>");
						return false;
					}
					if($this->core->getMCPE()->getScoreboardManager()->getId($args[1]) === null) {
						$sender->sendMessage($this->core->getErrorPrefix() . "There is no Scoreboard with the name " . $args[1]);
						return false;
					} else {
						$scoreboard = new \core\mcpe\scoreboard\Scoreboard($args[2], ScoreboardAction::MODIFY);

						if($args[1] === "all") {
							foreach($this->core->getServer()->getOnlinePlayers() as $p) {
								if($p instanceof CorePlayer) {
									$scoreboard->removeDisplay($p);
								}
							}
							$sender->sendMessage($this->core->getPrefix() . "Removed the display of the Scoreboard " . $args[2] . " for all the online players");
							return true;
						} else {
							$p = $this->core->getServer()->getPlayer($args[1]);

							if(!$p instanceof CorePlayer) {
								$sender->sendMessage($this->core->getErrorPrefix() . $p . " is not a Online");
							}
							$scoreboard->removeDisplay($p);
							$sender->sendMessage($this->core->getPrefix() . "Removed the display of the Scoreboard " . $args[2] . " for " . $p->getName());
							return true;
						}
					}
				break;
				case "rename":
					if(count($args) < 3) {
						$sender->sendMessage($this->core->getErrorPrefix() . "Usage: /scoreboard rename <old name> <new name>");
						return false;
					}
					if($this->core->getMCPE()->getScoreboardManager()->getId($args[1]) === null) {
						$sender->sendMessage($this->core->getErrorPrefix() . "There is no Scoreboard with the name " . $args[1]);
						return false;
					} else {
						$scoreboard = new \core\mcpe\scoreboard\Scoreboard($args[1], ScoreboardAction::MODIFY);
						$scoreboard->rename($args[1], $args[2]);
						$sender->sendMessage($this->core->getPrefix() . "Renamed the Scoreboard with name " . $args[1] . " to " . $args[2] . " and re sent it to all viewers");
						return true;
					}
				break;
				case "clearLines":
					if(count($args) < 2) {
						$sender->sendMessage($this->core->getErrorPrefix() . "Usage: /scoreboard clearLines <name>");
						return false;
					}
					if($this->core->getMCPE()->getScoreboardManager()->getId($args[1]) === null) {
						$sender->sendMessage($this->core->getErrorPrefix() . "There is no Scoreboard with the name " . $args[1]);
						return false;
					} else {
						$scoreboard = new \core\mcpe\scoreboard\Scoreboard($args[1], ScoreboardAction::MODIFY);
						$scoreboard->removeLines();
						$sender->sendMessage($this->core->getPrefix() . "Cleared the Lines of the Scoreboard with name " . $args[1]);
						return true;
					}
				break;
			}
			return true;
		}
	}
}