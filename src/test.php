<?php

include 'game.php';

function allData($telegramId) {
    $game = getGame($telegramId);
    
    print_r($game);
}

$user_funcs = [
    'reset' => 'setUpTables', //admin only
    'start' => 'startGame',
    'end' => 'endGame',
    'srs' => 'srs',
    'lrs' => 'lrs',
    'stats' => 'stats',
    'shield' => 'shield',
    'torpedo' => 'torpedo',
    'laser' => 'laser',
    'move' => 'move',
    'help' => 'help',
    'exit' => 'stop', 'stop' => 'stop',
    'data' => 'allData'
];

$fh = fopen('php://stdin', 'r');
$reading = true;
$game = null;
while ($reading) {
    $next_line = fgets($fh, 1024); // read the special file to get the user input from keyboard
    $parts = preg_split('/\s+/', $next_line, -1, PREG_SPLIT_NO_EMPTY);
    $command = strtolower( $parts[0] );
    
    if (!in_array($command, array_keys($user_funcs))) {
        echo "game: $command: command not found. Type 'help' for more info.\n";
        continue;
    }
    $command = $user_funcs[$command];
    
    // build parameters
    $parts[0] = 0; //userId
    
    $result = call_user_func_array($command, $parts);
    
    if (is_string($result)) {
        echo $result . "\n";
        continue;
    }
    if (array_key_exists('extra', $result)) {
        echo $result['extra'];
        unset($result['extra']);
    }
    echo "success\n\n";
    $game = $result;
}



/*
setUpTables();

$newgame = startGame(0);

var_dump($newgame); die(1);

echo lrs(0, $newgame);
echo "\n";
echo srs(0, $newgame);
echo "\n";


print_r (stats(0, $newgame));

$newgame = shield(0, 100, $newgame);

print_r (stats(0, $newgame));

$newgame['spaceship'] = moveObject($newgame['spaceship'], 0.75, 80, $newgame);
print_r( $newgame['spaceship']  );

$newgame['spaceship'] = moveObject($newgame['spaceship'], 0.75, 80, $newgame);
print_r( $newgame['spaceship']  );

$newgame['spaceship'] = moveObject($newgame['spaceship'], 0.75, 80, $newgame);
print_r( $newgame['spaceship']  );


print_r (stats(0, $newgame));

echo srs(0, $newgame);
echo "\n";
echo lrs(0, $newgame);*/