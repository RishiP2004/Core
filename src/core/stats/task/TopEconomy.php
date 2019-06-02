<?php

declare(strict_types = 1);

namespace core\stats\task;

use core\Core;
use core\CorePlayer;

use core\stats\Statistics;

use pocketmine\Server;

use pocketmine\command\ConsoleCommandSender;

use pocketmine\scheduler\AsyncTask;

use pocketmine\utils\TextFormat;

class TopEconomy extends AsyncTask implements Statistics {
	private $sender;

	private $unit = "";

	private $allEconomy = [], $ops = [], $banned = [];

	private $page = 1;

	private $max = 0;

	private $topList;

	public function __construct(string $sender, string $unit, array $allEconomy, int $page, array $ops, array $banned) {
		$this->sender = $sender;
		$this->unit = $unit;
		$this->allEconomy = $allEconomy;
		$this->page = $page;
		$this->ops = $ops;
		$this->banned = $banned;
	}

	public function onRun() : void {
		$this->topList = serialize($this->getTopList());
	}

	private function getTopList() {
		$allEconomy = $this->allEconomy;
		$banned = $this->banned;
		$ops = $this->ops;

		arsort($allEconomy);

		$array = [];
		$place = 1;

		$this->max = ceil((count($allEconomy) - count($banned) - (count($ops))) / Statistics::TOP_SHOWN_PER_PAGE[$this->unit]);
		$this->page = min($this->max, max(1, $this->page));

		foreach($this->allEconomy as $player => $coins) {
			$player = strtolower($player);

			if(isset($banned[$player])) {
				continue;
			}
			if(isset($this->ops[$player])) {
				continue;
			}
			$current = ceil($place / self::TOP_SHOWN_PER_PAGE[$this->unit]);

			if($current === $this->page) {
				$array[$place] = [$player, $coins];
			} else if($current > $this->page) {
				break;
			}
			++$place;
		}
		return $array;
	}

	public function onCompletion(Server $server) : void {
		$core = Core::getInstance();
		$unit = strtoupper($this->unit);
		$output = $core->getPrefix() . "Top " . $unit . " (" . $this->page . "/" . $this->max . "):";

		foreach(unserialize($this->topList) as $place => $list) {
			$output .= TextFormat::GRAY . $place . ". " .  $list[0] . " - " . $core->getStats()->getEconomyUnit($this->unit) . $list[1];
		}
		$output = substr($output, 0, -1);

		if($this->sender instanceof ConsoleCommandSender) {
			$this->sender->sendMessage($output);
		} else if($core->getServer()->getPlayer($this->sender) instanceof CorePlayer) {
			$this->sender->sendMessage($output);
		}
	}
}