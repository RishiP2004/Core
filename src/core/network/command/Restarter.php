<?php

declare(strict_types = 1);

namespace core\network\command;

use core\Core;

use core\network\Network;

use core\utils\SubCommand;

use core\network\command\subCommand\{
    Add,
    Help,
    Memory,
    Set,
    Start,
    Stop,
    Subtract,
    Time
};

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class Restarter extends PluginCommand {
    private $manager;

    private $subCommands = [], $commandObjects = [];

    public function __construct(Network $manager) {
        parent::__construct("restarter", Core::getInstance());

        $this->manager = $manager;

        $this->setAliases(["restart", "serverrestart"]);
        $this->setPermission("core.network.command");
        $this->setDescription("Restart Command");
        $this->loadSubCommand(new Add($manager));
        $this->loadSubCommand(new Help($manager));
        $this->loadSubCommand(new Memory($manager));
        $this->loadSubCommand(new Set($manager));
        $this->loadSubCommand(new Start($manager));
        $this->loadSubCommand(new Stop($manager));
        $this->loadSubCommand(new Subtract($manager));
        $this->loadSubCommand(new Time($manager));
    }

    private function loadSubCommand(SubCommand $subCommand) {
        $this->commandObjects[] = $subCommand;
        $commandId = count($this->commandObjects) - 1;
        $this->subCommands[$subCommand->getName()] = $commandId;

        foreach($subCommand->getAliases() as $alias) {
            $this->subCommands[$alias] = $commandId;
        }
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        }
        if(!isset($args[0])) {
            $sender->sendMessage(Core::ERROR_PREFIX . "Usage: /restarter help");
            return false;
        }
        $subCommand = array_shift($args);

        if(!isset($this->subCommands[$subCommand])) {
            $sender->sendMessage(Core::ERROR_PREFIX . "Usage: /restarter help");
            return false;
        }
        $command = $this->commandObjects[$this->subCommands[$subCommand]];

        if(!$command->canUse($sender)) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
        } else {
            if(!$command->execute($sender, $args)) {
                $sender->sendMessage(Core::ERROR_PREFIX . "Usage: /restarter" . " " . $command->getName() . " " . $command->getUsage());
                return false;
            } else {
                return true;
            }
        }
        return false;
    }
}