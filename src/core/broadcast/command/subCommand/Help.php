<?php

declare(strict_types = 1);

namespace core\broadcast\command\subCommand;

use core\Core;

use core\utils\SubCommand;

use core\broadcast\Broadcast;

use pocketmine\command\CommandSender;

use pocketmine\utils\TextFormat;

class Help extends SubCommand {
	private $manager;

	public function __construct(Broadcast $manager) {
		$this->manager = $manager;
	}

	public function canUse(CommandSender $sender) : bool {
        return $sender->hasPermission("core.broadcast.subcommand.help");
    }

    public function getUsage() : string {
        return "";
    }

    public function getName() : string {
        return "help";
    }

    public function getDescription() : string {
        return "Help about Broadcast";
    }

    public function getAliases() : array {
        return [];
    }

    public function execute(CommandSender $sender, array $args) : bool {
        $sender->sendMessage(Core::PREFIX . "Broadcast Help:");
        $sender->sendMessage(TextFormat::GRAY . "/broadcast help");
        $sender->sendMessage(TextFormat::GRAY . "/broadcast sendmessage <message>");
        $sender->sendMessage(TextFormat::GRAY . "/broadcast sendpopup <popup>");
        $sender->sendMessage(TextFormat::GRAY . "/broadcast sendtitle <title>");
        return true;
    }
}