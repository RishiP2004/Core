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
        $this->setUsage("<player> <item [:damage]> [amount] [tags]");
        $this->setDescription("Add an Item to yours or another Player's Inventory");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->core->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 2) {
            $sender->sendMessage($this->core->getErrorPrefix() . "Usage: /item " . $this->getUsage());
            return false;
        }
		$player = $sender->getServer()->getPlayer($args[0]);

		if(!$player instanceof CorePlayer) {
			$sender->sendMessage($this->core->getErrorPrefix() . $args[0] . " is not a valid Player");
			return true;
		}
		try {
			$item = ItemFactory::fromString($args[1]);
		} catch(\InvalidArgumentException $exception) {
			$sender->sendMessage($this->core->getErrorPrefix() . $args[1] . " is not a valid Item");
			return true;
		}
		if(!isset($args[2])) {
			$item->setCount($item->getMaxStackSize());
		} else {
			$item->setCount($args[2]);
		}
		if(isset($args[3])) {
			$tags = $exception = \null;
			$data = \implode(" ", \array_slice($args, 3));
			
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
		if(!$player->getInventory()->canAddItem($item)) {
			$player->sendMessage($this->core->getPrefix() . $sender->getName() . " Gave you the Item: " . $item->getName() . ", Id: " . $item->getId() . " Damage: " . $item->getDamage() . " and Count: " . $item->getCount());
			$sender->sendMessage($this->core->getPrefix() . "Gave the Item: " . $item->getName() . ", Id: " . $item->getId() . " Damage: " . $item->getDamage() . " and Count: " . $item->getCount() . " to " . $player->getName());
			$player->getInventory()->getLevel()->dropItem($item, $player);
			return true;
		}
		$player->getInventory()->addItem(clone $item);
		$player->sendMessage($this->core->getPrefix() . $sender->getName() . " Gave you the Item: " . $item->getName() . ", Id: " . $item->getId() . " Damage: " . $item->getDamage() . " and Count: " . $item->getCount());
		$sender->sendMessage($this->core->getPrefix() . "Gave the Item: " . $item->getName() . ", Id: " . $item->getId() . " Damage: " . $item->getDamage() . " and Count: " . $item->getCount() . " to " . $player->getName());
		return true;
    }
}