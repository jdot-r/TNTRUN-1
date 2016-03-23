<?php

namespace game\tntrun;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\level\Explosion;
use pocketmine\event\block\BlockEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityMoveEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\math\Vector3 as Vector3;
use pocketmine\math\Vector2 as Vector2;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\network\protocol\AddMobPacket;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\UpdateBlockPacket;
use pocketmine\block\Block;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\protocol\DataPacket;
use pocketmine\network\protocol\Info;
use pocketmine\network\protocol\LoginPacket;
use game\util\PacketMonitor;
use pocketmine\entity\FallingBlock;
use game\api\MGDatastoreConfig;
use game\api\MGArena;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerInteractEvent;

/**
 * TntRun PlugIn - MCPE Mini-Game
 *
 * Copyright (C) MCPE_PluginDev
 *
 * @author DavidJBrockway aka MCPE_PluginDev
 *        
 */
class TNTRun extends PluginBase implements Listener {
 	// variables	
 	public $config; 	
 	public $arenaBuilder; 	
 	public $tntRunCommand;
 	public $mgdatastoreconfig;
 	
 	public $mineSweeperSessions = [];
 	public $mineSweeperScores = []; 
 	public $fallingblocks= [];	 	

 	public $arenaPlayers = [];
 	public $livePlayers = [];
 	public $tntQuickSessions = [];
 	
 	//display flag
 	public $pos_display_flag = 0; 	
 	
 	public $setblock_flag = 0;	 	
 	public $game_mode = 0;
 	/**
 	 * OnLoad 
 	 * (non-PHPdoc)
 	 * @see \pocketmine\plugin\PluginBase::onLoad()
 	 */
	public function onLoad() {
		$this->arenaBuilder = new TNTArenaBuilder($this);		
		$this->tntRunCommand = new TNTRunCommand($this);
	}
	
	
	/**
	 * OnEnable
	 * 
	 * (non-PHPdoc)
	 * @see \pocketmine\plugin\PluginBase::onEnable()
	 */
	public function onEnable() {				
		if (! file_exists ( $this->getDataFolder () . "config.yml" )) {
			@mkdir ( $this->getDataFolder () );
			file_put_contents ( $this->getDataFolder () . "config.yml", $this->getResource ( "config.yml" ) );
		}
		// read restriction
		// $this->config = yaml_parse(file_get_contents($this->getDataFolder() . "config.yml"));
		$this->getConfig ()->getAll ();
		
		$this->log( TextFormat::GREEN . "-------------------------------------------------" );
		$this->log( TextFormat::GREEN . "- MCPE_PluginDev_MCPE_TNTRun- Enabled!" );
		$this->log( TextFormat::GREEN . "-------------------------------------------------" );		
		$this->log( TextFormat::BLUE . "- lobby world: ".$this->getConfig ()->get ( "tntrun_lobby_world"));
		$this->log( TextFormat::BLUE . "- lobby location at x:".$this->getConfig ()->get ( "tntrun_lobby_x" ). " y:".$this->getConfig ()->get ( "tntrun_lobby_y" ). "z:".$this->getConfig ()->get ( "tntrun_lobby_z" ));
		$this->log( TextFormat::BLUE . "- arena world: ".$this->getConfig ()->get ( "tntrun_arena_world"));
		$this->log( TextFormat::BLUE . "- arena location at x:".$this->getConfig ()->get ( "tntrun_arena_x" ). " y:".$this->getConfig ()->get ( "tntrun_arena_y" ). "z:".$this->getConfig ()->get ( "tntrun_arena_z" ));		
		$this->log( TextFormat::GREEN . "-------------------------------------------------" );
						
		$this->enabled = true;
		$this->getServer ()->getPluginManager ()->registerEvents ( $this, $this );					
		//schedule reset task
		$runScheduleTask = $this->getConfig ()->get ( "reset_scheduler" );
		if ($runScheduleTask!=null && $runScheduleTask=="on") {
			$this->log( TextFormat::GREEN . "- Reset Scheduler Enabled!" );		
			$resetValue = $this->getConfig ()->get ( "reset_timeout" );
			if ($resetValue==null) {
				$resetValue = 8000;
			}
			$resetTask = new PlayResetTimeout ( $this);
			$this->getServer ()->getScheduler ()->scheduleRepeatingTask( $resetTask, $resetValue );
			$this->log( TextFormat::GREEN . "-MCPE_PluginDev_MCPE_TNTRun - round reset scheduler will run in every ".$resetValue. " ticks." );
			$this->log( TextFormat::GREEN . "-------------------------------------------------" );
		}
	}
	
	/**
	 * OnDisable
	 * (non-PHPdoc)
	 * @see \pocketmine\plugin\PluginBase::onDisable()
	 */
	public function onDisable() {
		$this->log( TextFormat::RED . "MCPE_PluginDev_TNTRun - Disabled" );
		$this->enabled = false;
	}

	/**
	 * OnCommand
	 * (non-PHPdoc)
	 * @see \pocketmine\plugin\PluginBase::onCommand()
	 */
public function onCommand(CommandSender $sender, Command $command, $label, array $args) {		
		$this->tntRunCommand->onCommand($sender, $command, $label, $args);		
	}

	/**
	 * OnBlockBreak
	 * 
	 * @param BlockBreakEvent $event
	 */
	public function onBlockBreak(BlockBreakEvent $event) {	
		$b = $event->getBlock();
		if ($this->pos_display_flag==1) {
			$event->getPlayer()->sendMessage("block BREAKED: [x=".$b->x." y=".$b->y." z=".$b->z."]");
		}
	}

	
	public function onBlockPlace(BlockPlaceEvent $event) {
		$b = $event->getBlock ();
		if ($this->pos_display_flag==1) {
			$event->getPlayer()->sendMessage("block PLACED: ".$b);
			$event->getPlayer()->sendMessage("block PLACED: [x=".$b->x." y=".$b->y." z=".$b->z."]");
			//return;
		}
	}
	
	public function onPlayerInteract (PlayerInteractEvent $event) {
		$b = $event->getBlock();
		if ($this->pos_display_flag==1) {
			$event->getPlayer()->sendMessage("player TOUCHED: [x=".$b->x." y=".$b->y." z=".$b->z."]");
			//return;
		}		
		$this->tntRunCommand->onPlayerInteract($event);	
	}
	
	/**
	 * OnPlayerJoin
	 * 
	 * @param PlayerJoinEvent $event
	 */	
	public function onPlayerJoin(PlayerJoinEvent $event) {		
		$this->tntRunCommand->onPlayerJoin($event);
	}

	/**
	 * Handle Player Move Event
	 *
	 * @param EntityMoveEvent $event
	 */
	public function onPlayerMove(PlayerMoveEvent $event) {
		$this->tntRunCommand->onPlayerMove($event);		
	}
	
	public function onEntityDamage ( EntityDamageEvent $event) {
		$this->tntRunCommand->onEntityDamage($event);		
	}
		
	public function onEntityMotion ( EntityMotionEvent $event) {
		//$this->log("onEntityMotion: Name: ".$event->getEventName());
		//$this->log("onEntityMotion: Cause:".$event->getCause());
		//$this->log("onEntityMotion: Entity:".$event->getEntity()->getName());
	}
		
	/**
	 * OnQuit
	 *
	 * @param PlayerQuitEvent $event
	 */
	public function onQuit(PlayerQuitEvent $event) {
		//$this->tntRunCommand->onQuit($event);		
		//remove player record from database
	}
		
	/**
	 * Logging util function
	 * 
	 * @param unknown $msg
	 */
	private function log($msg){
		$this->getLogger ()->info ($msg);
	}
		
}
