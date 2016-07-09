<?php

require 'game_private.php';

function srs($telegramId, $game = null)
{
    if (!$game)
        $game = getGame($telegramId);

    return visualiseQuadron($game);

}

function lrs($telegramId, $game = null)
{
    if (!$game)
        $game = getGame($telegramId);

    return visualiseBoard($game);
}

function stats($telegramId, $game = null)
{
    if (!$game)
        $game = getGame($telegramId);

    unset($game['spaceship']['coords_x'], $game['spaceship']['coords_y'], $game['spaceship']['coords_q']);
    return $game['spaceship'];
}

function startGame($telegramId)
{
    $boardCheck = [
        1 => [],
        2 => [],
        3 => [],
        4 => [],
        5 => [
            0 => 0,
        ],
        6 => [],
        7 => [],
        8 => [],
        9 => [],
    ];
    $spaceship = [
        "health" => 100,
        "energy" => 500,
        "shield" => 0,
        "torpedos" => 5,
        "fuel" => 1000,
        "score" => 0,
        "coords_x" => 0,
        "coords_y" => 0,
        "coords_q" => 5
    ];

    $enemies = [];
    $count = rand(6, 12);
    for ($i = 0; $i < $count; $i++) {
        $enemy = [
            "health" => 100,
            "energy" => rand(50, 200),
            "shield" => 0,
            "torpedos" => rand(2,4),
            "fuel" => rand(200,500),
            "coords_x" => rand(-100, 100),
            "coords_y" => rand(-100, 100),
            "coords_q" => rand(1,9)
        ];
        if ($boardCheck[$enemy["coords_q"]][$enemy["coords_y"]] == $enemy['coords_x']) {
            $i--;
            continue;
        }
        $boardCheck[$enemy["coords_q"]][$enemy["coords_y"]] = $enemy['coords_x'];
        $enemies[] = $enemy;
    }

    $meteors = [];
    $count = rand(10, 30);
    for ($i = 0; $i < $count; $i++) {
        $meteor = [
            "density" => rand(1,10),
            "coords_x" => rand(-100, 100),
            "coords_y" => rand(-100, 100),
            "coords_q" => rand(1,9)
        ];
        if ($boardCheck[$meteor["coords_q"]][$meteor["coords_y"]] == $meteor['coords_x']) {
            $i--;
            continue;
        }
        $boardCheck[$meteor["coords_q"]][$meteor["coords_y"]] = $meteor['coords_x'];
        $meteors[] = $meteor;
    }

    $tanks = [];
    $count = rand(1, 4);
    for ($i = 0; $i < $count; $i++) {
        $tank = [
            "fuel" => rand(200,1000),
            "coords_x" => rand(-100, 100),
            "coords_y" => rand(-100, 100),
            "coords_q" => rand(1,9)
        ];
        if ($boardCheck[$tank["coords_q"]][$tank["coords_y"]] == $tank['coords_x']) {
            $i--;
            continue;
        }
        $boardCheck[$tank["coords_q"]][$tank["coords_y"]] = $tank['coords_x'];
        $tanks[] = $tank;
    }



    return [
        'spaceship' => $spaceship,
        'enemies' => $enemies,
        'meteors' => $meteors,
        'tanks' => $tanks
    ];

}

function endGame($telegramId)
{

}

function shield($telegramId, $energy, $game = null)
{
    if (!$game)
        $game = getGame($telegramId);

    if ($game['spaceship']['energy'] < $energy)
        throw new Exception('energy_low');

    $game['spaceship']['energy'] -= $energy;
    $game['spaceship']['shield'] += $energy;

    updateGame($telegramId, $game);

    return $game;
}

function torpedo($telegramId, $direction)
{

    enemyTurn();
}

function laser($telegramId, $direction, $energy)
{

    enemyTurn();
}

function move($telegramId, $direction, $distance, $game = null)
{
    if (!$game)
        $game = getGame($telegramId);

    if ($direction < 0 || $direction > 1 ||
        $distance < 0 || $distance > 100)
        throw new ErrorException('Invalid input');

    if ($game['spaceship']['fuel'] < $distance / 2)
        throw new Exception('fuel_low');

    $degree = round( $direction * 360 );

    $coordX = $game['spaceship']['coords_x'];
    $coordY = $game['spaceship']['coords_y'];
    $coordQ = $game['spaceship']['coords_q'];

    $newX = $degree ? $coordX - (-101 -$coordY) * sin($degree) : $coordX;
    $newY = $degree ? $coordY + (-101 -$coordY) * cos($degree) : -101;

    $factor = $distance / (sqrt( pow($newX-$coordX, 2) + pow($newY-$coordY, 2)));

    $newX *= $factor;
    $newY *= $factor;

    if ($degree < 90) {
            // right and up.
            // x++,
            if ($newX > 100 && in_array($coordQ, [3,6,9])) {
                $x = 100;
                $q = $coordQ;
            } elseif ($newX > 100) {
                $x = -200 + $newX;
                $q = $coordQ + 1;
            } else {
                $x = $newX;
                $q = $coordQ;
            }

            //y --
            if ($newY < -100 && in_array($coordQ, [1,2,3])) {
                $y = -100;
                $q = $q;
            } elseif ($newY < -100) {
                $y = 200 + $newY;
                $q = $q - 3;
            } else {
                $y = $newY;
                $q = $q;
            }
    } elseif ($degree < 180) {
            // right and down.
            // x++,
            if ($newX > 100 && in_array($coordQ, [3,6,9])) {
                $x = 100;
                $q = $coordQ;
            } elseif ($newX > 100) {
                $x = -200 + $newX;
                $q = $coordQ + 1;
            } else {
                $x = $newX;
                $q = $coordQ;
            }
            //y ++
            if ($newY > 100 && in_array($coordQ, [7,8,9])) {
                $y = 100;
                $q = $q;
            } elseif ($newY > 100) {
                $y = -200 + $newY;
                $q = $q + 3;
            } else {
                $y = $newY;
                $q = $q;
            }

    } elseif ($degree < 270) {
            // left and down.
            // x--
            if ($newX < -100 && in_array($coordQ, [1,4,7])) {
                $x = -100;
                $q = $coordQ;
            } elseif ($newX < -100) {
                $x = 200 + $newX;
                $q = $coordQ - 1;
            } else {
                $x = $newX;
                $q = $coordQ;
            }

            //y ++
            if ($newY > 100 && in_array($coordQ, [7,8,9])) {
                $y = 100;
                $q = $q;
            } elseif ($newY > 100) {
                $y = -200 + $newY;
                $q = $q + 3;
            } else {
                $y = $newY;
                $q = $q;
            }

    } elseif ($degree <= 360) {
            //left and up
            // x--,
            if ($newX < -100 && in_array($coordQ, [1,4,7])) {
                $x = -100;
                $q = $coordQ;
            } elseif ($newX < -100) {
                $x = 200 + $newX;
                $q = $coordQ - 1;
            } else {
                $x = $newX;
                $q = $coordQ;
            }

            //y--
            if ($newY < -100 && in_array($coordQ, [1,2,3])) {
                $y = -100;
                $q = $q;
            } elseif ($newY < -100) {
                $y = 200 + $newY;
                $q = $q - 3;
            } else {
                $y = $newY;
                $q = $q;
            }
    } else {
            echo $degree;
            throw new ErrorException('Something went wrong with the angle');
    }

    if (!checkCoords($x, $y, $q, $game))
        return $game;

    $game['spaceship']['coords_y'] = $y;
    $game['spaceship']['coords_x'] = $x;
    $game['spaceship']['coords_q'] = $q;

    $game['spaceship']['fuel'] -= $distance / 2;

    $game = enemyTurn($telegramId, $game);

    updateGame($telegramId, $game);

    return $game;
}