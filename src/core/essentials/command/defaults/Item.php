<?php

declare(strict_types = 1);

namespace core\essentials\command\defaults;

use core\Core;
use core\CorePlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\item\ItemFactory;

use pocketmine\nbt\JsonNBTParser;

use pocketmine\nbt\tag\CompoundTag;

class Item extends PluginCommand {
    private $core;

    public function __construct(Core $core) {
        parent::__construct("item", $core);

        $this->core = $core;

		$this->setAliases(["give"]);
        $this->setPermission("core.essentials.defaults.command.item");
        $this->setUsage("<item [:damage]> [amount] [tags] [player]");
        $this->setDescription("Add an Item to yours or another Player's Inventory");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /item " . $this->getUsage());
            return false;
        }
		$item = ItemFactory::fromString($args[0]);

		if(!isset($args[1])) {
			$item->setCount($item->getMaxStackSize());
		} else {
			$item->setCount($args[1]);
		}
		if(isset($args[2])) {
			$tags = $exception = \null;
			$data = \implode(" ", \array_slice($args, 2));
			
			try {
				$tags = JsonNBTParser::parseJSON($data);
			} catch(\Throwable $throwable) {
				$exception = $throwable;
			}
			if(!$tags instanceof CompoundTag or $exception !== null) {
				$sender->sendMessage($this->core->getErrorPrefix() . "Invalid Tag");
				return true;
			}
			$item->setNamedTag($tags);
		}
		if(!$item->getId() === 0) {
			$sender->sendMessage($this->core->getErrorPrefix() . $item . " is not a valid Item");
            return false;
		}
        if(isset($args[3])) {
			if(!$sender->hasPermission($this->getPermission() . ".Other")) {
				$sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
				return false;
			}
            $player = $this->core->getServer()->getPlayer($args[3]);

            if(!$player instanceof CorePlayer) {
                $sender->sendMessage($this->core->getErrorPrefix() . $args[3] . " is not Online");
                return false;
            } else {
				$player->getInventory()->addItem(clone $item);
				$player->sendMessage($this->core->getPrefix() . $sender->getName() . " Gave you the Item: " . $item->getName() . ", Id: " . $item->getId() . " Damage: " . $item->getDamage() . " and Count: " . $item->getCount());
				$sender->sendMessage($this->core->getPrefix() . "Gave the Item: " . $item->getName() . ", Id: " . $item->getId() . " Damage: " . $item->getDamage() . " and Count: " . $item->getCount() . " to " . $player->getName());
                return true;
            }
        }
        if(!$sender instanceof CorePlayer) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You must be a Player to use this Command");
            return false;
        } else {
			$sender->getInventory()->setItemInHand(clone $item);
			$sender->sendMessage($this->core->getPrefix() . "Gave yourself the Item: " . $item->getName() . ", Id: " . $item->getId() . " Damage: " . $item->getDamage() . " and Count: " . $item->getCount());
            return true;
        }
    }
}