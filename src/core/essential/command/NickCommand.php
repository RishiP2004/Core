<?php

declare(strict_types = 1);

namespace hcf\command\types;

use hcf\command\utils\Command;
use hcf\translation\Translation;
use hcf\translation\TranslationException;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class NickCommand extends Command {

    /**
     * NickCommand constructor.
     */
    public function __construct() {
        parent::__construct("nick", "Set your username to whatever you want");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     *
     * @throws TranslationException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (!$sender->hasPermission("nick.permission")) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        $name = implode(" ", $args);
        if($name == "reset" or $name == "off"){
            $sender->setDisplayName($sender->getName());
            $sender->sendMessage(TextFormat::BOLD . TextFormat::GRAY . "[" . TextFormat::GOLD . TextFormat::BOLD . "!" . TextFormat::GRAY . "] - " . TextFormat::RESET . TextFormat::GOLD . "Your nickname has been" . TextFormat::YELLOW . " disabled" . TextFormat::GOLD . "!");
            return;
		}
		    $sender->setDisplayName($name);
        $sender->setDisplayName($name);
        $sender->sendMessage(TextFormat::BOLD . TextFormat::GRAY . "[" . TextFormat::GOLD . TextFormat::BOLD . "!" . TextFormat::GRAY . "] - " . TextFormat::RESET . TextFormat::GOLD . "Your username has been set to " . TextFormat::YELLOW . $name . "!");
        return;
    }
}
