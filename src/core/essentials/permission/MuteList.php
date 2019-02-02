<?php

namespace core\essentials\permission;

use pocketmine\permission\{
    BanList,
    BanEntry
};

class MuteList extends BanList {
    public function add(BanEntry $entry) {
        if($entry instanceof BlockEntry) {
            throw new \InvalidArgumentException();
        }
        parent::add($entry);
    }

    public function addBan(string $target, string $reason = null, \DateTime $expires = null, string $source = null) : BanEntry {
        $entry = new MuteEntry($target);

        $entry->setReason($reason ?? $entry->getReason());
        $entry->setExpires($expires);
        $entry->setSource($source ?? $entry->getSource());
        parent::addBan($entry->getName(), $entry->getReason(), $entry->getExpires(), $entry->getSource());;
        return $entry;
    }
}