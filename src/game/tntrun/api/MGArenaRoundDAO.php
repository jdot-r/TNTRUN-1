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
class MGArenaRoundDAO {
	
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
	public function retrieveRoundByName($round, $arena, $world) {
		$records = [ ];
		try {
			$prepare = $this->plugin->database->prepare ( "SELECT * from arena_round WHERE round = :round and arena=:arena and world=:world" );
			$prepare->bindValue ( ":round", $pname, SQLITE3_TEXT );
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
	 * retrieve all arena names
	 * 
	 * @return string|multitype:Ambigous <>
	 */
	public function retrieveAllRoundName() {
		$records = [ ];
		try {
			$prepare = $this->plugin->database->prepare ( "SELECT distinct round FROM arena_round" );
			$result = $prepare->execute ();
			if ($result instanceof \SQLite3Result) {
				// $data = $result->fetchArray ( SQLITE3_ASSOC );
				while ( $data = $result->fetchArray ( SQLITE3_ASSOC ) ) {
					// $dataPlayer = $data ['pname'];
					// $dataWorld = $data ['world'];
					// $dataX = $data ['x'];
					// $dataY = $data ['y'];
					// $dataZ = $data ['z'];
					$records [] = $data ["round"];
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
	public function upsetArenaRound($round,$arena,$owner, $world, $round_x, $round_y, $round_z, $exit_x, $exit_y, $exit_z, $minPlayers,$maxPlayers,$roundTime,$timeOut, $game) {

		try {
			$prepare = $this->plugin->database->prepare ( "SELECT * from arena_round WHERE round = :round and arena=:arena and world=:world" );
			$prepare->bindValue ( ":round", $pname, SQLITE3_TEXT );
			$prepare->bindValue ( ":arena", $arena, SQLITE3_TEXT );			
			$prepare->bindValue ( ":world", $world, SQLITE3_TEXT );			
			$result = $prepare->execute ();
			if ($result instanceof \SQLite3Result) {
				$data = $result->fetchArray ( SQLITE3_ASSOC );
				$result->finalize ();
				if (isset ( $data ["round"] )) {
					try {						
						$prepare = $this->plugin->database->prepare ( "UPDATE arena_round SET round_x=:round_x, round_y=:round_y , round_z=:round_z, exit_x=:exit_x, exit_y=:exit_y , exit_z=:exit_z, minPlayers=:minPlayers, maxPlayers=:maxPlayers , roundTime=:roundTime, timeOut=:timeOut , game=:game WHERE round = :round and arena=:arena and world=:world" );
						$prepare->bindValue ( ":round_x",$round_x, SQLITE3_INTEGER );
						$prepare->bindValue ( ":round_y", $round_y, SQLITE3_INTEGER );
						$prepare->bindValue ( ":round_z", $round_z, SQLITE3_INTEGER );						
						$prepare->bindValue ( ":exit_x",$round_x, SQLITE3_INTEGER );
						$prepare->bindValue ( ":exit_y", $round_y, SQLITE3_INTEGER );
						$prepare->bindValue ( ":exit_z", $round_z, SQLITE3_INTEGER );												
						$prepare->bindValue ( ":minPlayers", $minPlayers, SQLITE3_INTEGER );
						$prepare->bindValue ( ":maxPlayers", $maxPlayers, SQLITE3_INTEGER );
						$prepare->bindValue ( ":roundTime", $roundTime, SQLITE3_INTEGER );
						$prepare->bindValue ( ":timeOut", $timeOut, SQLITE3_INTEGER );			
						$prepare->bindValue ( ":game", $game, SQLITE3_TEXT );						
						$prepare->execute ();						
					} catch ( \Exception $exception ) {
						return "update arena player failed!: " . $exception->getMessage ();
					}
					return "updated arena player succeed!";
				} else {
					try {
						$prepare = $this->plugin->database->prepare ( "INSERT INTO arena_round (round,arena, owner, world, round_x,round_y, round_z, exit_x, exit_y, exit_z,minPlayers,maxPlayers,roundTime,timeOut,game) VALUES (:round,:arena,owner, :world, round_x=:round_x, round_y=:round_y , round_z=:round_z, exit_x=:exit_x, exit_y=:exit_y , exit_z=:exit_z, minPlayers=:minPlayers, maxPlayers=:maxPlayers , roundTime=:roundTime, timeOut=:timeOut , game=:game)" );
						$prepare->bindValue ( ":round", $round, SQLITE3_TEXT );
						$prepare->bindValue ( ":arena", $arena, SQLITE3_TEXT );
						$prepare->bindValue ( ":owner", $owner, SQLITE3_TEXT );						
						$prepare->bindValue ( ":world", $world, SQLITE3_TEXT );
						$prepare->bindValue ( ":round_x",$round_x, SQLITE3_INTEGER );
						$prepare->bindValue ( ":round_y", $round_y, SQLITE3_INTEGER );
						$prepare->bindValue ( ":round_z", $round_z, SQLITE3_INTEGER );						
						$prepare->bindValue ( ":exit_x",$exit_x, SQLITE3_INTEGER );
						$prepare->bindValue ( ":exit_y", $exit_y, SQLITE3_INTEGER );
						$prepare->bindValue ( ":exit_z", $exit_z, SQLITE3_INTEGER );						
						$prepare->bindValue ( ":minPlayers", $isAllow, SQLITE3_INTEGER );
						$prepare->bindValue ( ":maxPlayers", $rating, SQLITE3_INTEGER );
						$prepare->bindValue ( ":roundTime", $isAllow, SQLITE3_INTEGER );
						$prepare->bindValue ( ":timeOut", $rating, SQLITE3_INTEGER );												
						$prepare->bindValue ( ":game", $game, SQLITE3_TEXT );
						$prepare->execute ();
						//$this->plugin->getLogger ()->info ( "created arena player record " . $pzone );
					} catch ( \Exception $exception ) {
						return "add failed!: " . $exception->getMessage ();
					}
					return "add arena round succeed!";
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
	public function removeArena($round, $arena, $world) {
		try {
			$prepare = $this->plugin->database->prepare ( "DELETE FROM arena_round WHERE round = :round and arena=:arena and world=:world" );
			$prepare->bindValue ( ":round", $pname, SQLITE3_TEXT );
			$prepare->bindValue ( ":arena", $arena, SQLITE3_TEXT );
			$prepare->bindValue ( ":world", $world, SQLITE3_TEXT );
			$prepare->execute ();
			$this->log ( "Removed arena round: ".$pname );
		} catch ( \Exception $exception ) {
			return "deletion arena round failed!: " . $exception->getMessage ();
		}
		return "deleted arena round data";
	}
}