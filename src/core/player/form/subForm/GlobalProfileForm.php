<?php

declare(strict_types=1);

namespace core\player\form\subForm;

use core\Core;

use core\player\{
	CorePlayer,
	CoreUser,
	Statistics
};

use dktapps\pmforms\{
	CustomForm,
	CustomFormResponse,
	element\Label
};

use pocketmine\player\Player;

use pocketmine\utils\TextFormat;

class GlobalProfileForm extends CustomForm {
	public function getTitle() : string {
		return $this->user !== null ? $this->user->getName() . "'s Global Profile" : "Your Global Profile";
	}

	public function getElements() : array {
		if(!is_null($this->user)) {
			if(!is_null($this->user->getServer())) {
				$server = $this->user->getServer()->getName();
			} else {
				$server = "Offline";
			}
			$rank = $this->user->getRank()->getFormat();
			$coins = $this->user->getCoins();
		} else {
			$server = $this->player->getCoreUser()->getServer()->getName();
			$rank = $this->player->getCoreUser()->getRank()->getFormat();
			$coins = $this->player->getCoreUser()->getCoins();
		}
		$l1 = new Label("Rank Label", TextFormat::GRAY . "Rank: " . $rank);
		$l2 = new Label("Coins Label", TextFormat::GRAY . "Coins: " . Statistics::COIN_UNIT . $coins);
		$l3 = new Label("Server Label", TextFormat::GRAY . "Server: " . $server);
		return [$l1, $l2, $l3];
	}

	public function onSubmit() : \Closure {
		return function(Player $submitter, CustomFormResponse $response) : void {
		};
	}

	public function onClose() : \Closure {
		return function(Player $submitter) : void {
			$submitter->sendMessage(Core::PREFIX . "Closed Profile menu");
		};
	}

	public function __construct(private CorePlayer $player, private ?CoreUser $user = null) {
		parent::__construct($this->getTitle(), $this->getElements(), $this->onSubmit(), $this->onClose());

		$player->sendForm($this);
		$player->sendMessage(Core::PREFIX . "Opened Global Profile menu");
	}
}