<?php

declare(strict_types=1);

namespace core\player\form;

use core\Core;

use core\network\NetworkManager;

use core\player\{
	CorePlayer,
	CoreUser
};

use core\player\form\subForm\GlobalProfileForm;

use dktapps\pmforms\{
	MenuForm,
	MenuOption,
	FormIcon
};

use pocketmine\player\Player;

use pocketmine\utils\TextFormat;

class ProfileForm extends MenuForm {
	public function getTitle() : string {
		return $per = null ? $this->user->getName() . "'s Profile" : "Your Profile";
	}

	public function getText() : string {
		return TextFormat::LIGHT_PURPLE . "Pick an option";
	}

	public function getOptions() : array {
		$b1 = new MenuOption(TextFormat::GRAY . "Global");
		$b2 = new MenuOption(TextFormat::GRAY . "Lobby", new FormIcon(NetworkManager::getInstance()->getServer("Lobby")->getIcon(), FormIcon::IMAGE_TYPE_URL));
		//$b3 = new MenuOption(TextFormat::GRAY . "HCF", new FormIcon(NetworkManager::getInstance()->getServer("HCF")->getIcon(), FormIcon::IMAGE_TYPE_URL));
		$options = [$b1, $b2];
		return $options;
	}

	public function onSubmit() : \Closure {
		return function(Player $submitter, int $selected) : void {
			if($submitter instanceof CorePlayer) {
				switch($selected) {
					case 0:
						new GlobalProfileForm($submitter, $this->user);
					break;
					case 1:
						//new LobbyProfileForm($submitter, $this->user);
					break;
					case 2:
					break;
				}
			}
		};
	}

	public function onClose() : \Closure {
		return function(Player $submitter) : void {
			$submitter->sendMessage(Core::PREFIX . "Closed Profile menu");
		};
	}

	public function __construct(CorePlayer $player, private ?CoreUser $user = null) {
		parent::__construct($this->getTitle(), $this->getText(), $this->getOptions(), $this->onSubmit(), $this->onClose());

		$player->sendForm($this);
		$player->sendMessage(Core::PREFIX . "Opened Profile menu");
	}
}