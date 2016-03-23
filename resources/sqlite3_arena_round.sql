CREATE TABLE arena_round (
  round TEXT PRIMARY KEY,
  arena TEXT,
  owner TEXT,  
  world TEXT,
  round_x INTEGER,
  round_y INTEGER,
  round_z INTEGER,
  exit_x INTEGER,
  exit_y INTEGER,
  exit_z INTEGER,  
  minPlayers INTEGER,      
  maxPlayers INTEGER,
  roundTime INTEGER,
  timeOut INTEGER,
  game TEXT    
);