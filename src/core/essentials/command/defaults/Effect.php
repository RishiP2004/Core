<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;
use core\CorePlayer;

use core\essentials\Essentials;

use pocketmine\Server;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\entity\EffectInstance;

class Effect extends PluginCommand {
    private $manager;

    public function __construct(Essentials $manager) {
        parent::__construct("effect", Core::getInstance());

        $this->manager = $manager;

        $this->setPermission("core.essentials.defaults.command.effect");
        $this->setUsage("<player> <effect : clear> [seconds] [amplifier] [hideParticles]");
        $this->setDescription("Add an Effect to you or another Player");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 2) {
            $sender->sendMessage(Core::ERROR_PREFIX . "Usage: /effect " . $this->getUsage());
            return false;
        }
        $player = Server::getInstance()->getPlayer($args[0]);

        if(!$player instanceof CorePlayer) {
            $sender->sendMessage(Core::ERROR_PREFIX . $args[0] . " is not Online");
            return false;
        }
        if(isset($args[1]) === "clear") {
            if(!$sender->hasPermission($this->getPermission() . ".other")) {
                $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
                return false;
            } else {
                foreach(Server::getInstance()->getPlayer($args[1])->getEffects() as $effect) {
                    $player->removeEffect($effect->getId());
                    $sender->sendMessage(Core::PREFIX . "Cleared " . $player->getName() . "'s Effects");
                    $player->sendMessage(Core::PREFIX . $sender->getName() . " Cleared your Effects");
                }
                return true;
            }
        } 
        $effect = \pocketmine\entity\Effect::getEffectByName($args[1]);

        if($effect === null) {
            $effect = \pocketmine\entity\Effect::getEffect((int) $args[1]);
        }
        if($effect === null) {
            $sender->sendMessage(Core::ERROR_PREFIX . $args[1] . " is not a valid Effect");
            return false;
        }
        $amplification = 0;

		if(count($args) >= 3){
			if(($d = $this->getBoundedInt($sender, $args[2], 0, (int) (INT32_MAX / 20))) === null) {
				$sender->sendMessage(Core::ERROR_PREFIX . $args[2] . " is too Big of a Duration");
				return false;
			}
			$duration = $d * 20; 
		} else {
			$duration = null;
		}
		if(count($args) >= 4) {
			$amplification = $this->getBoundedInt($sender, $args[3], 0, 255);

			if($amplification === null) {
				$sender->sendMessage(Core::ERROR_PREFIX . $args[3] . " is too Big of an Amplifier");
				return false;
			}
		}
        $visible = true;

        if(isset($args[5])) {
            $v = strtolower($args[5]);

            if($v === "on" or $v === "true" or $v = 1) {
                $visible = false;
            }
        }
        if($duration === 0) {
            if(!$player->hasEffect($effect->getId())) {
                $sender->sendMessage(Core::ERROR_PREFIX . $player->getName() . " doesn't have the Effect " . $effect->getName());
                return false;
            }
            $player->removeEffect($effect->getId());
            $sender->sendMessage(Core::PREFIX . "Removed the Effect " . $effect->getName() . " from " . $player->getName());
            $player->sendMessage(Core::PREFIX . $sender->getName() . " Removed the Effect " . $effect->getName() . " from you");
            return true;
        } else {
            $effectInstance = new EffectInstance($effect, $duration, $amplification, $visible);
			$str = $visible === false ? "True" : "False";
			
            $player->addEffect($effectInstance);
            $sender->sendMessage(Core::PREFIX . "Added the Effect " . $effect->getName() . " to " . $player->getName() . " for " . $duration . " Seconds, " . $amplification . " Amplifier and Hidden: " . $str);
            $player->sendMessage(Core::PREFIX . $sender->getName() . " Removed the Effect " . $effect->getName() . " from you");
            return true;
        }
    }
}