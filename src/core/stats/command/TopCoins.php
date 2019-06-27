<?php

declare(strict_types = 1);

namespace core\stats\command;

use core\Core;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class TopCoins extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("topcoins", $core);

        $this->core = $core;

        $this->setPermission("core.stats.command.topcoins");
        $this->setUsage("[page]");
        $this->setDescription("Check the Top Coins");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        } else {
            $page = $args[0] ?? 1;
            $banned = [];

            foreach($this->core->getServer()->getNameBans()->getEntries() as $entry) {
                if($this->core->getStats()->getCoreUser($entry->getName())) {
                    $banned[] = $entry->getName();
                }
            }
            $ops = [];

            foreach($this->core->getServer()->getOps()->getAll() as $op) {
                if($this->core->getStats()->getCoreUser((string) $op)) {
                    $ops[] = $op;
                }
            }
            $this->core->getStats()->sendTopEconomy("coins", $sender, $page, $ops, $banned);
            return true;
        }
    }
}