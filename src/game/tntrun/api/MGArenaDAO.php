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

/**
 * TntRun PlugIn - MCPE Mini-Game
 *
 * Copyright (C) MCPE_PluginDev
 *
 * @author DavidJBrockway aka MCPE_PluginDev
 *        
 */
class MGArenaDAO {
	
	private $plugin;
	public function __construct($pg) {
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
	public function retrieveArenaByName($arena, $owner, $world) {
		$records = [ ];
		try {
			$prepare = $this->plugin->database->prepare ( "SELECT * FROM arena WHERE arena = :arena and owner=:owner and world=:world" );
			$prepare->bindValue ( ":arena", $arena, SQLITE3_TEXT );
			$prepare->bindValue ( ":owner", $owner, SQLITE3_TEXT );
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
	public function retrieveArena($arena,$world) {
		$records = [ ];
		try {
			$prepare = $this->plugin->database->prepare ( "SELECT * FROM arena WHERE arena = :arena and world=:world" );
			$prepare->bindValue ( ":arena", $arena, SQLITE3_TEXT );
			//$prepare->bindValue ( ":owner", $owner, SQLITE3_TEXT );
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
	 * retrieve all arena names
	 * 
	 * @return string|multitype:Ambigous <>
	 */
	public function retrieveAllArenaName() {
		$records = [ ];
		try {
			$prepare = $this->plugin->database->prepare ( "SELECT distinct arena FROM arena" );
			$result = $prepare->execute ();
			if ($result instanceof \SQLite3Result) {
				// $data = $result->fetchArray ( SQLITE3_ASSOC );
				while ( $data = $result->fetchArray ( SQLITE3_ASSOC ) ) {
					// $dataPlayer = $data ['pname'];
					// $dataWorld = $data ['world'];
					// $dataX = $data ['x'];
					// $dataY = $data ['y'];
					// $dataZ = $data ['z'];
					$records [] = $data ["arena"];
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
	public function upsetArena($arena, $owner, $world, $entrance_x, $entrance_y, $entrance_z, $exit_x,$exit_y,$exit_z,$size, $capacity, $level, $isprivate, $game) {
		try {
			$prepare = $this->plugin->database->prepare ( "SELECT * from arena WHERE arena = :arena and owner=:owner" );
			$prepare->bindValue ( ":arena", $arena, SQLITE3_TEXT );
			$prepare->bindValue ( ":owner", $owner, SQLITE3_TEXT );			
			$result = $prepare->execute ();
			if ($result instanceof \SQLite3Result) {
				$data = $result->fetchArray ( SQLITE3_ASSOC );
				$result->finalize ();
				if (isset ( $data ["arena"] )) {
					try {
						$prepare = $this->plugin->database->prepare ( "UPDATE arena SET world = :world, entrance_x = :entrance_x, entrance_y=:entrance_y, entrance_z=:entrance_z, exit_x=:exit_x, exit_y=:exit_y, exit_z=:exit_z, size=:size, capacity=:capacity, level=:level,isprivate=:isprivate,game=:game WHERE arena = :arena and owner=:owner" );
						$prepare->bindValue ( ":world", $world, SQLITE3_TEXT );
						$prepare->bindValue ( ":entrance_x", $entrance_x, SQLITE3_INTEGER );
						$prepare->bindValue ( ":entrance_y", $entrance_y, SQLITE3_INTEGER );
						$prepare->bindValue ( ":entrance_z", $entrance_z, SQLITE3_INTEGER );
						$prepare->bindValue ( ":exit_x", $exit_x, SQLITE3_INTEGER );
						$prepare->bindValue ( ":exit_y", $exit_y, SQLITE3_INTEGER );
						$prepare->bindValue ( ":exit_z", $exit_z, SQLITE3_INTEGER );												
						$prepare->bindValue ( ":size", $size, SQLITE3_INTEGER );
						$prepare->bindValue ( ":capacity", $capacity, SQLITE3_INTEGER );
						$prepare->bindValue ( ":level", $level, SQLITE3_INTEGER );
						$prepare->bindValue ( ":isprivate", $isprivate, SQLITE3_INTEGER );
						$prepare->bindValue ( ":game", $game, SQLITE3_TEXT );												
						$prepare->execute ();						
					} catch ( \Exception $exception ) {
						return "update arena failed!: " . $exception->getMessage ();
					}
					return "updated arena succeed!";
				} else {
					try {
						$prepare = $this->plugin->database->prepare ( "INSERT INTO arena (arena, owner, world, entrance_x,entrance_y, entrance_z, exit_x, exit_y, exit_z, size, capacity, level, isprivate, game) VALUES (:arena, :owner, :world, :entrance_x,:entrance_y, :entrance_z, :exit_x, :exit_y, :exit_z, :size, :capacity, :level, :isprivate, :game)" );
						$prepare->bindValue ( ":arena", $arena, SQLITE3_TEXT );
						$prepare->bindValue ( ":owner", $owner, SQLITE3_TEXT );
						$prepare->bindValue ( ":world", $world, SQLITE3_TEXT );
						$prepare->bindValue ( ":entrance_x", $entrance_x, SQLITE3_INTEGER );
						$prepare->bindValue ( ":entrance_y", $entrance_y, SQLITE3_INTEGER );
						$prepare->bindValue ( ":entrance_z", $entrance_z, SQLITE3_INTEGER );
						$prepare->bindValue ( ":exit_x", $exit_x, SQLITE3_INTEGER );
						$prepare->bindValue ( ":exit_y", $exit_y, SQLITE3_INTEGER );
						$prepare->bindValue ( ":exit_z", $exit_z, SQLITE3_INTEGER );
						$prepare->bindValue ( ":size", $size, SQLITE3_INTEGER );
						$prepare->bindValue ( ":capacity", $capacity, SQLITE3_INTEGER );
						$prepare->bindValue ( ":level", $level, SQLITE3_INTEGER );
						$prepare->bindValue ( ":isprivate", $isprivate, SQLITE3_INTEGER );
						$prepare->bindValue ( ":game", $game, SQLITE3_TEXT );
						$prepare->execute ();
						//$this->plugin->getLogger ()->info ( "created arena record " . $arena );
					} catch ( \Exception $exception ) {
						return "add failed!: " . $exception->getMessage ();
					}
					return "add TNTRun Arena succeed!";
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
	public function removeArena($arena, $owner) {
		try {
			$prepare = $this->plugin->database->prepare ( "DELETE FROM arena WHERE arena = :arena and owner=:owner" );
			$prepare->bindValue ( ":arena", $arena, SQLITE3_TEXT );
			$prepare->bindValue ( ":owner", $owner, SQLITE3_TEXT );			
			//$prepare->bindValue ( ":world", $world, SQLITE3_TEXT );
			$prepare->execute ();
			$this->log ( "Removed arena: ".$arena );
		} catch ( \Exception $exception ) {
			return "deletion failed!: " . $exception->getMessage ();
		}
		return "deleted arena data";
	}
}