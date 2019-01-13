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

use pocketmine\math\Vector3;

use pocketmine\utils\Random;

class ParticleCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("particle", $GPCore);

        $this->GPCore = $GPCore;

        $this->setPermission("GPCore.Essentials.Defaults.Command.Particle");
        $this->setUsage("<name> <x> <y> <z> <xd> <yd> <zd> [count] [data]");
        $this->setDescription("Add a Particle");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 7) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Usage: /particle" . " " . $this->getUsage());
            return false;
        }
        if($sender instanceof GPPlayer) {
            $level = $sender->getLevel();
        } else {
            $level = $sender->getServer()->getDefaultLevel();
        }
        $name = strtolower($args[0]);
        $position = new Vector3($args[1], $args[2], $args[3]);
        $xd = $args[4];
        $yd = $args[5];
        $zd = $args[6];
        $count = isset($args[7]) ? max(1, (int) $args[7]) : 1;
        $data = isset($args[8]) ? (int) $args[8] : null;
        $particle = $this->GPCore->getEssentials()->getDefaults()->getParticle($name, $position, $xd, $yd, $zd, $data);

        if($particle === null) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . $args[0] . " is not a valid Particle");
            return true;
        }
        $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Added the Particle " . $args[0]);

        $random = new Random(microtime(true) * 1000 + mt_rand());

        for($i = 0; $i < $count; ++$i) {
            $particle->setComponents($position->x + $random->nextSignedFloat() * $xd, $position->y + $random->nextSignedFloat() * $yd, $position->z + $random->nextSignedFloat() * $zd);
            $level->addParticle($particle);
        }
        return true;
    }
}