<?php

declare(strict_types = 1);

namespace core\stats\command;

use core\Core;

use core\stats\{
	Stats,
	Statistics
};

use pocketmine\command\{
    PluginCommand,
    CommandSender
};
use pocketmine\utils\TextFormat;

class TopCoins extends PluginCommand {
    private $manager;

    public function __construct(Stats $manager) {
        parent::__construct("topcoins", Core::getInstance());

        $this->manager = $manager;

        $this->setPermission("core.stats.command.topcoins");
        $this->setUsage("[page]");
        $this->setDescription("Check the Top Coins");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage(Core::ERROR_PREFIX . "You do not have Permission to use this Command");
            return false;
        } else {
			$page = $args[0] ?? 1;
			$top = $this->manager->getTopCoins(5, $page);

			if(empty($top)) {
				$sender->sendMessage(Core::ERROR_PREFIX . "No Accounts registered");
				return false;
			}
			$message = Core::PREFIX . "Top Coins (Page: " . $page . ")";

			for($i = 0; $i < count($top); ++$i) {
				$message .= TextFormat::EOL . TextFormat::GOLD . "$i + 1" . TextFormat::GRAY . array_keys($top)[$i] . ": " . TextFormat::GREEN . Statistics::COIN_UNIT . " " . array_values($top)[$i];
			}
			$sender->sendMessage($message);
			return true;
        }
    }
}