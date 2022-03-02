<?php

namespace xig1517;

use xig1517\main;
use xig1517\Player;

class functions
{

    private $plugin;

    public function __construct (main $plugin)
    {
        $this->plugin = $plugin;
    }

    public function calTime ($sec)
    {
        $h = 0; $m = 0;
        $sec = floor($sec);
        if ($sec >= 60) {
            $m = floor($sec / 60);
            $sec = $sec - $m*60;
        }
        if ($m >= 60) {
            $h = floor($m / 60);
            $m = $m - $h*60;
        }
        return array($h, $m, $sec);
    }

    public function sendNotification ()
    {
        $ms = $this->plugin->conf->get('message-setting');

        if ($ms['broadcast'] == 'true') {
            if (($msg = $ms['custom-chat']) != 'null') {
                $this->broadcast(0, $this->transStr($msg));
            }
            if (($msg = $ms['custom-popup']) != 'null') {
                $this->broadcast(1, $this->transStr($msg));
            }
            if (($msg = $ms['custom-tip']) != 'null') {
                $this->broadcast(2, $this->transStr($msg));
            }
        }
        return true;
    }
    
    public function transStr ($msg) 
    {
        $ctr = $this->calTime($this->plugin->restartTime - ($this->plugin->currentTick/20));
        $msg = str_replace("{%h}", $ctr[0], $msg);
        $msg = str_replace("{%m}", $ctr[1], $msg);
        $msg = str_replace("{%s}", $ctr[2], $msg);
        return $msg;
    }

    public function transUnit ($value, $unit)
    {
        if ($unit == 'h') $value *= 3600;
        else if ($unit == 'm') $value *= 60;
        return $value;
    }

    public function broadcast ($t, $msg) 
    {
        switch ($t) {
            case 0;
                $this->plugin->getServer()->broadcastMessage($msg);
                break;
            case 1;
                $this->plugin->getServer()->broadcastPopup($msg);
                break;
            case 2;
                $this->plugin->getServer()->broadcastTip($msg);
                break;
        }
        return true;
    }

    public function stopServer ()
    {
        foreach($this->plugin->getServer()->getOnlinePlayers() as $pl){
			$pl->save(true);
            $pl->kick($this->plugin->conf->get('kick-message'));
		}

		foreach($this->plugin->getServer()->getWorldManager()->getWorlds() as $w){
			$w->save(true);
		}
        $this->plugin->getServer()->shutdown();
    }

}