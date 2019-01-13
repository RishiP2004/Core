<?php
/**
 *    ___________________________
 *   /  _____/\______   \_   ___ \  ___________   ____
 *  /   \  ___ |     ___/    \  \/ /  _ \_  __ \_/ __ \
 *  \    \_\  \|    |   \     \___(  <_> )  | \/\  ___/
 *   \______  /|____|    \______  /\____/|__|    \___  >
 *          \/                  \/                   \/
 */
namespace GPCore\Essentials\Defaults\Commands;

use GPCore\GPCore;

use GPCore\Stats\Objects\GPPlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\entity\{
    Effect,
    EffectInstance
};

class EffectCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("effect", $GPCore);

        $this->GPCore = $GPCore;

        $this->setPermission("GPCore.Essentials.Defaults.Command.Effect");
        $this->setUsage("<effect : clear> <player> [seconds] [amplifier] [hideParticles]");
        $this->setDescription("Add a Particle");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 2) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Usage: /effect" . " " . $this->getUsage());
            return false;
        }
        $user = $this->GPCore->getStats()->getGPUser($args[1]);

        if(!$user->hasAccount()) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[0] . " is not a valid Player");
            return false;
        }
        $player = $user->getGPPlayer();

        if(!$player instanceof GPPlayer) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . $user->getUsername() . " is not Online");
        }
        if(isset($args[0]) === "clear") {
            if(!$sender->hasPermission($this->getPermission() . ".Other")) {
                $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
                return false;
            } else {
                foreach($this->GPCore->getServer()->getPlayer($args[1])->getEffects() as $effect) {
                    $player->removeEffect($effect->getId());
                    $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Cleared " . $user->getUsername() . "'s Effects");
                    $player->sendMessage($this->GPCore->getBroadcast()->getPrefix() . $sender->getName() . " Cleared your Effects");
                }
                return true;
            }
        }
        $effect = Effect::getEffectByName($args[0]);

        if($effect === null) {
            $effect = Effect::getEffect($args[0]);
        }
        if($effect === null) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[0] . " is not a valid Effect");
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
                $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[3] . " is too Big of an Amplifier");
                return false;
            }
            if($amplification < 0) {
                $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[3] . " is too Big of an Amplifier");
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
                $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $user->getUsername() . " doesn't have the Effect " . $effect->getName());
                return false;
            }
            $player->removeEffect($effect->getId());
            $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Removed the Effect " . $effect->getName() . " from " . $user->getUsername());
            $player->sendMessage($this->GPCore->getBroadcast()->getPrefix() . $sender->getName() . " Removed the Effect " . $effect->getName() . " from you");
            return true;
        } else {
            $effectInstance = new EffectInstance($effect, $duration, $amplification, $visible);

            $player->addEffect($effectInstance);
            $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Added the Effect " . $effect->getName() . " to " . $user->getUsername() . " for " . $duration . " Seconds, " . $amplification . " Amplifier and Hidden: " . $visible);
            $player->sendMessage($this->GPCore->getBroadcast()->getPrefix() . $sender->getName() . " Removed the Effect " . $effect->getName() . " from you");
            return true;
        }
    }
}