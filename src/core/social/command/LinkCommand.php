<?php

namespace core\social\command;

use core\Core;

use core\player\CorePlayer;

use core\social\SocialManager;

use CortexPE\Commando\BaseCommand;

use pocketmine\command\CommandSender;

class LinkCommand extends BaseCommand {
    /** @var array<int, int> */
    private static $running = [];

    public function prepare() : void {
		// TODO: Implement prepare() method.
	}

	public function canUse(CommandSender $sender) {
		return $sender->hasPermission("link.command") and $sender instanceof CorePlayer;
	}

    public function onRun(CommandSender $sender, string $commandLabel, array $args): void {
        if(!$this->canUse($sender)) {
			$sender->sendMessage(Core::ERROR_PREFIX . "You do not have permission to use this command.");
			return;
        }
        if(isset(self::$running[$sender->getRawUniqueId()])) {
			$sender->sendMessage(Core::ERROR_PREFIX . "Currently linking your account.");
            return;
        }
        self::$running[$sender->getRawUniqueId()] = true;

        SocialManager::getInstance()->getDiscordId($sender->getRawUniqueId(), function($discordUUID, $code) use ($sender) {
			if(!$sender->isOnline()) {
				unset(LinkCommand::$running[$sender->getRawUniqueId()]);
				return;
			}
			if(!is_null($discordUUID)) {
				$sender->sendMessage(Core::ERROR_PREFIX . "Discord account is already linked.");
				unset(LinkCommand::$running[$sender->getRawUniqueId()]);
				return;
			}
			if(!is_null($code)) {
				$sender->sendMessage(Core::ERROR_PREFIX . "Discord link code: " . $code);
				unset(LinkCommand::$running[$sender->getRawUniqueId()]);
				return;
			}
			LinkCommand::generate($sender);
		});
    }

    public static function generate(HCFPlayer $sender): void {
        $code = strtoupper(substr(uniqid('', true), 0, 8));
        Database::queryAsync("SELECT 1 FROM discord WHERE code=?", "s", [$code], static function (array $rows) use ($sender, $code): void {
            if (!$sender->isOnline()) {
                unset(LinkCommand::$running[$sender->getRawUniqueId()]);
                return;
            }
            if (!empty($rows)) {
                LinkCommand::generate($sender);
                return;
            }
            Database::queryAsync("INSERT INTO discord (uuid, code) VALUES (?, ?)", "ss", [$sender->getRawUniqueId(), $code], static function (array $rows) use ($sender, $code): void {
                unset(LinkCommand::$running[$sender->getRawUniqueId()]);
                if (!$sender->isOnline()) {
                    return;
                }
                $sender->sendMessage(Translation::getMessage("linkCode", ["code" => $code]));
            });
        });
    }
}
