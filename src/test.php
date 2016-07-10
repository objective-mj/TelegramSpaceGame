<?php

include 'game.php';

//setUp();

$newgame = startGame(0);
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
echo lrs(0, $newgame);