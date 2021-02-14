<?php

declare(strict_types = 1);

namespace core\essentials\permission;

use core\Core;
use core\CoreUser;

use core\essentials\Essentials;

use core\stats\Stats;

use DateTime;
use pocketmine\permission\BanEntry;

class BanList extends \pocketmine\permission\BanList {
    private $type = "";

    public $list = [];

    public function __construct(string $type) {
        $this->type = $type;
    }

    public function load() {
        Core::getInstance()->getDatabase()->executeSelect("sentences.get", [], function(array $rows) {
            foreach($rows as [
				"xuid" => $xuid,
				"username" => $name,
				"reason" => $reason,
				"expires" => $expires,
				"sentencer" => $source
            ]) {
				$entry = new \core\essentials\permission\BanEntry($name);

				$datetime = DateTime::createFromFormat("m-d-Y H:i", $expires);

				$entry->setReason($reason !== null ? $reason : $entry->getReason());
				$entry->setExpires($datetime);
				$entry->setSource($source !== null ? $source : $entry->getSource());

				$this->list[$entry->getName()] = $entry;

				$this->removeExpired();
            }
        });
    }
    /**
     * @return CoreUser[]
     */
    public function getSentences() : array {
        return $this->list;
    }

	public function getEntries() : array{
		return $this->getSentences();
	}

    public function isBanned(string $name) : bool {
        $this->removeExpired();

        return isset($this->list[strtolower($name)]);
    }

    public function addBan(string $target, string $reason = null, DateTime $expires = null, string $source = null) : BanEntry {
    	$entry = new \core\essentials\permission\BanEntry($target);

        $entry->setReason($reason !== null ? $reason : $entry->getReason());
        $entry->setExpires($expires);
        $entry->setSource($source !== null ? $source : $entry->getSource());
		
		$this->list[$entry->getName()] = $entry;

		$this->removeExpired();
		
        Stats::getInstance()->getCoreUser($target, function($user) use($source, $reason, $expires) {
			Core::getInstance()->getDatabase()->executeInsert("sentences.register", [
				"xuid" => $user->getXuid(),
				"registerDate" => date("m:d:y h:A"),
				"listType" => $this->type,
				"type" => Essentials::BAN,
				"username" => $user->getName(),
				"sentencer" => $source,
				"reason" => $reason,
				"expires" => $expires->format('m-d-Y H:i')
			]);
		});
        return $entry;
    }

    public function remove(string $name) {
       	Stats::getInstance()->getCoreUser($name, function($user) use ($name) {
			Core::getInstance()->getDatabase()->executeChange("sentences.delete", [
				"xuid" => $user->getXuid(),
				"listType" => $this->type,
				"type" => Essentials::BAN
			]);

			$name = strtolower($name);

			if(isset($this->list[$name])) {
				unset($this->list[$name]);
			}
		});
    }

    public function removeExpired() {
        foreach($this->list as $name => $entry) {
            if($entry->hasExpired()) {
                $this->remove($name);
            }
        }
    }
}