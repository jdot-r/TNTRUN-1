<?php

namespace game\api;

use pocketmine\utils\TextFormat;

/**
 * Datastore
 * TntRun PlugIn - MCPE Mini-Game
 *
 * Copyright (C) MCPE_PluginDev
 *
 * @author DavidJBrockway aka MCPE_PluginDev
 *        
 */

class MGDatastoreConfig {
	const DB_STORE_FILE = "tntrun.db";
	const DB_SQL_FILE_ARENA = "sqlite3_arena.sql";
	const DB_SQL_FILE_ARENA_PLAYER = "sqlite3_arena_player.sql";
	const DB_SQL_FILE_ARENA_ROUND = "sqlite3_arena_round.sql";
	const DB_SQL_FILE_LIVE_PLAYER = "sqlite3_live_player.sql";
	private $plugin;
	public function __construct( $pg) {
		$this->plugin = $pg;
	}
	private function log($msg) {
		$this->plugin->getLogger ()->info ( $msg );
	}
	
	/**
	 * Initialize database
	 */
	public function initlize() {
		// create plugin folder
		@mkdir ( $this->plugin->getDataFolder () );
		if (! file_exists ( $this->plugin->getDataFolder () . $this::DB_STORE_FILE )) {
			// open in file
			//$this->plugin->database = new \SQLite3 ( $this->plugin->getDataFolder () . $this::DB_STORE_FILE, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE );			
			//open in-memory database
			//":memory:"
			$this->plugin->database = new \SQLite3 (":memory:",SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
			
			// create Arena tables
			$resource = $this->plugin->getResource ( $this::DB_SQL_FILE_ARENA );
			$this->plugin->database->exec ( stream_get_contents ( $resource ) );
			$this->log ( TextFormat::BLUE . "- TNTRun created new Arena in-memory database." );
			
			// create Arena Player tables
			$resource = $this->plugin->getResource ( $this::DB_SQL_FILE_ARENA_PLAYER );
			$this->plugin->database->exec ( stream_get_contents ( $resource ) );
			$this->log ( TextFormat::BLUE . "- TNTRun created new Arena player in-memory database." );
			
			// create Arena round tables
			$resource = $this->plugin->getResource ( $this::DB_SQL_FILE_ARENA_ROUND );
			$this->plugin->database->exec ( stream_get_contents ( $resource ) );
			$this->log ( TextFormat::BLUE . "- TNTRun created new Arena round in-memory database." );
			
			// create live player tables
			$resource = $this->plugin->getResource ( $this::DB_SQL_FILE_LIVE_PLAYER );
			$this->plugin->database->exec ( stream_get_contents ( $resource ) );
			$this->log ( TextFormat::BLUE . "- TNTRun created new live player in-memory database." );
		} else {
			$this->plugin->database = new \SQLite3 ( $this->plugin->getDataFolder () . $this::DB_STORE_FILE, SQLITE3_OPEN_READWRITE );
			$this->log ( TextFormat::BLUE . "- TNTRun loaded use existing player in-memory database." );
		}
	}
}

