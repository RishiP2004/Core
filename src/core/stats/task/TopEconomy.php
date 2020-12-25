<?php

declare(strict_types = 1);

namespace core\stats\task;

use core\Core;

use core\stats\Statistics;

use pocketmine\Server;

use pocketmine\scheduler\AsyncTask;

use pocketmine\utils\TextFormat;

class TopEconomy extends AsyncTask implements Statistics {
	private $sender;

	private $unit;

	private $allEconomy, $ops, $banned;
	
	private $addOp = true, $addBanned = true;

	private $page, $max = 0;

	private $topList;

	public function __construct(string $sender, string $unit, array $allEconomy, int $page, bool $addOp, array $ops, bool $addBanned, array $banned) {
		$this->sender = $sender;
		$this->unit = $unit;
		$this->allEconomy = $allEconomy;
		$this->page = $page;
		$this->addOp = $addOp;
		$this->ops = $ops;
		$this->addBanned = $addBanned;
		$this->banned = $banned;
	}

	public function onRun() : void {
		$this->topList = serialize((array) $this->getTopList());
	}

	private function getTopList() {
		$allEconomy = (array) $this->allEconomy;
		$banned = (array) $this->banned;
		$ops = (array) $this->ops;

		arsort($allEconomy);

		$array = [];
		$place = 1;

		$this->max = ceil((count($allEconomy) - ($this->addBanned ? 0 : count($banned)) - ($this->addOp ? 0 : count($ops))) / Statistics::TOP_SHOWN_PER_PAGE[$this->unit]);
		$this->page = (int) min($this->max, max(1, $this->page));

		foreach($allEconomy as $player => $coins) {
			$p = strtolower($player);

			if(isset($banned[$p]) and !$this->addBanned) {
				continue;
			}
			if(isset($this->ops[$p]) and !$this->addOp) {
				continue;
			}
			$current = (int) ceil($place / self::TOP_SHOWN_PER_PAGE[$this->unit]);

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
		$unit = ucfirst($this->unit);
		$output = Core::PREFIX . "Top " . $unit . " (" . $this->page . "/" . $this->max . "):\n";

		foreach(unserialize($this->topList) as $place => $list) {
			$output .= TextFormat::GRAY . $place . ". " .  $list[0] . " - " . Statistics::UNITS[$unit] . $list[1];
		}
		$output = substr($output, 0, -1);
		$player = $server->getPlayerExact($this->sender);
		
		if($this->sender === "CONSOLE") {
			Server::getInstance()->getLogger()->info($output);
		} else {
			$player->sendMessage($output);
		}
	}
}