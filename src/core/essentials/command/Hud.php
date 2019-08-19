<?php

declare(strict_types = 1);

namespace core\essentials\command;

use core\Core;

use core\CorePlayer;

use pocketmine\command\{
	PluginCommand,
	CommandSender
};

class Hud extends PluginCommand {
	private $core;

	public function __construct(Core $core) {
		parent::__construct("hud", $core);

		$this->core = $core;

		$this->setPermission("core.essentials.command.hud");
		$this->setUsage("<type> [value]");
		$this->setDescription("Set a Hud Type on or Off");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
		if(!$sender->hasPermission($this->getPermission())) {
			$sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
			return false;
		}
		if(!$sender instanceof CorePlayer) {
			$sender->sendMessage($this->core->getErrorPrefix() . "You must be a Player to use this Command");
			return false;
		}
		if(count($args) < 1) {
			$sender->sendMessage($this->core->getErrorPrefix() . "Usage: /hud " . $this->getUsage());
			return false;
		} else {
			if(isset($args[1])) {
				switch(strtolower($args[1])) {
					case "true":
					case "on":
						$value = true;
					break;
					case "false":
					case "off":
						$value = false;
					break;
					default:
						$sender->sendMessage($this->core->getErrorPrefix() . $args[1] . " is not a valid Boolean");
						return false;
					break;
				}
			}
			switch(strtolower($args[0])) {
				case "popup":
				case "bottom":
				case (int) CorePlayer::POPUP:
					$type = CorePlayer::POPUP;

					if(isset($args[1])) {
						$hud = $value;
					} else {
						$hud = $sender->hasHud($type) === true ? false : true;
					}
					$sender->setHud($type, $hud);

					$str = $sender->hasHud($type) === true ? "True" : "False";

					$sender->sendMessage($this->core->getPrefix() . "Set your Popup Hud to " . $str);
					return true;
				break;
				case "scoreboard":
				case "side":
				case (int) CorePlayer::SCOREBOARD:
					$type = CorePlayer::SCOREBOARD;

					if(isset($args[1])) {
						$hud = $value;
					} else {
						$hud = $sender->hasHud($type) === true ? false : true;
					}
					$sender->setHud($type, $hud);

					$str = $sender->hasHud($type) === true ? "True" : "False";
					
					$sender->sendMessage($this->core->getPrefix() . "Set your Scoreboard Hud to " . $str);
					return true;
				break;
				default:
					$sender->sendMessage($this->core->getErrorPrefix() . $value . " is not a valid Hud Type. Types: Scoreboard, Popup");
					return false;
				break;
			}
		}
	}
}