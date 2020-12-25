<?php

declare(strict_types = 1);

namespace core\stats\command;

use core\Core;

use core\stats\Stats;

use core\stats\rank\Rank;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\utils\TextFormat;

class Ranks extends PluginCommand {
    private $manager;
    
    public function __construct(Stats $manager) {
        parent::__construct("ranks", Core::getInstance());
        
        $this->manager = $manager;
        
        $this->setPermission("core.stats.command.ranks");
        $this->setDescription("Check all the Ranks");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        } else {
            $sender->sendMessage(Core::PREFIX . "Ranks:");
            
            foreach($this->manager->getRanks() as $rank) {
                if($rank instanceof Rank) {
                    $sender->sendMessage(TextFormat::GRAY . "- " . $rank->getName() . ": " . TextFormat::YELLOW . $rank->getValue());
                }
            }
            return true;
        }
    }
}
