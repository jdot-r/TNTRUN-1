<?php

namespace game\api;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\event\block\BlockEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityMoveEvent;
use pocketmine\event\Listener;
use pocketmine\math\Vector3 as Vector3;
use pocketmine\math\Vector2 as Vector2;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\block\Cactus;

/**
 * MG Arena DAO for PocketMine-MP
 * TntRun PlugIn - MCPE Mini-Game
 *
 * Copyright (C) MCPE_PluginDev
 *
 * @author DavidJBrockway aka MCPE_PluginDev
 *        
 */
class MGArenaPlayerDAO {
	
	private $plugin;
	public function __construct( $pg) {
		$this->plugin = $pg;
	}
	private function log($msg) {
		$this->plugin->getLogger ()->info ( $msg );
	}

	
	/**
	 * retrieve arena by name
	 * 
	 * @param unknown $arena
	 * @param unknown $owner
	 * @return string|multitype:multitype:
	 */
	public function retrievePlayerByName($pname, $arena, $world) {
		$records = [ ];
		try {
			$prepare = $this->plugin->database->prepare ( "SELECT * from arena_player WHERE pname = :pname and arena=:arena and world=:world" );
			$prepare->bindValue ( ":pname", $pname, SQLITE3_TEXT );
			$prepare->bindValue ( ":arena", $arena, SQLITE3_TEXT );
			$prepare->bindValue ( ":world", $world, SQLITE3_TEXT );
			$result = $prepare->execute ();
			if ($result instanceof \SQLite3Result) {
				// $data = $result->fetchArray ( SQLITE3_ASSOC );
				while ( $data = $result->fetchArray ( SQLITE3_ASSOC ) ) {
					// $dataPlayer = $data ['pname'];
					// $dataWorld = $data ['world'];
					// $dataX = $data ['x'];
					// $dataY = $data ['y'];
					// $dataZ = $data ['z'];
					$records [] = $data;
				}
				// var_dump($records);
				$result->finalize ();
			}
		} catch ( \Exception $exception ) {
			return "get failed!: " . $exception->getMessage ();
		}
		return $records;
	}
	
	/**
	 * retrieve arena by name
	 *
	 * @param unknown $arena
	 * @param unknown $owner
	 * @return string|multitype:multitype:
	 */
	public function retrieveArenaPlayers($arena) {
		$records = [ ];
		try {
			$prepare = $this->plugin->database->prepare ( "SELECT * from arena_player WHERE arena=:arena" );
			$prepare->bindValue ( ":arena", $arena, SQLITE3_TEXT );
			$result = $prepare->execute ();
			if ($result instanceof \SQLite3Result) {
				// $data = $result->fetchArray ( SQLITE3_ASSOC );
				while ( $data = $result->fetchArray ( SQLITE3_ASSOC ) ) {
					// $dataPlayer = $data ['pname'];
					// $dataWorld = $data ['world'];
					// $dataX = $data ['x'];
					// $dataY = $data ['y'];
					// $dataZ = $data ['z'];
					$records [] = $data;
				}
				// var_dump($records);
				$result->finalize ();
			}
		} catch ( \Exception $exception ) {
			return "get failed!: " . $exception->getMessage ();
		}
		return $records;
	}
	
	/**
	 * retrieve all arena names
	 * 
	 * @return string|multitype:Ambigous <>
	 */
	public function retrieveAllPlayerName() {
		$records = [ ];
		try {
			$prepare = $this->plugin->database->prepare ( "SELECT distinct pname FROM arena_player" );
			$result = $prepare->execute ();
			if ($result instanceof \SQLite3Result) {
				// $data = $result->fetchArray ( SQLITE3_ASSOC );
				while ( $data = $result->fetchArray ( SQLITE3_ASSOC ) ) {
					// $dataPlayer = $data ['pname'];
					// $dataWorld = $data ['world'];
					// $dataX = $data ['x'];
					// $dataY = $data ['y'];
					// $dataZ = $data ['z'];
					$records [] = $data ["pname"];
				}
				// var_dump($records);
				$result->finalize ();
			}
		} catch ( \Exception $exception ) {
			return "get failed!: " . $exception->getMessage ();
		}
		return $records;
	}
	
	
	/**
	 * upset Arena
	 * 
	 * @param unknown $arena
	 * @param unknown $owner
	 * @param unknown $world
	 * @param unknown $entrance_x
	 * @param unknown $entrance_y
	 * @param unknown $entrance_z
	 * @param unknown $exit_x
	 * @param unknown $exit_y
	 * @param unknown $exit_z
	 * @param unknown $size
	 * @param unknown $capacity
	 * @param unknown $level
	 * @param unknown $isprivate
	 * @param unknown $game
	 * @return string
	 */
	public function upsetArenaPlayer($pname,$arena, $world, $home_x, $home_y, $home_z, $isAllow,$rating,$game) {
		try {
			
			//$this->plugin->getLogger ()->info ( "Pname =" . $pname. " Arena =".$arena );
			
			$prepare = $this->plugin->database->prepare ( "SELECT * from arena_player WHERE pname = :pname and arena=:arena" );
			$prepare->bindValue ( ":pname", $pname, SQLITE3_TEXT );
			$prepare->bindValue ( ":arena", $arena, SQLITE3_TEXT );			
			//$prepare->bindValue ( ":world", $world, SQLITE3_TEXT );			
			$result = $prepare->execute ();
			if ($result instanceof \SQLite3Result) {
				$data = $result->fetchArray ( SQLITE3_ASSOC );								
				
				//$this->plugin->getLogger ()->info ( "dump " );
				//var_dump($data);
								
				$result->finalize ();
				if (isset ( $data ["pname"] )) {
					try {						
						$prepare = $this->plugin->database->prepare ( "UPDATE arena_player SET home_x = :home_x, home_y=:home_y, home_z=:home_z, isAllow=:isAllow, rating=:rating, game=:game WHERE pname = :pname and arena = :arena and world = :world" );
						$prepare->bindValue ( ":home_x",$home_x, SQLITE3_INTEGER );
						$prepare->bindValue ( ":home_y", $home_y, SQLITE3_INTEGER );
						$prepare->bindValue ( ":home_z", $home_z, SQLITE3_INTEGER );
						$prepare->bindValue ( ":isAllow", $isAllow, SQLITE3_INTEGER );
						$prepare->bindValue ( ":rating", $rating, SQLITE3_INTEGER );
						$prepare->bindValue ( ":game", $game, SQLITE3_TEXT );
						$prepare->execute ();						
					} catch ( \Exception $exception ) {
						return "update arena player failed!: " . $exception->getMessage ();
					}
					return "updated arena player succeed!";
				} else {					
					try {
						$prepare = $this->plugin->database->prepare ( "INSERT INTO arena_player (pname,arena, world, home_x,home_y, home_z, isAllow, rating, game) VALUES (:pname,:arena, :world, :home_x,:home_y, :home_z, :isAllow, :rating, :game)" );
						$prepare->bindValue ( ":pname", $pname, SQLITE3_TEXT );
						$prepare->bindValue ( ":arena", $arena, SQLITE3_TEXT );
						$prepare->bindValue ( ":world", $world, SQLITE3_TEXT );
						$prepare->bindValue ( ":home_x",$home_x, SQLITE3_INTEGER );
						$prepare->bindValue ( ":home_y", $home_y, SQLITE3_INTEGER );
						$prepare->bindValue ( ":home_z", $home_z, SQLITE3_INTEGER );
						$prepare->bindValue ( ":isAllow", $isAllow, SQLITE3_INTEGER );
						$prepare->bindValue ( ":rating", $rating, SQLITE3_INTEGER );
						$prepare->bindValue ( ":game", $game, SQLITE3_TEXT );
						$prepare->execute ();
						
						//$this->plugin->getLogger ()->info ( "created arena player record " . $pname );
					} catch ( \Exception $exception ) {
						return "add failed!: " . $exception->getMessage ();
					}
					return "add arena player succeed!";
				}
			}
		} catch ( \Exception $exception ) {
			return "db error: " . $exception->getMessage ();
		}
		// something else if wrong with database
		return "no record";
	}
	
	
	/**
	 * remove arena
	 * 
	 * @param unknown $arena
	 * @param unknown $owner
	 * @param unknown $world
	 * @return string
	 */
	public function removeArena($arena, $pname) {
		try {
			$prepare = $this->plugin->database->prepare ( "DELETE FROM arena_player WHERE pname = :pname and arena=:arena" );
			$prepare->bindValue ( ":pname", $pname, SQLITE3_TEXT );
			$prepare->bindValue ( ":arena", $arena, SQLITE3_TEXT );
			$prepare->execute ();
			//$this->log ( "Removed arena player: ".$pname );
		} catch ( \Exception $exception ) {
			return "deletion arena player failed!: " . $exception->getMessage ();
		}
		return "deleted arena data";
	}
}