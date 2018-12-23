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

use GPCore\Stats\Tasks\TopCoinsTask;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class TopCoinsCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("topcoins", $GPCore);

        $this->GPCore = $GPCore;

        $this->setPermission("GPCore.Stats.Command.TopCoins");
        $this->setUsage("[page]");
        $this->setDescription("Check the Top Coins");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        } else {
            $page = $args[0] ?? 1;
            $banned = [];

            foreach($this->GPCore->getServer()->getNameBans()->getEntries() as $entry) {
                if($this->GPCore->getStats()->getGPUser($entry->getName())->hasAccount()) {
                    $banned[] = $entry->getName();
                }
            }
            $ops = [];

            foreach($this->GPCore->getServer()->getOps()->getAll() as $op) {
                if($this->GPCore->getStats()->getGPUser($op)->hasAccount()) {
                    $ops[] = $op;
                }
            }
            $this->GPCore->getStats()->sendTopCoins($sender, $page, $ops, $banned);
            return true;
        }
    }
}