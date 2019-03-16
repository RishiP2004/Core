<?php

namespace core\essentials\command\defaults;

use core\Core;

use core\CorePlayer;

use core\utils\{
    PocketMine,
    Entity
};

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\math\Vector3;

use pocketmine\level\Level;

use pocketmine\utils\Random;

class Particle extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("particle", $core);

        $this->core = $core;

        $this->setPermission("core.essentials.defaults.particle.command");
        $this->setUsage("<name> [x] [y] [z] [xd] [yd] [zd] [count] [data]");
        $this->setDescription("Add a Particle");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1 && !$sender instanceof CorePlayer) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /particle" . " " . $this->getUsage());
            return false;
        }
        if($sender instanceof CorePlayer && count($args) < 1) {
            $level = $sender->getLevel();

            $position = new Vector3(PocketMine::getRelativeDouble($sender->getX(), $sender, $args[1]), PocketMine::getRelativeDouble($sender->getY(), $sender, $args[2], 0, Level::Y_MAX), PocketMine::getRelativeDouble($sender->getZ(), $sender, $args[3]));
        } else {
            $level = $this->core->getServer()->getLevelManager()->getDefaultLevel();
            $position = new Vector3((float) $args[1], (float) $args[2], (float) $args[3]);
        }
        $name = strtolower($args[0]);
        $xd = (float) $args[4];
        $yd = (float) $args[5];
        $zd = (float) $args[6];
        $count = isset($args[7]) ? max(1, (int) $args[7]) : 1;
        $data = isset($args[8]) ? (int) $args[8] : null;
        $particle = Entity::getParticle($name, $data);

        if($particle === null) {
            $sender->sendMessage($this->core->getErrorPrefix() . $name . " is not a valid Particle");
            return true;
        }
        $sender->sendMessage($this->core->getPrefix() . "Playing Particle " . $name . " for " . $count . " times");

        $random = new Random((int) (microtime(true) * 1000) + mt_rand());

        for($i = 0; $i < $count; ++$i) {
            $level->addParticle($position->add($random->nextSignedFloat() * $xd, $random->nextSignedFloat() * $yd, $random->nextSignedFloat() * $zd), $particle);
        }
        return true;
    }
}