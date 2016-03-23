CREATE TABLE arena (
  arena TEXT PRIMARY KEY,
  owner TEXT,
  world TEXT,
  entrance_x INTEGER,
  entrance_y INTEGER,
  entrance_z INTEGER,
  exit_x INTEGER,
  exit_y INTEGER,
  exit_z INTEGER,    
  size INTEGER,    
  capacity INTEGER,
  level INTEGER,      
  isprivate INTEGER,      
  game TEXT    
);