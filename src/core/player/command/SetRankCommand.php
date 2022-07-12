<?php

declare(strict_types = 1);

namespace core\player\command;

use core\Core;

use core\player\CorePlayer;
use core\player\rank\{
	Rank,
	DefaultRank,
	CompoundRank,
	RankIds
};
use core\player\PlayerManager;
use core\player\traits\PlayerCallTrait;
use core\player\command\args\{
	OfflinePlayerArgument,
	RankArgument
};


use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\args\RawStringArgument;

use pocketmine\Server;

use pocketmine\command\CommandSender;

use pocketmine\utils\TextFormat;

class SetRankCommand extends BaseCommand {
	use PlayerCallTrait;

	public function prepare() : void {
		$this->setPermission("setrank.command");
		$this->registerArgument(0, new OfflinePlayerArgument("player"));
		$this->registerArgument(1, new RankArgument("rank"));
		$this->registerArgument(2, new RawStringArgument("type", true));
	}

    public function onRun(CommandSender $sender, string $commandLabel, array $args) : void {
		$this->getCoreUser($args["player"]->getName(), function($user) use ($sender, $args) {
			if(is_null($user)) {
				$sender->sendMessage(Core::ERROR_PREFIX . $args["player"] . " is not a valid Player");
				return false;
			}
			if($args["rank"] === "0") { //to remove a rank
				if (!isset($args["type"]) or !in_array($args["type"], ["donator", "staff"])) {
					$sender->sendMessage(Core::ERROR_PREFIX . "Usage: /setrank <player>  0 [donator|staff]");
					return false;
				}
				$currentRankId = $user->getRank();
				$currentRank = PlayerManager::getInstance()->getRank($currentRankId);

				if(!$currentRank instanceof CompoundRank) {
					$sender->sendMessage(Core::ERROR_PREFIX . "Not a compound rank");
					return false;
				}
				if ($args["type"] === "donator" or $args["type"] === "d") {
					if (!$currentRank->getDonatorRank() === null) {
						$sender->sendMessage(Core::ERROR_PREFIX . "Has no donator rank");
						return false;
					}
					$newRankId = $currentRank->getIdentifier() & CompoundRank::STAFF_RANK_MASK;
					$removedGroupId = $currentRankId - $newRankId;
				} else {
					if (!$currentRank->getStaffRank() === null) {
						$sender->sendMessage(Core::ERROR_PREFIX . "Has no staff rank");
						return false;
					}
					$newRankId = $currentRank->getIdentifier() & CompoundRank::DONATOR_RANK_MASK;
					$removedGroupId = $currentRankId - $newRankId;
				}
				$user->setRank(PlayerManager::getInstance()->getRank($newRankId));

                $player = Server::getInstance()->getPlayerExact($user->getName());

				if($player instanceof CorePlayer) {
					$player->setNameTag($player->getCoreUser()->getRank()->getNameTagFormat());
				}
				$sender->sendMessage(Core::PREFIX . "Removed rank to " . PlayerManager::getInstance()->getRank($removedGroupId)->getColoredName());
				return true;
			} else {
				if (!$args["rank"] instanceof Rank && !$args["rank"] instanceof DefaultRank) {
					$sender->sendMessage(Core::ERROR_PREFIX . "Invalid rank");
					$sender->sendMessage(Core::PREFIX . "Ranks:");
					
					foreach(PlayerManager::getInstance()->getRanksFlat() as $rank) {
						if($rank instanceof Rank) {
							$sender->sendMessage(TextFormat::GRAY . "- " . $rank->getName() . ": " . TextFormat::YELLOW . $rank->getValue());
						}
					}
					return true;
				}
				$currentRankId = $user->getRank()->getIdentifier();

                if($args["rank"] instanceof DefaultRank) {
					$newRankId = 0;
				} else {
                    // get rid of current donator rank (rightmost 8 bits)
                    $currentStaffPart = ($currentRankId >> 8) << 8;
                  
					if ($args["rank"]->getValue() === RankIds::DONATOR_RANK) {
						$newRankId = $currentStaffPart + $args["rank"]->getIdentifier();
					} else {
						$newRankId = $currentRankId - $currentStaffPart + ($args["rank"]->getIdentifier() << 8);
					}
				}
				$user->setRank(PlayerManager::getInstance()->getRank($newRankId));
				$player = Server::getInstance()->getPlayerExact($user->getName());

				if($player instanceof CorePlayer) {
					$player->setNameTag($player->getCoreUser()->getRank()->getNameTagFormatFor($player));
				}
				$sender->sendMessage(Core::PREFIX . "Set " . $user->getName() . "rank to " . PlayerManager::getInstance()->getRank($newRankId)->getFormat());
                return true;
            }
		});
    }
}