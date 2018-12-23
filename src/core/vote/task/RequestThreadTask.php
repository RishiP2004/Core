<?php

namespace core\vote\task;

use core\Core;
use core\CorePlayer;

use core\utils\Website;

use core\vote\ServerListQuery;

use pocketmine\scheduler\AsyncTask;

class RequestThreadTask extends AsyncTask {
    private $name = "";

    protected $queries, $rewards, $error;

    public function __construct(string $name, $queries) {
        $this->name = $name;
        $this->queries = $queries;
    }

    public function onRun() : void {
        foreach($this->queries as $query) {
            if($query instanceof ServerListQuery) { 
                if($return = Website::getURL(str_replace("{USERNAME}", urlencode($this->name), $query->getCheckURL())) !== false && is_array($return = json_decode($return, true)) && isset($return["Voted"]) && is_bool($return["Voted"]) && isset($return["Claimed"]) && is_bool($return["Claimed"])) {
                    $query->setVoted($return["Voted"] ? 1 : -1);
                    $query->setClaimed($return["Claimed"] ? 1 : -1);

                    if($query->hasVoted() && !$query->hasClaimed()) {
                        if($return = Website::getUrl(str_replace("{USERNAME}", urlencode($this->name), $query->getClaimURL())) != false && is_array(($return = json_decode($return, true))) && isset($return["Voted"]) && is_bool($return["Voted"]) && isset($return["Claimed"]) && is_bool($return["Claimed"])) {
                            $query->setVoted($return["Voted"] ? 1 : -1);
                            $query->setClaimed($return["Claimed"] ? 1 : -1);

                            if($query->hasVoted() && $query->hasClaimed()) {
                                $this->rewards++;
                            }
                        } else {
                            $this->error = "Error sending claim data for \"" . $this->name . "\" to \"" . str_replace("{USERNAME}", urlencode($this->name), $query->getClaimURL()) . "\". Invalid VRC file or bad Internet connection";

                            $query->setVoted(-1);
                            $query->setClaimed(-1);
                        }
                    }
                } else {
                    $this->error = "Error fetching vote data for \"" . $this->name . "\" from \"" . str_replace("{USERNAME}", urlencode($this->name), $query->getCheckURL()) . "\". Invalid VRC file or bad Internet connection";

                    $query->setVoted(-1);
                    $query->setClaimed(-1);
                }
            }
        }
    }

    public function onCompletion() : void {
		$core = Core::getInstance();
		
        if(isset($this->error)) {
            $core->getServer()->getLogger()->info($core->getErrorPrefix() . $this->error);
        }
        $player = $core->getServer()->getPlayer($this->name);

        if($player instanceof CorePlayer) {
            $player->giveVoteRewards($this->rewards);
            array_splice($core->getVote()->queue, array_search($this->name, $core->getVote()->queue, true), 1);
        }
    }
}