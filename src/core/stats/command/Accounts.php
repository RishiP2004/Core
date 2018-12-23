<?php
/**
 *    ___________________________
 *   /  _____/\______   \_   ___ \  ___________   ____
 *  /   \  ___ |     ___/    \  \/ /  _ \_  __ \_/ __ \
 *  \    \_\  \|    |   \     \___(  <_> )  | \/\  ___/
 *   \______  /|____|    \______  /\____/|__|    \___  >
 *          \/                  \/                   \/
 */
namespace GPCore\Stats\Commands;

use GPCore\GPCore;

use GPCore\Network\Objects\GPServer;

use GPCore\Stats\Objects\GPUser;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\utils\TextFormat;

class AccountsCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("accounts", $GPCore);

        $this->GPCore = $GPCore;

        $this->setAliases(["accs"]);
        $this->setPermission("GPCore.Stats.Command.Accounts");
        $this->setDescription("Get all registered Accounts");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        } else {
			$sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Total Accounts Registered (x" . count($this->GPCore->getStats()->getUsers()) . ")");
		
			$users = [];
		
			foreach($this->GPCore->getStats()->getUsers() as $user) {
			    if($user instanceof GPUser) {
                    $users[] = $user->getUsername();
                }
			}
			$sender->sendMessage(TextFormat::GRAY . implode(", ", $users));
			return true;
		}
    }
}