<?php
/**
 *    ___________________________
 *   /  _____/\______   \_   ___ \  ___________   ____
 *  /   \  ___ |     ___/    \  \/ /  _ \_  __ \_/ __ \
 *  \    \_\  \|    |   \     \___(  <_> )  | \/\  ___/
 *   \______  /|____|    \______  /\____/|__|    \___  >
 *          \/                  \/                   \/
 */
namespace GPCore\Stats\Commands;

use GPCore\GPCore;

use GPCore\Stats\Objects\Rank;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\utils\TextFormat;

class RanksCommand extends PluginCommand {
    private $GPCore;
    
    public function __construct(GPCore $GPCore) {
        parent::__construct("ranks", $GPCore);
        
        $this->GPCore = $GPCore;
        
        $this->setPermission("GPCore.Stats.Command.Ranks");
        $this->setDescription("Check all the Ranks");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        } else {
            $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Ranks:");
            
            foreach($this->GPCore->getStats()->getAllRanks() as $rank) {
                if($rank instanceof Rank) {
                    $sender->sendMessage(TextFormat::GRAY . "- " . $rank->getName() . ": " . TextFormat::YELLOW . $rank->getValue());
                }
            }
            return true;
        }
    }
}
