<?php

namespace xig1517;

use pocketmine\scheduler\Task;
use xig1517\main;
use xig1517\functions;

class ticking extends Task 
{

    private $f;

    private $plugin;

    public function __construct(main $plugin)
    {
        $this->plugin = $plugin;
        $this->f = new functions($plugin);
    }

    public function onRun() : void
    {
        $ctsec = $this->plugin->currentTick / 20;
        $restartTime = $this->plugin->restartTime;
    
        if ($ctsec < $restartTime) {
            $remainTime = $restartTime - $ctsec;
            $notAll = $this->plugin->conf->get('notification-setting')['not-time'];
            foreach ($notAll as $na) {
                if ($remainTime == $na) {
                    $this->f->sendNotification();
                    break;
                }
            }

            $rcAll = $this->plugin->conf->get('reality-countdown');
            if ($rcAll['broadcast'] == 'true') {
                if (($msg = $rcAll['popup-message']) != 'null') {
                    $this->f->broadcast(1, $this->f->transStr($msg));
                }
                if (($msg = $rcAll['tip-message']) != 'null') {
                    $this->f->broadcast(2, $this->f->transStr($msg));
                }
            }
        }
        else if ($ctsec == $restartTime) {
            $this->f->broadcast(0, 'Restarting...');
            $this->f->stopServer();
        }
        else if ($ctsec > $restartTime) {
            $this->plugin->currentTick = ($restartTime - 15)*20;
            $this->f->broadcast(0, 'Server will be restart in 15 sec.');
        }
        $this->plugin->currentTick ++;
    }

}

?>