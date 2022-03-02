<?php

namespace xig1517;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use xig1517\commands;
use xig1517\functions;

class main extends PluginBase
{

    private $initConfig = array(
        'restart-mode'=>0,

        'time'=>1.5,

        'kick-message'=>'Server restarting...',

        'reality-countdown'=>[
            'broadcast'=>'true',
            'popup-message'=>'Server will be restart in {%h} hr {%m} mins {%s} sec...',
            'tip-message'=>'null'
        ],

        'message-setting'=>[
            'broadcast'=>'true',
            'custom-chat'=>'Server will be restart in {%h} hr {%m} mins {%s} sec.',
            'custom-popup'=>'null',
            'custom-tip'=>'null',
        ],

        'notification-setting'=>[
            'not-time'=>[ //sec
                '1800',
                '900',
                '600',
                '60',
                '10',
                '5',
                '4',
                '3',
                '2',
                '1'
            ],
        ],
    );

    public $currentTick;

    public $conf;

    public $f;

    public $restartTime;

    public function onEnable () : void 
    {
        $this->conf = new Config($this->getDataFolder() ."config.yml", Config::YAML, $this->initConfig);

        $this->getScheduler()->scheduleRepeatingTask(new ticking($this), 1);

        $this->currentTick = 0;

        $this->f = new functions($this);

        $this->restartTime = $this->conf->get('time')*60*60;

        $this->getLogger()->info("AutoRestart loaded.");
    }

    public function onCommand (CommandSender $sender, Command $command, string $label, array $args) : bool
    {
        $name = $sender->getName();
        switch ($command->getName()) {
            case 'xar':
                if (isset($args[0])) {
                    switch ($args[0]) {

                        case 'time':
                            if (($msg = $this->conf->get('message-setting')['custom-chat']) == 'null') 
                                $msg = "Server will be restart in {%h} hr {%m} mins {%s} sec.";

                            $msg = $this->f->transStr($msg);
                            if ($sender instanceof Player) {
                                $sender->sendmessage($msg);
                            }
                            else {
                                $this->getLogger()->Info($msg);
                            }
                            break;

                        case 'set':
                        case 's':
                            if (!isset($args[1]) OR !isset($args[2])) {
                                $sender->sendMessage('Usage: /xar set <value> <unit(h,m,s)>');
                            }
                            else {
                                $time = $args[1];
                                $unit = $args[2];
                                $transed = $this->f->transUnit($time, $unit);
                                $this->restartTime = $transed + ($this->currentTick/20);
                                $d = $this->f->calTime($transed);
                                $sender->sendmessage('The restartTime have been set '. $d[0] .':'. $d[1] .':'. $d[2]);
                            }
                            break;

                        case 'add':
                        case 'a':
                            if (!isset($args[1]) OR !isset($args[2])) {
                                $sender->sendMessage('Usage: /xar add <value> <unit(h,m,s)>');
                            }
                            else {  
                                $time = $args[1];
                                if ($time < 0) {
                                    $sender->sendmessage('please use: /xar reduce <value> <unit(h,m,s)>');
                                    break;
                                }
                                $unit = $args[2];
                                $transed = $this->f->transUnit($time, $unit);
                                $this->restartTime += $transed;
                                $d = $this->f->calTime($this->restartTime - ($this->currentTick/20));
                                $sender->sendmessage('Add '. $time .''. $unit .' to restartTime, new restartTime is '. $d[0] .':'. $d[1] .':'. $d[2]);
                            }
                            break;

                            case 'reduce':
                            case 'r':
                                if (!isset($args[1]) OR !isset($args[2])) {
                                    $sender->sendMessage('Usage: /xar add <value> <unit(h,m,s)>');
                                }
                                else {  
                                    $time = $args[1];
                                    if ($time < 0) {
                                        $sender->sendmessage('the value must > 0');
                                        break;
                                    }
                                    $unit = $args[2];
                                    $transed = $this->f->transUnit($time, $unit);
                                    $this->restartTime -= $transed;
                                    $d = $this->f->calTime($this->restartTime - ($this->currentTick/20));
                                    $sender->sendmessage('Reduce '. $time .''. $unit .' from restartTime, new restartTime is '. $d[0] .':'. $d[1] .':'. $d[2]);
                                }
                                break;

                        case 'help':
                        default:
                            $sender->sendMessage('XAutoRestart HELP');
                            $sender->sendMessage('/xar time                  ----- 顯示下次重啟時間');
                            $sender->sendMessage('/xar set <value> <unit>    ----- 設定下次重啟時間');
                            $sender->sendMessage('/xar add <value> <unit>    ----- 新增下次重啟時間');
                            $sender->sendMessage('/xar reduce <value> <unit> ----- 減少下次重啟時間');
                            $sender->sendMessage('/xar help                  ----- 獲取幫助');
                            break;
                    }
                }
                else {
                    $sender->sendmessage('Usage: /xar help');
                }
            break;
        }
        return false;
    }
}
?>