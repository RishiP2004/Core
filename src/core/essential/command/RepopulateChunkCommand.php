<?php

declare(strict_types = 1);

namespace core\essential\command;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use pocketmine\command\CommandSender;

class RepopulateChunkCommand extends BaseCommand {
	public function prepare() : void {
		$this->setPermission("repopulatechunk.command");
		$this->addConstraint(new InGameRequiredConstraint($this));
	}

    public function onRun(CommandSender $sender, string $commandLabel, array $args): void {
        $chunk = $sender->getWorld()->getOrLoadChunkAtPosition($sender->getPosition());

		if($chunk === null) {
            return;
        }
        $chunk->setPopulated(false);
        $sender->getWorld()->orderChunkPopulation($chunk->getX(), $chunk->getZ(), true);
	}
}