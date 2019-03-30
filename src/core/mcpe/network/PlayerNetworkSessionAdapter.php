<?php

namespace core\mcpe\network;

use core\CorePlayer;

use pocketmine\Server;

use pocketmine\network\mcpe\protocol\PlayerInputPacket;

class PlayerNetworkSessionAdapter extends \pocketmine\network\mcpe\PlayerNetworkSessionAdapter {
	/** @var Server */
	protected $server;
	/** @var CorePlayer */
	protected $player;

	public function __construct(Server $server, CorePlayer $player) {
		parent::__construct($server, $player);

		$this->server = $server;
		$this->player = $player;
	}

	public function handlePlayerInput(PlayerInputPacket $packet) : bool {
		return $this->player->handlePlayerInput($packet);
	}
}