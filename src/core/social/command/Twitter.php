<?php

declare(strict_types = 1);

namespace core\social\command;

use core\Core;

use core\utils\SubCommand;

use core\social\command\subCommand\{
    DirectMessage,
    Follow,
    Help,
    Tweet
};

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

class Twitter extends PluginCommand {
    private $core;

    private $subCommands = [], $commandObjects = [];

    public function __construct(Core $core) {
        parent::__construct("twitter", $core);

        $this->core = $core;

        $this->setPermission("core.social.twitter");
        $this->setDescription("twitter Command");
        $this->loadSubCommand(new DirectMessage($core));
        $this->loadSubCommand(new Follow($core));
        $this->loadSubCommand(new Help($core));
        $this->loadSubCommand(new Tweet($core));
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
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /twitter help");
            return false;
        }
        $subCommand = array_shift($args);

        if(!isset($this->subCommands[$subCommand])) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /twitter help");
            return false;
        }
        $command = $this->commandObjects[$this->subCommands[$subCommand]];

        if(!$command->canUse($sender)) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
        } else {
            if(!$command->execute($sender, $args)) {
                $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /twitter " . $command->getName() . " " . $command->getUsage());
                return false;
            } else {
                return true;
            }
        }
        return false;
    }
}