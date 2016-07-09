<?php

include 'game.php';
include 'database.php';

//setUp();

$newgame = startGame(0);
echo lrs(0, $newgame);
echo "\n";
echo srs(0, $newgame);
echo "\n";

print_r (stats(0, $newgame));

$newgame = shield(0, 100, $newgame);

print_r (stats(0, $newgame));

for ($i = 0; $i < 500; $i++) {
    try {
        $newgame = move(0, rand(0,100) / 100, rand(0,100), $newgame);
        print ($newgame['spaceship']['coords_q']);
    } catch(Exception $e) {
        print_r (stats(0, $newgame));
        throw $e;
    }
}

print_r (stats(0, $newgame));

echo srs(0, $newgame);
echo "\n";
echo lrs(0, $newgame);