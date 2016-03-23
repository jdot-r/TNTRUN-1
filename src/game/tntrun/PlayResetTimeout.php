<?php

namespace game\tntrun;

use pocketmine\scheduler\PluginTask;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\level\Explosion;
use pocketmine\level\Position;


/**
 * TntRun PlugIn - MCPE Mini-Game
 *
 * Copyright (C) MCPE_PluginDev
 *
 * @author DavidJBrockway aka MCPE_PluginDev
 *        
 */
class PlayResetTimeout extends PluginTask {
	private $pgin;
	public function __construct(TNTRun $plugin) {
		$this->pgin = $plugin;
		parent::__construct ( $plugin );
	}
	public function onRun($ticks) {
		$this->pgin->game_mode = 0;
		
		$resetScheduler = $this->pgin->getConfig ()->get ( "reset_scheduler" );
		if ($resetScheduler == null || $resetScheduler == "no") {
			// do nothing
			return;
		}
		
		$resetValue = $this->pgin->getConfig ()->get ( "reset_timeout" );
		if ($resetValue==null) {
			$resetValue = 8000;
		}
		
		$resetOption = $this->pgin->getConfig ()->get ( "reset_option" );
		$tntrunhome = $this->pgin->getConfig ()->get ( "tntrun_arena_world" );
		$arenaName = $this->pgin->getConfig ()->get ( "tntrun_arena_name" );
		$arenaSize = $this->pgin->getConfig ()->get ( "tntrun_arena_size" );
		$arenaX = $this->pgin->getConfig ()->get ( "tntrun_arena_x" );
		$arenaY = $this->pgin->getConfig ()->get ( "tntrun_arena_y" );
		$arenaZ = $this->pgin->getConfig ()->get ( "tntrun_arena_z" );		
		
		// display winners
		if (count ( $this->pgin->livePlayers ) > 0) {
			$this->pgin->getServer ()->broadcastMessage ( "|*************************|" );
			$this->pgin->getServer ()->broadcastMessage ( "|* CONGLATULATION!!!     *|" );
			$this->pgin->getServer ()->broadcastMessage ( "***************************" );
			$this->pgin->getServer ()->broadcastMessage ( "*[TnTRun] Round Winners:" . count ( $this->pgin->livePlayers ) );
			foreach ( $this->pgin->livePlayers as $player ) {
				$this->pgin->getServer ()->broadcastMessage ( "> " . $player->getName () );				
			}
			foreach ( $this->pgin->arenaPlayers as $player ) {
				//send each players back to lobby on reset
				$lobbyX = $this->pgin->getConfig ()->get ( "tntrun_lobby_x" );
				$lobbyY = $this->pgin->getConfig ()->get ( "tntrun_lobby_y" );
				$lobbyZ = $this->pgin->getConfig ()->get ( "tntrun_lobby_z" );
				$player->teleport ( new Position ( $lobbyX, $lobbyY, $lobbyZ ));
			}			
			$this->pgin->getServer ()->broadcastMessage ( "***************************" );
		}
		
		if ($resetOption != null && $resetOption == "FULL") {
			// $this->pgin->stadiumBuilder->buildStadium($this->pgin->getServer(), new Position ( $statiumX, $statiumY, $statiumZ ), $statiumSize );
			// do nothing at this moment
		} else {
			$homelevel = $this->pgin->getServer ()->getLevelByName ( $tntrunhome );
			if ($homelevel != null) {
				$arenaInfo = $this->pgin->arenaBuilder->resetArenaBuilding ( $homelevel, new Position ( $arenaX, $arenaY, $arenaZ ) );
				$this->pgin->game_mode = 0;
				$this->pgin->livePlayers = [ ];
				$this->pgin->arenaPlayers = [ ];
				
				// send the arena owner first
				$this->pgin->getServer ()->broadcastMessage ( "***********************************" );
				$this->pgin->getServer ()->broadcastMessage ( "* TnTRun reset done, It's ready!  *" );
				$this->pgin->getServer ()->broadcastMessage ( "* players tap [Join] sign to play  *" );
				$this->pgin->getServer ()->broadcastMessage ( "***********************************" );
				
				$this->pgin->getServer ()->broadcastMessage ( "-----------------------------" );
				$this->pgin->getServer ()->broadcastMessage ( " Next [TnTRun] Reset in " . $resetValue . " ticks" );
				$this->pgin->getServer ()->broadcastMessage ( "-----------------------------" );
				
			} else {
				$this->pgin->getLogger ()->info ( "TnTRun Missing Configuration: Unable to load TnTRun World [" . $tntrunhome . "]" );
			}
		}		
		
	}
	public function giveAll() {
		$data = $this->generateData ();
		// $this->broadcast("Random item given! (" . $data["id"] . ":" . $data["meta"] . ")");
		foreach ( $this->pgin->getServer ()->getOnlinePlayers () as $p ) {
			$this->give ( $p, $data );
		}
	}
	public function give($p, $data) {
		$item = new Item ( $data ["id"], $data ["meta"], $data ["amount"] );
		$p->getInventory ()->addItem ( $item );
	}
	public function generateData() {
		return $this->itemdata [rand ( 0, (count ( $this->itemdata ) - 1) )];
	}
	public function onCancel() {
	}
}
