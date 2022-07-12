<?php

declare(strict_types = 1);

namespace core\essential\command;

use core\Core;

use core\player\CorePlayer;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\constraint\InGameRequiredConstraint;

use pocketmine\command\CommandSender;

class HudCommand extends BaseCommand {
	public function prepare() : void {
		$this->setPermission("hud.command");
		$this->addConstraint(new InGameRequiredConstraint($this));
		$this->registerArgument(0, new RawStringArgument("type", false));
		$this->registerArgument(1, new RawStringArgument("value", true));
	}

	public function onRun(CommandSender $sender, string $commandLabel, array $args) : void {
		if(isset($args["value"])) {
			switch(strtolower($args["value"])) {
				case "true":
				case "on":
					$value = true;
				break;
				case "false":
				case "off":
					$value = false;
				break;
				default:
					$sender->sendMessage(Core::ERROR_PREFIX . $args["value"] . " is not a valid Boolean");
					return;
			}
		}
		switch(strtolower($args["type"])) {
			case "popup":
			case "bottom":
			case (int) CorePlayer::POPUP:
				$type = CorePlayer::POPUP;
				$nameType = "Popup";
			break;
			case "scoreboard":
			case "side":
			case (int) CorePlayer::SCOREBOARD:
				$type = CorePlayer::SCOREBOARD;
				$nameType = "Scoreboard";
			break;
			default:
				$sender->sendMessage(Core::ERROR_PREFIX . $args["type"] . " is not a valid Hud Type. Types: Scoreboard, Popup");
				return;
			break;
		}
		if(isset($args["value"])) {
			$hud = $args["value"];
		} else {
			$hud = $sender->hasHud($type) === true ? false : true;
		}
		$sender->setHud($type, $hud);

		$str = $sender->hasHud($type) === true ? "True" : "False";

		$sender->sendMessage(Core::PREFIX . "Set your " . $nameType . " Hud to " . $str);
		return;
	}
}