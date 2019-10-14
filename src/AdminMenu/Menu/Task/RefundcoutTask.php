<?php

namespace AdminMenu\Menu\Task;

use pocketmine/Player;
use AdminMenu\Menu\Main;
use pocketmine\scheduler\Task;

class RefundcoutTask extends Task{
    public $seconds = 10*20;

    public function __construct(Main $plugin, Player $player)
    {
        $this->plugin = $plugin;
        $this->player = $player;
    }

    public function onRun($tick): void{
        $this->player->sendPopup($this->plugin->tag . " §c•§a Đợi §e".$this->seconds."§a Để tiếp tục dùng lệnh!");
        if($this->seconds === 0){
            $name = $this->player->getName();
            if(is_array($name, $this->plugin->tasks)){
                unset($this->plugin->tasks[array_search($name, $this->plugin->tasks)]);
                $this->plugin->tasks[$this->player->getId()]->getHandler()->cancel();
                $this->player->sendPopup("§c• §aBạn đã có thể dùng lại lệnh!");
            }
        }
        $this->seconds--;
    }
}