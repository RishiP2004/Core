<?php

namespace core\essentials\command\defaults;

use core\Core;

use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\entity\EffectInstance;

class Effect extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("effect", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.defaults.effect.command");
        $this->setUsage("<effect : clear> <player> [seconds] [amplifier] [hideParticles]");
        $this->setDescription("Add a Particle");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 2) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /effect" . " " . $this->getUsage());
            return false;
        }
        $player = $this->core->getServer()->getPlayer($args[1]);

        if(!$player instanceof CorePlayer) {
            $sender->sendMessage($this->core->getErrorPrefix() . $args[1] . " is not Online");
            return false;
        }
        if(!$user = $this->core->getStats()->getCoreUser($args[1])) {
            $sender->sendMessage($this->core->getErrorPrefix() . $args[1] . " is not a valid Player");
            return false;
        }
        if(isset($args[0]) === "clear") {
            if(!$sender->hasPermission($this->getPermission() . ".Other")) {
                $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
                return false;
            } else {
                foreach($this->core->getServer()->getPlayer($args[1])->getEffects() as $effect) {
                    $player->removeEffect($effect->getId());
                    $sender->sendMessage($this->core->getPrefix() . "Cleared " . $user->getName() . "'s Effects");
                    $player->sendMessage($this->core->getPrefix() . $sender->getName() . " Cleared your Effects");
                }
                return true;
            }
        }
        $effect = \pocketmine\entity\Effect::getEffectByName($args[0]);

        if($effect === null) {
            $effect = \pocketmine\entity\Effect::getEffect($args[0]);
        }
        if($effect === null) {
            $sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Effect");
            return false;
        }
        $amplification = 0;

        if(isset($args[3])) {
            $duration = ($args[2]) * 20;
        } else {
            $duration = $effect->getDefaultDuration();
        }
        if(isset($args[4])) {
            $amplification = $args[3];

            if($amplification > 255) {
                $sender->sendMessage($this->core->getErrorPrefix() . $args[3] . " is too Big of an Amplifier");
                return false;
            }
            if($amplification < 0) {
                $sender->sendMessage($this->core->getErrorPrefix() . $args[3] . " is too Big of an Amplifier");
                return false;
            }
            return true;
        }
        $visible = true;

        if(isset($args[5])) {
            $v = strtolower($args[5]);

            if($v === "on" or $v === "true") {
                $visible = false;
            }
        }
        if($duration === 0) {
            if(!$player->hasEffect($effect->getId())) {
                $sender->sendMessage($this->core->getErrorPrefix() . $user->getName() . " doesn't have the Effect " . $effect->getName());
                return false;
            }
            $player->removeEffect($effect->getId());
            $sender->sendMessage($this->core->getPrefix() . "Removed the Effect " . $effect->getName() . " from " . $user->getName());
            $player->sendMessage($this->core->getPrefix() . $sender->getName() . " Removed the Effect " . $effect->getName() . " from you");
            return true;
        } else {
            $effectInstance = new EffectInstance($effect, $duration, $amplification, $visible);

            $player->addEffect($effectInstance);
            $sender->sendMessage($this->core->getPrefix() . "Added the Effect " . $effect->getName() . " to " . $user->getName() . " for " . $duration . " Seconds, " . $amplification . " Amplifier and Hidden: " . $visible);
            $player->sendMessage($this->core->getPrefix() . $sender->getName() . " Removed the Effect " . $effect->getName() . " from you");
            return true;
        }
    }
}