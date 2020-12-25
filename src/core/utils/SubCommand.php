<?php

declare(strict_types = 1);

namespace core\utils;

use pocketmine\command\CommandSender;

abstract class SubCommand {
	public abstract function canUse(CommandSender $sender) : bool;

    public abstract function getUsage() : string;

    public abstract function getName() : string;

    public abstract function getDescription() : string;

    public abstract function getAliases() : array;

    public abstract function execute(CommandSender $sender, array $args) : bool;
}