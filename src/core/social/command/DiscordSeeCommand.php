<?php

namespace hcf\command\types;

use hcf\command\task\GetDiscordUsernameTask;
use hcf\command\utils\Command;
use hcf\database\Database;
use hcf\HCFPlayer;
use hcf\translation\Translation;
use pocketmine\command\CommandSender;
use pocketmine\Server;

class DiscordSeeCommand extends Command {

    /** @var array<int, int> */
    public static $running = [];

    /**
     * ListCommand constructor.
     */
    public function __construct() {
        parent::__construct("discord", "See a player's discord account.", "/discord <username>");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (!$sender instanceof HCFPlayer || !$sender->hasPermission("permission.staff")) {
            $sender->sendMessage(Translation::getMessage("noPermission"));
            return;
        }
        if (isset(self::$running[$sender->getRawUniqueId()])) {
            $sender->sendMessage(Translation::getMessage("commandCooldown"));
            return;
        }
        if (!isset($args[0])) {
            $sender->sendMessage(Translation::getMessage("usageMessage", [
                "usage" => $this->getUsage()
            ]));
            return;
        }

        self::$running[$sender->getRawUniqueId()] = true;
        if ($p = Server::getInstance()->getPlayer($args[0])) {
            $args[0] = $p->getName();
        }
        Database::queryAsync("SELECT username, discordUid FROM discord LEFT JOIN players p ON p.uuid=discord.uuid WHERE username=?", "s", [$args[0]],
            static function (array $rows) use ($sender): void {
                if (!$sender->isOnline()) {
                    unset(DiscordCommand::$running[$sender->getRawUniqueId()]);
                    return;
                }
                if (!isset($rows[0]["username"])) {
                    $sender->sendMessage(Translation::getMessage("invalidPlayer"));
                    unset(DiscordCommand::$running[$sender->getRawUniqueId()]);
                    return;
                }
                $username = $rows[0]["username"];
                if (!isset($rows[0]["discordUid"])) {
                    $sender->sendMessage(Translation::getMessage("noLinkedDiscord", ["name" => $username]));
                    unset(DiscordCommand::$running[$sender->getRawUniqueId()]);
                    return;
                }
                $senderUsername = $sender->getName();
                $uuid = $sender->getRawUniqueId();
                Server::getInstance()->getAsyncPool()->submitTask(new GetDiscordUsernameTask($rows[0]["discordUid"], $username, $senderUsername, $uuid));
            });
    }
}
