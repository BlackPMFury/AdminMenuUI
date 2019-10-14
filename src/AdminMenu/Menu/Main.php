<?php

namespace AdminMenu\Menu;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\{Player, Server};
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\player\{PlayerJoinEvent, PlayerChatEvent};
use pocketmine\network\mcpe\protocol\LoginPacket;
use AdminMenu\Menu\Task\RefundcountTask;
use Jojoe7777\FormAPI;

class Main extends PluginBase implements Listener{
    public $config = [];
    public $tag = "§c•§aAdminMenu§c•";

    public $task;
    public $tasks = [];

    public function onEnable(){
        $this->getServer()->getLogger()->info("§aEnable Plugin...");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->banned = new Config($this->getDataFolder() . "Banned.yml", Config::YAML, []);
        $this->kick = new Config($this->getDataFolder() . "Kick.yml", Config::YAML, []);
    }

    public function onLoad(): void{
        $this->getServer()->getLogger()->info("
//§a               _           _       __  __                  
//§c      /\      | |         (_)     |  \/  |                 
//§b     /  \   __| |_ __ ___  _ _ __ | \  / | ___ _ __  _   _ 
//§d    / /\ \ / _` | '_ ` _ \| | '_ \| |\/| |/ _ \ '_ \| | | |
//§e   / ____ \ (_| | | | | | | | | | | |  | |  __/ | | | |_| |
//§6  /_/    \_\__,_|_| |_| |_|_|_| |_|_|  |_|\___|_| |_|\__,_|
//                   §aCode By §cBlackPMFury
//");
    }

    public function onDeceiver(DataPacketReceiveEvent $ev): void{
        $p = $ev->getPlayer();
        $bc = $ev->getPacket();
        if ($bc instanceof LoginPacket) {
            if ($bc->clientId === 0) {
                $ev->setCancelled(true);
                $p->kick("§cYou used ToolBox!");
            }
        }
    }

    public function onJoin(PlayerJoinEvent $ev){
        $p = $ev->getPlayer();
        $n = $p->getName();
        $online = count($this->getServer()->getOnlinePlayers());
        $this->getServer()->broadcastMessage("§e ".$n." §aVừa Gia nhập cộng đồng!");
        foreach($this->getServer()->getOnlinePlayers() as $pl){
            $p->sendPopup("§c•§a Tổng Số Online ngay bây giờ: ". $online);
        }
    }

    public function createTask($sender){
        $name = $sender->getName();
        $task = new RefundcountTask($this, $sender);
        $this->getScheduler()->scheduleRepeatingTask($task, 10*20);
        $this->tasks[$sender->getId()] = $task;
        $this->tasks[] = $name;
    }

    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
        switch($cmd->getName()){
            case "adminui":
            case "AdminUI":
                if(!($sender instanceof Player)){
                    $this->getLogger()->alert("please use in game!");
                    return true;
                }
                $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
                $form = $api->createSimpleForm(Function (Player $sender, $data){
                    $ketqua = $data;
                    if ($ketqua == null){
                    }
                    switch($ketqua){
                        case 0:
                            $this->onKick($sender);
                            break;
                        case 1:
                            $this->onBanned($sender);
                            break;
                        case 2:
                            $this->reason($sender);
                            break;
                        case 3:
                            $sender->sendMessage($this->tag . " §aHay lắm đĩ mẹ mày :))");
                            break;
                    }
                });
                $form->setTitle($this->tag);
                $form->setContent("§l§c• §aAdminMenu Version 1.0");
                $form->addButton("§c-==• §aKick §c•==-", 0);
                $form->addButton("§b-==§c•§a Ban §c•§b==-", 1);
                $form->addButton("§b-==§c•§a Lý Do bị kick §c•§b==-", 2);
                $form->addButton("§a Thoát", 3);
                $form->sendToPlayer($sender);
        }
        return true;
    }

    public function onKick($sender){
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createCustomForm(Function (Player $sender, $data){
            $this->getServer()->dispatchCommand(new ConsolseCommandSender(), "kick ".$args[1]." ". $args[2]);
            $this->kick->set($sender->getName(), ["Name" => $args[1], "Reason" => $args[2]]);
            $this->kick->save();
            $this->createTask($sender);
            $this->getServer()->broadcastMessage("§cAdmCmd: ".$args[1]." Bị kick bởi ".$sender->getName()." Reason: ". $args[2]);
        });
        $form->setTitle($this->tag);
        $form->addLabel("§c>> §a AdmCmd: Hãy Dùng nếu có múc đích Đúng!");
        $form->addInput("§c•§aTên§c•");
        $form->addInput("§c•§dReason§c•");
        $form->sendToPlayer($sender);
    }

    public function onBanned($sender){
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createCustomForm(Function (Player $sender, $data){
            $this->getServer()->dispatchCommand(new ConsolseCommandSender(), "ban ".$args[1]." ". $args[2]);
            $this->banned->set($sender->getName(), ["Name" => $args[1], "Reason" => $args[2]]);
            $this->banned->save();
            $this->createTask($sender);
            $this->getServer()->broadcastMessage("§cAdmCmd: ".$args[1]." Bị Banned bởi ".$sender->getName()." Reason: ". $args[2]);
        });
        $form->setTitle($this->tag);
        $form->addLabel("§c>> §a AdmCmd: Hãy Dùng nếu có múc đích Đúng!");
        $form->addInput("§c•§aTên§c•");
        $form->addInput("§c•§dReason§c•");
        $form->sendToPlayer($sender);
    }

    public function reason($sender){
        $cfg = $this->kick->get($sender->getName());
        $name = $cfg["Name"];
        $reason = $cfg["Reason"];
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createCustomForm(Function (Player $sender, $data){
        });
        $form->setTitle("§b-==§c•§a Lý Do bị kick §c•§b==-");
        $form->addLabel("§c• §aLý Do banh bị kick");
        $form->addLabel("§c• §aName:§e ". $name);
        $form->addLabel("§c• §aReason:§e ". $reason);
        $form->sendToPlayer($sender);
    }
}