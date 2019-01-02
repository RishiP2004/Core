<?php

namespace core\stats\command;

use core\Core;

use core\stats\rank\Rank;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\utils\TextFormat;

class Ranks extends PluginCommand {
    private $core;
    
    public function __construct(Core $core) {
        parent::__construct("ranks", $core);
        
        $this->core = $core;
        
        $this->setPermission("core.stats.command.ranks");
        $this->setDescription("Check all the Ranks");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        } else {
            $sender->sendMessage($this->core->getPrefix() . "Ranks:");
            
            foreach($this->core->getStats()->getRanks() as $rank) {
                if($rank instanceof Rank) {
                    $sender->sendMessage(TextFormat::GRAY . "- " . $rank->getName() . ": " . TextFormat::YELLOW . $rank->getValue());
                }
            }
            return true;
        }
    }
}
