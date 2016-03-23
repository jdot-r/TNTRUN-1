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
use game\api\MGArenaFactory;
use pocketmine\command\defaults\TeleportCommand;
use game\api\MGArenaPlayer;
use game\api\MGArena;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\level\format\PocketChunkParser;


/**
 * TntRun PlugIn - MCPE Mini-Game
 *
 * Copyright (C) MCPE_PluginDev
 *
 * @author DavidJBrockway aka MCPE_PluginDev
 *        
 */
class TNTRunCommand {
	private $pgin;
	public $mytaskid;
	public $ROUND_TIMEOUT = 8000;
	public function __construct(TNTRun $pg) {
		$this->pgin = $pg;
	}
	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		// $this->log( TextFormat::RED . "- onCommand :".$command->getName());
		// check command names
		if ((strtolower ( $command->getName () ) == "tntrun") && isset ( $args [0] )) {
			if (! ($sender instanceof Player)) {
				$sender->sendMessage ( TextFormat::RED . "This command can only be used in-game." );
				return true;
			}
			// if (count ( $args ) > 2) {
			// $sender->sendMessage ( TextFormat::RED . "Usage: /tntrun start \n /tntrun start [board size |8..24] \n /tntsp stop" );
			// return true;
			// }
			$player = $sender->getPlayer ();
			// // ensure player is running in creative mode
			// if ($player->getGamemode () != 1) {
			// $sender->sendMessage ( TextFormat::RED . "This mini-game can only be used in Creative Mode!" );
			// return true;
			// }
			$this->log ( $command->getName () . " " . count ( $args ) . " " . $args [0] );
			
			if (strtolower ( $args [0] ) == "blockon") {
				if (! $player->isOp ()) {
					$player->sendMessage ( "* You are not authorized to use this command!*" );
					return;
				}
				$this->pgin->pos_display_flag = 1;
				$sender->sendMessage ( "TnTRun block position display ON" );
				return;
			}
			if (strtolower ( $args [0] ) == "blockoff") {
				if (! $player->isOp ()) {
					$player->sendMessage ( "* You are not authorized to use this command!*" );
					return;
				}
				$this->pgin->pos_display_flag = 0;
				$sender->sendMessage ( "TnTRun block position display OFF" );
				return;
			}
			
			if (strtolower ( $args [0] ) == "setsize") {
				if (! $player->isOp ()) {
					$player->sendMessage ( "* You are not authorized to use this command!*" );
					return;
				}
				$sender->sendMessage ( "TnTRun sets arena size to ".$args [1]);
				$this->pgin->arenaBuilder->boardsize = $args [1];
				return;
			}
			
			if (strtolower ( $args [0] ) == "liveplayers") {				
				$sender->sendMessage ( "TnTRun LIVE players: ". count($this->pgin->livePlayers));
				foreach ($this->pgin->livePlayers as $p) {
					$sender->sendMessage ($p->getName());
				}
				return;
			}
			if (strtolower ( $args [0] ) == "status") {
				$this->getArenaStats($player);
				return;
			}

			if ((strtolower ( $args [0] ) == "reset")) {
				
				$arenaX = $this->pgin->getConfig ()->get ( "tntrun_arena_x" );
				$arenaY = $this->pgin->getConfig ()->get ( "tntrun_arena_y" );
				$arenaZ = $this->pgin->getConfig ()->get ( "tntrun_arena_z" );
				
				$player->sendMessage ( "Reset arena floors" );
				$arenaInfo = $this->pgin->arenaBuilder->resetArenaBuilding ( $player->getLevel (), new Position ( $arenaX, $arenaY, $arenaZ ) );
					
				// reset players
				$this->pgin->livePlayers = [];
				$this->pgin->arenaPlayers = [];
					
				// send the arena owner first
				$player->sendMessage ( "***************************************" );
				$player->sendMessage ( "* TNT Run Reset done. it's ready for play! *" );
				$player->sendMessage ( "***************************************" );
			}
			
			
			// setup arena
			if ((strtolower ( $args [0] ) == "create")) {
				// add arena owner to the list
				if (! $player->isOp ()) {
					$player->sendMessage ( "* You are not authorized to use this command!*" );
					return;
				}
				// $this->log ( "lp: " . $lp->getName () );
				// set player to game lobby
				$arenaName = $this->pgin->getConfig ()->get ( "tntrun_arena_name" );
				$arenaSize = $this->pgin->getConfig ()->get ( "tntrun_arena_size" );
				$arenaX = $this->pgin->getConfig ()->get ( "tntrun_arena_x" );
				$arenaY = $this->pgin->getConfig ()->get ( "tntrun_arena_y" );
				$arenaZ = $this->pgin->getConfig ()->get ( "tntrun_arena_z" );
				
				// build areana
				$arenaInfo = $this->pgin->arenaBuilder->buildArena ( $player->getLevel (), new Position ( $arenaX, $arenaY, $arenaZ ) );
				$ex = $arenaInfo ["entrance_x"];
				$ey = $arenaInfo ["entrance_y"];
				$ez = $arenaInfo ["entrance_z"];
				
				// set session for this player
				$this->pgin->tntQuickSessions ["tntrun_arena"] = $arenaInfo;
				
				// reset players
				$this->pgin->livePlayers = [ ];
				$this->pgin->arenaPlayers = [ ];
				
				// send the arena owner first
				$player->sendMessage ( "***************************************" );
				$player->sendMessage ( "* TnTRun Arena Created. ready to play! *" );
				$player->sendMessage ( "***************************************" );
				
				$this->pgin->game_mode = 0;				
				return;
			}
			
			// super fast quick start
			if (strtolower ( $args [0] ) == "start") {
// 				if (! $player->isOp ()) {
// 					$player->sendMessage ( "* You are not authorized to use this command!*" );
// 					return;
// 				}
				// $this->pgin->getServer()->getLevelByName($name);
				$player->getServer ()->broadcastMessage( "TnTRun Started!" );
				
				foreach ( $this->pgin->arenaPlayers as $p ) {
					$this->pgin->livePlayers [$p->getName ()] = $p;
					$p->sendMessage ( "TnTRun player! Go, Go, Go!!!" );
					//$explosion = new Explosion ( new Position ( $player->x, $player->y + 3, $player->z ), 2 );
					//$explosion->explode ();
				}				

				$this->pgin->game_mode = 1;
				
				return;
			}
			
			if (strtolower ( $args [0] ) == "cleanup") {
				if (! $player->isOp ()) {
					$player->sendMessage ( "* You are not authorized to use this command!*" );
					return;
				}
				$player->sendMessage ( "Clean up TnTRun Arena, please wait!" );
				$this->cleanUpArena ( $player->getLevel () );				
				$player->sendMessage ( "use /tntrun reset to re-build arena." );	

				$this->pgin->game_mode = 0;				
				return;
			}
			
			if (strtolower ( $args [0] ) == "join") {
				
				if (!$this->isArenaAvailable()) {
					$player->sendMessage ( "Sorry, TnTRun game in-play. please wait!" );
					return;
				}
				
				$px = $this->pgin->getConfig ()->get ( "tntrun_arena_enter_x" );
				$py = $this->pgin->getConfig ()->get ( "tntrun_arena_enter_y" );
				$pz = $this->pgin->getConfig ()->get ( "tntrun_arena_enter_z" );
				
				if ($px == null || $py == null || $pz == null) {
					$this->log ( "Configuration Error - missing TnTRun player join button info. please contact Ops/admin." );
				} else {					
					//update game mode
					$this->pgin->game_mode = 2;					
					
					// add player to the list
					$this->pgin->arenaPlayers [$player->getName ()] = $player;
					// $this->log ( "player /join: " . $px . " " . $py . " " . $pz );
					$player->teleport ( new Position ( $px, $py, $pz ) );
					$player->sendMessage ( "----------------------------------------" );
					$player->sendMessage ( "Thanks for joining TnT Run. have fun!" );
					$player->sendMessage ( "when ready, tap the [green] block to [start]" );
					$player->sendMessage ( "or tap the [gold] block to [exit]." );
					$player->sendMessage ( "----------------------------------------" );					
					$player->getServer()->broadcastMessage($player->getName ()." has Join TnT Run!");					
				}
			}
		}
		return true;
	}
	
	/**
	 *
	 * Touched Join Button
	 *
	 * @param PlayerInteractEvent $event        	
	 */
	public function joinGame(PlayerInteractEvent $event) {
		$blockTouched = $event->getBlock ();
		$player = $event->getPlayer ();
		
		// check if hitting join sign
		$lx = $this->pgin->getConfig ()->get ( "tntrun_join_button_x" );
		$ly = $this->pgin->getConfig ()->get ( "tntrun_join_button_y" );
		$lz = $this->pgin->getConfig ()->get ( "tntrun_join_button_z" );
		
		$sx = $this->pgin->getConfig ()->get ( "tntrun_join_sign_x" );
		$sy = $this->pgin->getConfig ()->get ( "tntrun_join_sign_y" );
		$sz = $this->pgin->getConfig ()->get ( "tntrun_join_sign_z" );
		
		// JOIN BUTTON
		if ((round ( $blockTouched->x ) == $lx && round ( $blockTouched->y ) == $ly && round ( $blockTouched->z ) == $lz) || (round ( $blockTouched->x ) == $sx && round ( $blockTouched->y ) == $sy && round ( $blockTouched->z ) == $sz)) {
			$px = $this->pgin->getConfig ()->get ( "tntrun_arena_enter_x" );
			$py = $this->pgin->getConfig ()->get ( "tntrun_arena_enter_y" );
			$pz = $this->pgin->getConfig ()->get ( "tntrun_arena_enter_z" );

			//update game mode
			$this->pgin->game_mode = 2;
			
			if ($px == null || $py == null || $pz == null) {
				$this->log ( "Configuration Error - missing TnTRun player join button info. please contact Ops/admin." );
			} else {
				
				if (!$this->isArenaAvailable()) {
					$player->sendMessage ( "Sorry, TnTRun game in-play. please wait!" );
					return;
				}
								
				// add player to the list
				$this->pgin->arenaPlayers [$player->getName ()] = $player;
				// $this->log ( "player /join: " . $px . " " . $py . " " . $pz );
				$event->getPlayer ()->teleport ( new Position ( $px, $py, $pz ) );
				$event->getPlayer ()->sendMessage ( "----------------------------------------" );
				$event->getPlayer ()->sendMessage ( "Thanks for joining TnTRun. have fun!" );
				$event->getPlayer ()->sendMessage ( "when ready, tap the [green] block to [start]" );
				$event->getPlayer ()->sendMessage ( "or tap the [gold] block to [exit]." );				
				$event->getPlayer ()->sendMessage ( "----------------------------------------" );				
				$event->getPlayer ()->getServer()->broadcastMessage($player->getName ()." has Join TnT Run!");
			}
		}
	}
	
	/*
	 * Touched Join Button @param PlayerInteractEvent $event
	 */
	public function returnLobby(PlayerInteractEvent $event) {
		$blockTouched = $event->getBlock ();
		$player = $event->getPlayer ();

		//	ground floor exist	
		$lx = $this->pgin->getConfig ()->get ( "tntrun_ground_exit_button_x" );
		$ly = $this->pgin->getConfig ()->get ( "tntrun_ground_exit_button_y" );
		$lz = $this->pgin->getConfig ()->get ( "tntrun_ground_exit_button_z" );		
		// top floor exist
		$sx = $this->pgin->getConfig ()->get ( "tntrun_top_exit_button_x" );
		$sy = $this->pgin->getConfig ()->get ( "tntrun_top_exit_button_y" );
		$sz = $this->pgin->getConfig ()->get ( "tntrun_top_exit_button_z" );
		
		// Exit BUTTON
		if ( (round ( $blockTouched->x ) == $lx && round ( $blockTouched->y ) == $ly && round ( $blockTouched->z ) == $lz) || (round ( $blockTouched->x ) == $sx && round ( $blockTouched->y ) == $sy && round ( $blockTouched->z ) == $sz)) {
			$px = $this->pgin->getConfig ()->get ( "tntrun_lobby_x" );
			$py = $this->pgin->getConfig ()->get ( "tntrun_lobby_y" );
			$pz = $this->pgin->getConfig ()->get ( "tntrun_lobby_z" );
			
			if ($px == null || $py == null || $pz == null) {
				$this->log ( "Configuration Error - missing TnTRun lobby button info. please contact Ops/admin." );
			} else {
				// remove players from the list
				unset ( $this->pgin->arenaPlayers [$player->getName ()] );
				unset ( $this->pgin->livePlayers [$player->getName ()] );
				// $this->log ( "player /join: " . $px . " " . $py . " " . $pz );
				$event->getPlayer ()->teleport ( new Position ( $px, $py, $pz ) );
				$event->getPlayer ()->sendMessage ( "thanks for playing TnTRun!" );				
				$event->getPlayer ()->getServer()->broadcastMessage($player->getName ()." has Left TnTRun!");
				
				//if last player left the building, then change game mode
				if ( count($this->pgin->livePlayers) == 0 && $this->pgin->game_mode==2 ) {
					$this->pgin->game_mode=0;
				}				
			}
		}
	}
	
	/**
	 *
	 * Touched Start Button
	 *
	 * @param PlayerInteractEvent $event        	
	 */
	public function startGame(PlayerInteractEvent $event) {
		$blockTouched = $event->getBlock ();
		$player = $event->getPlayer ();
		
		// check if hitting join sign
		$lx = $this->pgin->getConfig ()->get ( "tntrun_start_sign_x" );
		$ly = $this->pgin->getConfig ()->get ( "tntrun_start_sign_y" );
		$lz = $this->pgin->getConfig ()->get ( "tntrun_start_sign_z" );
		// ---//
		$sx = $this->pgin->getConfig ()->get ( "tntrun_start_button_x" );
		$sy = $this->pgin->getConfig ()->get ( "tntrun_start_button_y" );
		$sz = $this->pgin->getConfig ()->get ( "tntrun_start_button_z" );
		// START BUTTON//
		if ((round ( $blockTouched->x ) == $lx && round ( $blockTouched->y ) == $ly && round ( $blockTouched->z ) == $lz) || (round ( $blockTouched->x ) == $sx && round ( $blockTouched->y ) == $sy && round ( $blockTouched->z ) == $sz)) {
			$this->pgin->getServer ()->broadcastMessage ( "TnTRun round Started!" );
			$this->pgin->game_mode = 1;
			foreach ( $this->pgin->arenaPlayers as $p ) {
				$this->pgin->livePlayers [$p->getName ()] = $p;
				$p->sendMessage ( "============================" );
				$p->sendMessage ( "TnTRun Started! Go, Go, Go!!!" );
				$p->sendMessage ( "============================" );
				$p->sendMessage ( "Round players:".count($this->pgin->livePlayers));				
				// $explosion = new Explosion ( new Position ( $p->x, $p->y + 3, $p->z),1);
				// $explosion->explode();
			}
			
// 			$explosion = new Explosion ( new Position ( $player->x, $player->y + 3, $player->z ), 1 );
// 			$explosion->explode ();			
		}
	}
	
	/**
	 *
	 * Touched Reset Button
	 *
	 * @param PlayerInteractEvent $event        	
	 */
	public function resetGame(PlayerInteractEvent $event) {
		$blockTouched = $event->getBlock ();
		$player = $event->getPlayer ();
		
		// check if hitting join sign
		$lx = $this->pgin->getConfig ()->get ( "tntrun_reset_button_x" );
		$ly = $this->pgin->getConfig ()->get ( "tntrun_reset_button_y" );
		$lz = $this->pgin->getConfig ()->get ( "tntrun_reset_button_z" );
		// ---//
		$sx = $this->pgin->getConfig ()->get ( "tntrun_reset_sign_x" );
		$sy = $this->pgin->getConfig ()->get ( "tntrun_reset_sign_y" );
		$sz = $this->pgin->getConfig ()->get ( "tntrun_reset_sign_z" );
		
		// Pressed RESET BUTTON//
		if ((round ( $blockTouched->x ) == $lx && round ( $blockTouched->y ) == $ly && round ( $blockTouched->z ) == $lz) || (round ( $blockTouched->x ) == $sx && round ( $blockTouched->y ) == $sy && round ( $blockTouched->z ) == $sz)) {
			
			if (!$this->isArenaAvailable()) {
				$player->sendMessage ( "Sorry, TnTRun game in-play. please wait!" );
				return;
			}

			$this->pgin->game_mode = 0;
			
			$player->getServer ()->broadcastMessage ( "Resetting TnTRun, please wait..." );
			
			$arenaName = $this->pgin->getConfig ()->get ( "tntrun_arena_name" );
			$arenaSize = $this->pgin->getConfig ()->get ( "tntrun_arena_size" );
			$arenaX = $this->pgin->getConfig ()->get ( "tntrun_arena_x" );
			$arenaY = $this->pgin->getConfig ()->get ( "tntrun_arena_y" );
			$arenaZ = $this->pgin->getConfig ()->get ( "tntrun_arena_z" );
			
			// build areana
			// $arenaInfo = $this->pgin->arenaBuilder->buildArena ( $player, new Position($arenaX,$arenaY,$arenaZ) );
			// $ex = $arenaInfo ["entrance_x"];
			// $ey = $arenaInfo ["entrance_y"];
			// $ez = $arenaInfo ["entrance_z"];
			
			// // set session for this player
			// $this->pgin->tntQuickSessions ["tntrun_arena"] = $arenaInfo;
			$player->sendMessage ( "Reset arena floors" );
			$arenaInfo = $this->pgin->arenaBuilder->resetArenaBuilding ( $player->getLevel (), new Position ( $arenaX, $arenaY, $arenaZ ) );
			
			// reset players
			$this->pgin->livePlayers = [ ];
			$this->pgin->arenaPlayers = [ ];
			
			// send the arena owner first
			$player->getServer()->broadcastMessage  ("*****************************************" );
			$player->getServer()->broadcastMessage ( "* TnTRun Reset done. it's ready to play!*" );
			$player->getServer()->broadcastMessage ( "* players tap [Join] sign to join now.  *" );
			$player->getServer()->broadcastMessage  ("*****************************************" );
			// $player->getServer()->broadcast("Done!");
		}
	}
	
	/**
	 * Handle Player Move Event
	 *
	 * @param EntityMoveEvent $event        	
	 */
	public function onPlayerMove(PlayerMoveEvent $event) {
		
		// if ($event->getEntity () instanceof Player) {
		$player = $event->getPlayer ();
		// if(isset($this->mineSweeperSessions[$player->getName()])){
		$x = round ( $event->getFrom ()->x );
		$y = round ( $event->getFrom ()->y );
		$z = round ( $event->getFrom ()->z );
		
		//========================FROM DIRECTION==================================
		// $this->log("tnt layer = ".$tntblock->getID()." x=".$tntblock->x." y=".($tntblock->y-2)." z=".$tntblock->z);
		
		//make sure players in in the game
		if (isset($this->pgin->livePlayers[$player->getName()])) {
		
		if ((count ( $this->pgin->livePlayers ) > 0)) {
			//$this->log ( "live players=" . count ( $this->pgin->livePlayers ) );		
				
			$topblock = $player->getLevel ()->getBlock ( new Vector3 ( $x, $y - 1, $z ) );
			  //$this->log("top layer = ".$topblock->getID()." x=".$topblock->x." y=".($topblock->y)." z=".$topblock->z);
			$midblock = $player->getLevel ()->getBlock ( new Vector3 ( $x, $y - 2, $z ) );
			  //$this->log("mid layer = ".$midblock->getID()." x=".$midblock->x." y=".($midblock->y-1)." z=".$midblock->z);
			$tntblock = $player->getLevel ()->getBlock ( new Vector3 ( $x, $y - 3, $z ) );			
			  //$this->log("tnt layer = ".$tntblock->getID()." x=".$tntblock->x." y=".($tntblock->y-2)." z=".$tntblock->z);
		  			  
			//there are cases, pocketmine is not returning tnt layer instead as Air
			if ($tntblock->getID () == 46 
					|| ($tntblock->getID () == 0 && $topblock->getID()==44 && $midblock->getID()==12) 
					|| ($topblock->getID()==0 && $midblock->getID()==12 && $tntblock->getID () == 0) 
				) {				
				$this->pgin->arenaBuilder->removeUpdateBlock ( $topblock, $tntblock );
			}
						
			//if top layer is sand, remove it
			if ($topblock->getID()==12 && $tntblock->getID()==0 ) {
				$this->pgin->arenaBuilder->removeUpdateBlock ( $topblock, $midblock );				
			}					
			
		  }
		}

	}
	
	/**
	 * Entity Damage
	 *
	 * @param EntityDamageEvent $event        	
	 * @return boolean
	 */
	public function onEntityDamage(EntityDamageEvent $event) {
		// $this->log("onEntityDamage: Cause:".$event->getCause());
		// $this->log("onEntityDamage: Name: ".$event->getEventName());
		
		// check if falling block is sands
		if ($event->getEntity () instanceof FallingBlock) {
			$fallingblock = $event->getEntity ();
			// $this->log("onEntityDamage: FallingBlock:". $fallingblock);
			
			if ($fallingblock->onGround) {
				// $fallingblock->kill ();
				$fallingblock->scheduleUpdate ();
				// $blockId = $fallingblock->getBlock ();
				// $this->log ( "onEntityDamage: FallingBlock touch ground - then removed it ".$blockId );
				$sandblock = $fallingblock->getLevel ()->getBlock ( new Vector3 ( $fallingblock->x, $fallingblock->y + 1, $fallingblock->z ) );
				
				foreach ( $this->pgin->livePlayers as $livep ) {
// 					if ($livep instanceof MGArenaPlayer) {
// 						//$this->pgin->arenaBuilder->renderBlockByType ( $sandblock, $livep->player, 46 );
// 					} else {
						//$this->pgin->arenaBuilder->renderBlockByType ( $sandblock, $livep, 46 );
						$this->pgin->arenaBuilder->removeBlockForInGamePlayers($sandblock, $livep, 46);
					//}
					// $this->pgin->arenaBuilder->renderBlockByType ( $sandblock, $livep, 0);
				}
				// schedule clean up
				$cleanuptask = new FallingBlockCleanupTask ( $this->pgin, $fallingblock, $sandblock );
				$this->mytaskid = $this->pgin->getServer ()->getScheduler ()->scheduleDelayedTask ( $cleanuptask, 50 );
				
				// $this->pgin->fallingblocks = $fallingblock;
				// $x = $block->x;
				// $y = $block->y;
				// $z = $block->z;
				// $explosion = new Explosion ( new Position ($x, $y, $z), 1 );
				// $explosion->explode ();
			}
			
			if ($event->getEntity () instanceof Player) {
				$player = $event->getEntity ();
				if ($player->onGround) {
					$topblock = $fallingblock->getLevel ()->getBlock ( new Vector3 ( $fallingblock->x, $fallingblock->y - 1, $fallingblock->z ) );
					if ($topblock->getID () == 49) {
						// make explosition
						$explosion = new Explosion ( new Position ( $player->x, $player->y + 2, $player->z ), 1 );
						$explosion->explode ();
						
						// game over, send player back to lobby
						$lx = $this->pgin->getConfig ()->get ( "tntrun_lobby_x" );
						$ly = $this->pgin->getConfig ()->get ( "tntrun_lobby_y" );
						$lz = $this->pgin->getConfig ()->get ( "tntrun_lobby_z" );
						// send player to lobby
						$player->teleport ( new Position ( $lx, $ly, $lz ) );
					}
				}
			}
		}
		
		return true;
	}
	
	/**
	 * OnQuit
	 *
	 * @param PlayerQuitEvent $event        	
	 */
	public function onQuit(PlayerQuitEvent $event) {
		// $player = $event->getPlayer ();
		// $this->cleanUpArena ( $player );
	}
	public function onPlayerInteract(PlayerInteractEvent $event) {
		$this->joinGame ( $event );
		$this->startGame ( $event );
		$this->resetGame ( $event );
		$this->returnLobby ( $event );
		$this->onStatSignClick($event);
	}
	public function cleanUpArena(Level $level) {
		
		$this->pgin->game_mode=0; 
		
		// if (isset ( $this->pgin->tntQuickSessions ["tntrun_arena_x"] )) {
		// $arenaInfo = $this->pgin->tntQuickSessions ["tntrun_arena"];
		// if ($arenaInfo != null) {
		// $xx = $arenaInfo ["tntrun_arena_x"];
		// $yy = $arenaInfo ["tntrun_arena_y"];
		// $zz = $arenaInfo ["tntrun_arena_z"];
		
		// $this->log("CLEAN UP ARENA ".$xx. " ".$yy. " ". $zz);
		// var_dump($arenaInfo);
		$arenaWorld = $this->pgin->getConfig ()->get ( "tntrun_arena_world" );
		$arenaSize = $this->pgin->getConfig ()->get ( "tntrun_arena_size" );
		$arenaX = $this->pgin->getConfig ()->get ( "tntrun_arena_x" );
		$arenaY = $this->pgin->getConfig ()->get ( "tntrun_arena_y" );
		$arenaZ = $this->pgin->getConfig ()->get ( "tntrun_arena_z" );
		
		/**
		 * TODO makesure grab the world TNT belongs
		 */
// 		$tntLevel = $level->getServer ()->getLevelByName ( $arenaWorld );
// 		if ($tntLevel == null) {
// 			$level->getServer ()->broadcastMessage ( "Unable to clean up TnTRun, please contact Ops/admin!" );
// 			return;
// 		}
		$this->pgin->arenaBuilder->removeArena ( $level, $arenaX, $arenaY, $arenaZ );
		
		// $arena = new MGArena ( $this->pgin );
		// $arena->deleteArena ( $player->getName (), $player, $player->getLevel ()->getName () );
		
		// $players = $arena->getPlayers ( $player->getName () );
		// foreach ( $players as $p ) {
		// $pname = $p ["pname"];
		// unset ( $this->pgin->livePlayers [$pname] );
		// }
		
		$this->pgin->tntQuickSessions = [ ];
		$this->pgin->livePlayers = [ ];
		$this->pgin->arenaPlayers = [ ];
		
		$level->getServer ()->broadcastMessage ( "==========================" );
		$level->getServer ()->broadcastMessage ( "TnTRun Arena removed!" );
		$level->getServer ()->broadcastMessage ( "Thanks for playing TnT Run!" );
		$level->getServer ()->broadcastMessage ( "==========================" );
		// }
		// remove player session
		// unset ( $this->pgin->tntQuickSessions [$player->getName ()] );
		// remove live players
		// unset($this->pgin->livePlayers);
		// @TODO - remove players join the arena
		// $this->log ( $player->getName () . " Quit!" );
	}
	// }
	
	/**
	 * OnPlayerJoin
	 *
	 * @param PlayerJoinEvent $event        	
	 */
	public function onPlayerJoin(PlayerJoinEvent $event) {
		$lobbyX = $this->pgin->getConfig ()->get ( "tntrun_lobby_x" );
		$lobbyY = $this->pgin->getConfig ()->get ( "tntrun_lobby_y" );
		$lobbyZ = $this->pgin->getConfig ()->get ( "tntrun_lobby_z" );
		$player = $event->getPlayer ();
		$enableSpawnLobby = $this->pgin->getConfig ()->get ( "enable_spaw_lobby" );
		if ($enableSpawnLobby != null && $enableSpawnLobby == "yes") {
			$player->teleport ( new Position ( $lobbyX, $lobbyY, $lobbyZ ) );
			$this->log ( TextFormat::RED . "player spawn to lobby  " . $event->getPlayer ()->getName () . " at " . $lobbyX . " " . $lobbyY . " " . $lobbyZ );
		}
	}
	
	
	public function onStatSignClick(PlayerInteractEvent $event) {
		$lx = $this->pgin->getConfig()->get ( "tntrun_status_sign_x" );
		$ly = $this->pgin->getConfig()->get ( "tntrun_status_sign_y" );
		$lz = $this->pgin->getConfig()->get ( "tntrun_status_sign_z" );

		$blockTouched = $event->getBlock ();
		$player = $event->getPlayer();
		if ((round ( $blockTouched->x ) == $lx && round ( $blockTouched->y ) == $ly && round ( $blockTouched->z ) == $lz)) {
			//$player->teleport ( new Position ( $lobbyX, $lobbyY, $lobbyZ ) );
			//$this->log ( TextFormat::RED . "player spawn to lobby  " . $event->getPlayer ()->getName () . " at " . $lobbyX . " " . $lobbyY . " " . $lobbyZ );
			$this->getArenaStats($player);	
		}
	}

	public function isArenaAvailable() {
		$available = false;
		if ( count($this->pgin->livePlayers) == 0) {
			$available = true;
		} 
		return $available;
	}
	
	public function getArenaStats(Player $sender) {
		
		if ( count($this->pgin->livePlayers) > 0 ) {
			$sender->sendMessage ( "TnTRun Arena is busy. Please wait!");
			$sender->sendMessage ( "----------------------------------------");
			$sender->sendMessage ( "TnTRun Joined players: ". count($this->pgin->arenaPlayers));
			$sender->sendMessage ( "TnTRun Live players: ". count($this->pgin->livePlayers));
			foreach ($this->pgin->livePlayers as $p) {
				$sender->sendMessage ($p->getName());
			}
			$sender->sendMessage ( "----------------------------------------");
			return;
		}
		
		if ( count($this->pgin->livePlayers) == 0 && count($this->pgin->arenaPlayers) == 0 ) {
			$sender->sendMessage ( "-------------------------------------");
			$sender->sendMessage ( "TnTRun Arena is available for new game!");
			$sender->sendMessage ( "tap [Reset] then [Join] to start play.");
			$sender->sendMessage ( "-------------------------------------");
			return;
		}
		
		if ( count($this->pgin->livePlayers) == 0 && $this->pgin->game_mode==2 ) {
			$sender->sendMessage ( "-------------------------------------");
			$sender->sendMessage ( "Current game is available for [Join]");
			$sender->sendMessage ( "tap [Join] to join current game.");
			$sender->sendMessage ( "-------------------------------------");
			return;
		} else {
		
		//if ( count($this->pgin->livePlayers) == 0 ) {
			$sender->sendMessage ( "-------------------------------------");
			$sender->sendMessage ( "TnTRun Arena is available for new game!");
			$sender->sendMessage ( "tap [Reset] then [Join] to start play.");
			$sender->sendMessage ( "-------------------------------------");			
			return;
		} 

	}
	
	/**
	 * Logging util function
	 *
	 * @param unknown $msg        	
	 */
	private function log($msg) {
		$this->pgin->getLogger ()->info ( $msg );
	}
}