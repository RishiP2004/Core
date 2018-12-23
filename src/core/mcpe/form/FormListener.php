<?php

namespace core\mcpe\form;

use core\Core;
use core\CorePlayer;

use pocketmine\event\Listener;

use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;

class FormListener implements Listener {
    private $core;

    public function __construct(Core $core) {
        $this->core = $core;
    }
	
    public function onDataPacketReceive(DataPacketReceiveEvent $event) {
        $packet = $event->getPacket();
        $player = $event->getPlayer();
		
		if($player instanceof CorePlayer) {
			if($packet instanceof ModalFormResponsePacket) {
				$id = $packet->formId;
				$data = json_decode($packet->formData);
				
				$player->onFormSubmit($id, $data);
			}
		}
    }
}