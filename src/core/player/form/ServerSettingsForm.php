<?php

declare(strict_types=1);

namespace core\player\form;

use core\Core;

use core\player\CorePlayer;

use core\network\server\Server;

use dktapps\pmforms\{
	element\Label,
	FormIcon
};

use pocketmine\player\Player;

use pocketmine\utils\TextFormat;

class ServerSettingsForm extends \dktapps\pmforms\ServerSettingsForm {
	public function getTitle() : string {
		return Core::PREFIX . "Athena Settings";
	}

	public function getElements() : array {
		$l = new Label("Coming Soon", TextFormat::GRAY . "Coming Soon!");
		return [$l];
	}

	public function getImage() : FormIcon {
		return new FormIcon("http://icons.iconarchive.com/icons/double-j-design/diagram-free/128/settings-icon.png", FormIcon::IMAGE_TYPE_URL);
	}

	public function onSubmit() : \Closure {
		return function(Player $submitter, int $selected) : void {
		};
	}

	public function onClose() : \Closure {
		return function(Player $submitter) : void {
		};
	}

	public function __construct(CorePlayer $player, private Server $server) {
		parent::__construct($this->getTitle(), $this->getElements(), $this->getIcon(), $this->onSubmit(), $this->onClose());

		$player->sendForm($this);
	}
}