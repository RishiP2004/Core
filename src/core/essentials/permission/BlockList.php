<?php
/**
 *    ___________________________
 *   /  _____/\______   \_   ___ \  ___________   ____
 *  /   \  ___ |     ___/    \  \/ /  _ \_  __ \_/ __ \
 *  \    \_\  \|    |   \     \___(  <_> )  | \/\  ___/
 *   \______  /|____|    \______  /\____/|__|    \___  >
 *          \/                  \/                   \/
 */
namespace GPCore\Essentials\Defaults\Permissions;

use pocketmine\permission\{
    BanList,
    BanEntry
};

class BlockList extends BanList {
    public function add(BanEntry $entry) {
        if($entry instanceof BlockEntry) {
            throw new \InvalidArgumentException();
        }
        parent::add($entry);
    }

    public function addBan(string $target, string $reason = null, \DateTime $expires = null, string $source = null) : BanEntry {
        $entry = new BlockEntry($target);

        $entry->setReason($reason ?? $entry->getReason());
        $entry->setExpires($expires);
        $entry->setSource($source ?? $entry->getSource());
        parent::addBan($entry->getName(), $entry->getReason(), $entry->getExpires(), $entry->getSource());;
        return $entry;
    }
}
