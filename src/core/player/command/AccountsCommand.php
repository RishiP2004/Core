<?php

declare(strict_types = 1);

namespace core\player\command;

use core\Core;

use core\player\PlayerManager;

use CortexPE\Commando\BaseCommand;

use pocketmine\command\CommandSender;

use pocketmine\utils\TextFormat;

class AccountsCommand extends BaseCommand {
    public function prepare() : void {
		$this->setPermission("accounts.command");
	}

    public function onRun(CommandSender $sender, string $commandLabel, array $args) : void {
		PlayerManager::getInstance()->getAllCoreUsers(function($users) use($sender) {
			$sender->sendMessage(Core::PREFIX . "Total Accounts Registered (x" . count($users) . ")");
				
			$allUsers = [];
				
			foreach($users as $user) {
				$allUsers[] = $user->getName();
			}
			$sender->sendMessage(TextFormat::GRAY . implode(", ", $allUsers));
		});
    }
}