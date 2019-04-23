<?php

declare(strict_types = 1);

namespace core\network\command;

use core\Core;

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
    private $core;

    private $subCommands = [], $commandObjects = [];

    public function __construct(Core $core) {
        parent::__construct("restarter", $core);

        $this->core = $core;

        $this->setAliases(["restart", "serverrestart"]);
        $this->setPermission("core.network.command");
        $this->setDescription("Restart Command");
        $this->loadSubCommand(new Add($core));
        $this->loadSubCommand(new Help($core));
        $this->loadSubCommand(new Memory($core));
        $this->loadSubCommand(new Set($core));
        $this->loadSubCommand(new Start($core));
        $this->loadSubCommand(new Stop($core));
        $this->loadSubCommand(new Subtract($core));
        $this->loadSubCommand(new Time($core));
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
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(!isset($args[0])) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /restarter help");
            return false;
        }
        $subCommand = array_shift($args);

        if(!isset($this->subCommands[$subCommand])) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /restarter help");
            return false;
        }
        $command = $this->commandObjects[$this->subCommands[$subCommand]];

        if(!$command->canUse($sender)) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
        } else {
            if(!$command->execute($sender, $args)) {
                $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /restarter" . " " . $command->getName() . " " . $command->getUsage());
                return false;
            } else {
                return true;
            }
        }
        return false;
    }
}