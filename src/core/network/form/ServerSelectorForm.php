<?php

declare(strict_types=1);

namespace core\network\form;

use core\Core;

use core\network\NetworkManager;
use core\network\server\Server;

use core\player\CorePlayer;

use dktapps\pmforms\{
	MenuForm,
	MenuOption,
	FormIcon
};

use pocketmine\player\Player;

use pocketmine\utils\TextFormat;

class ServerSelectorForm extends MenuForm {
	private array $servers = [];

	public function getTitle() : string {
		return TextFormat::GOLD . "Server";
	}

	public function getText() {
		return TextFormat::LIGHT_PURPLE . "Pick a Server";
	}

	public function getOptions() : array {
		$options = [];
		$i = 0;

		foreach(NetworkManager::getInstance()->getServers() as $server) {
			if($server instanceof Server) {
				if(!$server->isOnline()) {
					$onlinePlayers = "";
					$maxSlots = "";
					$online = "No";

					if($server->isWhitelisted()) {
						$online = "Whitelisted";
					}
				} else {
					$onlinePlayers = "Players: " . count($server->getOnlinePlayers()) . "/";
					$maxSlots = $server->getMaxSlots();
					$online = "Yes";
				}
				$name = TextFormat::GRAY . $server->getName() . "\n" . TextFormat::GRAY . "Online: " . $online . "\n" . TextFormat::GRAY . $onlinePlayers . $maxSlots;

				if(empty($server->getIcon())) {
					$b = new MenuOption($name);
				} else {
					$b = new MenuOption($name, new FormIcon($server->getIcon(), FormIcon::IMAGE_TYPE_URL));
				}
				$options[] = $b;
				$this->servers[$i] = $server;
				$i++;
			}
		}
		return $options;
	}

	public function onSubmit() {
		return function(Player $submitter, int $selected) : void {
			if($submitter instanceof CorePlayer) {
				$server = NetworkManager::getInstance()->getServer($this->servers[$selected]);

				if($server instanceof Server) {
					if(!$submitter->hasPermission("network." . $server->getName())) {
						$submitter->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Server");
					}
					if($server->isWhitelisted() && !$submitter->hasPermission("network." . $server->getName() . ".whitelist")) {
						$submitter->sendMessage(Core::ERROR_PREFIX . $server->getName() . " is Whitelisted");
					} else {
						$submitter->transfer($server->getIp() . $server->getPort());
						$submitter->sendMessage(Core::PREFIX . "Transferring to the Server " . $server->getName());
					}
				}
			}
		};
	}

	public function onClose() {
		return function(Player $submitter) : void {
			$submitter->sendMessage(Core::PREFIX . "Closed Server Selector menu");
		};
	}

	public function __construct(CorePlayer $player) {
		parent::__construct($this->getTitle(), $this->getText(), $this->getOptions(), $this->onSubmit(), $this->onClose());

		$player->sendForm($this);
		$player->sendMessage(Core::PREFIX . "Opened Servers Command menu");
	}
}