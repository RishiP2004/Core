<?php
/**
 *    ___________________________
 *   /  _____/\______   \_   ___ \  ___________   ____
 *  /   \  ___ |     ___/    \  \/ /  _ \_  __ \_/ __ \
 *  \    \_\  \|    |   \     \___(  <_> )  | \/\  ___/
 *   \______  /|____|    \______  /\____/|__|    \___  >
 *          \/                  \/                   \/
 */
namespace GPCore\Essentials\Defaults\Commands;

use GPCore\GPCore;

use GPCore\Stats\Objects\GPPlayer;

use pocketmine\command\{
    PluginCommand,
    CommandSender
};

use pocketmine\timings\TimingsHandler;

use pocketmine\scheduler\BulkCurlTask;

use pocketmine\Server;

class TimingsCommand extends PluginCommand {
    private $GPCore;

    public function __construct(GPCore $GPCore) {
        parent::__construct("timings", $GPCore);

        $this->GPCore = $GPCore;

        $this->setPermission("GPCore.Essentials.Defaults.Command.Reload");
        $this->setUsage("<reload : on : off : paste>");
        $this->setDescription("Timings Command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if(!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "You do not have Permission to use this Command");
            return false;
        }
        if(count($args) < 1) {
            $sender->sendMessage($this->GPCore->getBroadcast()->getErrorPrefix() . "Usage: /timings" . " " . $this->getUsage());
            return false;
        } else {
            switch(strtolower($args[0])) {
                case "reload":
                    TimingsHandler::reload();
                    $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Timings Reloaded");
                break;
                case "on":
                    TimingsHandler::setEnabled(true);
                    $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Timings Enabled");
                break;
                case "off":
                    TimingsHandler::setEnabled(false);
                    $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Timings Disabled");
                break;
                case "paste":
                    $sampleTime = \microtime(\true) - $this->GPCore->getEssentials()->getDefaults()->timingStart;
                    $index = 0;
                    $timingFolder = $sender->getServer()->getDataPath() . "timings/";

                    if(!file_exists($timingFolder)) {
                        mkdir($timingFolder, 0777);
                    }
                    $timings = $timingFolder . "timings.txt";

                    while(file_exists($timings)) {
                        $timings = $timingFolder . "timings" . (++$index) . ".txt";
                    }
                    $fileTimings = $args[0] ? \fopen("php://temp", "r+b") : \fopen($timings, "a+b");

                    TimingsHandler::printTimings($fileTimings);
                    fwrite($fileTimings, "Sample time " . \round($sampleTime * 1000000000) . " (" . $sampleTime . "s)" . \PHP_EOL);

                    if($args[0]) {
                        fseek($fileTimings, 0);

                        $data = [
                            "syntax" => "text",
                            "poster" => $sender->getServer()->getName(),
                            "content" => \stream_get_contents($fileTimings)
                        ];

                        fclose($fileTimings);

                        $sender->getServer()->getAsyncPool()->submitTask(new class([
                            [
                                "page" => "http://paste.ubuntu.com",
                                "extraOpts" => [
                                    CURLOPT_HTTPHEADER => ["User-Agent: " . $sender->getServer()->getName() . " " . $sender->getServer()->getPocketMineVersion()],
                                    CURLOPT_POST => 1,
                                    CURLOPT_POSTFIELDS => $data
                                ]
                            ]
                        ], $sender) extends BulkCurlTask {
                            public function onCompletion(Server $server) {
                                $sender = $this->fetchLocal();

                                if($sender instanceof GPPlayer and !$sender->isOnline()){
                                    return;
                                }
                                $result = $this->getResult()[0];

                                if($result instanceof \RuntimeException) {
                                    $server->getLogger()->logException($result);
                                    return;
                                }
                                list(, $headers) = $result;

                                foreach($headers as $headerGroup) {
                                    if(isset($headerGroup["location"]) and \preg_match('#^http://paste\\.ubuntu\\.com/([A-Za-z0-9+\/=]+)/#', \trim($headerGroup["location"]), $match)) {
                                        $pasteId = $match[1];
                                        break;
                                    }
                                }
                                $Broadcast = GPCore::getInstance()->getBroadcast();

                                if(!isset($pasteId)) {
                                    $sender->sendMessage($Broadcast->getPrefix() . "Timings Error");
                                } else {
                                    $sender->sendMessage($Broadcast->getPrefix() . "Timings Uploaded to " . "http://paste.ubuntu.com/" . $pasteId . "/");
                                    $sender->sendMessage($Broadcast->getPrefix() . "Timings Read: " . "http://" . $sender->getServer()->getProperty("timings.host", "timings.pmmp.io") . "/?url=" . \urlencode($pasteId));
                                }
                            }
                        });
                    } else {
                        fclose($fileTimings);
                        $sender->sendMessage($this->GPCore->getBroadcast()->getPrefix() . "Timings Written to " . $timings);
                    }
                break;
            }
            return true;
        }
    }
}
