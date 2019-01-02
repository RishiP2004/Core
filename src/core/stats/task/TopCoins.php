<?php

namespace core\stats\task;

use core\Core;
use core\CorePlayer;

use core\stats\Statistics;

use pocketmine\command\ConsoleCommandSender;

use pocketmine\scheduler\AsyncTask;

use pocketmine\utils\TextFormat;

class TopCoins extends AsyncTask implements Statistics {
    private $sender;

    private $allCoins = [], $ops = [], $banned = [];

    private $page = 1;

    private $max = 0;

    private $topList;

    public function __construct(string $sender, array $allCoins, int $page, array $ops, array $banned) {
        $this->sender = $sender;
        $this->allCoins = $allCoins;
        $this->page = $page;
        $this->ops = $ops;
        $this->banned = $banned;
    }

    public function onRun() : void {
        $this->topList = serialize($this->getTopList());
    }

    private function getTopList() {
        $allCoins = $this->allCoins;
        $banned = $this->banned;
        $ops = $this->ops;

        arsort($allCoins);

        $array = [];
        $place = 1;

        $this->max = ceil((count($allCoins) - count($banned) - (count($ops))) / Statistics::TOP_SHOWN_PER_PAGE["Coins"]);
        $this->page = min($this->max, max(1, $this->page));

        foreach($allCoins as $player => $coins) {
            $player = strtolower($player);

            if(isset($banned[$player])) {
                continue;
            }
            if(isset($this->ops[$player])) {
                continue;
            }
			$current = ceil($place / self::TOP_SHOWN_PER_PAGE["Coins"]);
			
            if($current === $this->page) {
                $array[$place] = [$player, $coins];
            } else if($current > $this->page) {
                break;
            }
            ++$place;
        }
        return $array;
    }

    public function onCompletion() : void {
		$core = Core::getInstance();
        $output = $core->getPrefix() . "Top Coins (" . $this->page . "/" . $this->max . "):";

        foreach(unserialize($this->topList) as $place => $list) {
            $output .= TextFormat::GRAY . $place . ". " .  $list[0] . " - " . $core->getStats()->getEconomyUnit("Coins") . $list[1];
        }
        $output = substr($output, 0, -1);

        if($this->sender instanceof ConsoleCommandSender) {
            $this->sender->sendMessage($output);
        } else if($core->getServer()->getPlayer($this->sender) instanceof CorePlayer) {
            $this->sender->sendMessage($output);
        }
    }
}