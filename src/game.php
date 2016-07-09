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
        throw new Exception('Energy too low.');

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
        throw new Exception('Invalid input');

    if ($game['spaceship']['fuel'] < $distance/2)
        throw new Exception('Not Enough Fuel Left');

    $degree = round( $direction * 360 );

    switch ($degree) {
        case 0:
        case 360:
            $coordY = $game['spaceship']['coords_y'];
            $coordQ = $game['spaceship']['coords_q'];

            if ($coordY - $distance < -100 && in_array($coordQ, [1,2,3])) {
                $y = -100;
                $x = $game['spaceship']['coords_x'];
                $q = $coordQ;
                if (!checkCoords($x, $y, $q, $game))
                    throw new Exception('something is in the way');
                $game['spaceship']['coords_y'] = $y;
            } elseif ($coordY - $distance < -100) {
                $y = 200 + $coordY - $distance;
                $x = $game['spaceship']['coords_x'];
                $q = $coordQ - 3;
                if (!checkCoords($x, $y, $q, $game))
                    throw new Exception('something is in the way');

                $game['spaceship']['coords_y'] = $y;
                $game['spaceship']['coords_q'] = $q;
            } else {
                $y = $coordY - $distance;
                $x = $game['spaceship']['coords_x'];
                $q = $coordQ;
                if (!checkCoords($x, $y, $q, $game))
                    throw new Exception('something is in the way');

                $game['spaceship']['coords_y'] = $y;
            }
            break;
        case 180:
            $coordY = $game['spaceship']['coords_y'];
            $coordQ = $game['spaceship']['coords_q'];

            if ($coordY + $distance > 100 && in_array($coordQ, [7,8,9])) {
                $y = 100;
                $x = $game['spaceship']['coords_x'];
                $q = $coordQ;
                if (!checkCoords($x, $y, $q, $game))
                    throw new Exception('something is in the way');

                $game['spaceship']['coords_y'] = $y;
            } elseif ($coordY + $distance > 100 ) {
                $y = $coordY + $distance - 200;
                $x = $game['spaceship']['coords_x'];
                $q = $coordQ + 3;
                if (!checkCoords($x, $y, $q, $game))
                    throw new Exception('something is in the way');

                $game['spaceship']['coords_y'] = $y;
                $game['spaceship']['coords_q'] = $q;
            } else {
                $y = $coordY + $distance;
                $x = $game['spaceship']['coords_x'];
                $q = $coordQ;
                if (!checkCoords($x, $y, $q, $game))
                    throw new Exception('something is in the way');

                $game['spaceship']['coords_y'] = $y;
            }
            break;
        case 90:
        case 180:
            $coordX = $game['spaceship']['coords_x'];
            $coordQ = $game['spaceship']['coords_q'];

            if ($coordX + $distance > 100 && in_array($coordQ, [3,6,9])) {
                $game['spaceship']['coords_x'] = 100;
            } elseif ($coordX + $distance > 100 ) {
                $game['spaceship']['coords_x'] += $distance;
                $game['spaceship']['coords_q'] += 1;
            } elseif ($coordX + $distance < 100 && in_array($coordQ, [1,4,7])) {
                $game['spaceship']['coords_x'] = -100;
            } elseif ($coordX + $distance < 100) {
                $game['spaceship']['coords_x'] += $distance;
                $game['spaceship']['coords_q'] -= 1;
            } else {
                $game['spaceship']['coords_x'] += $distance;
            }
            break;
        default:
            break;
    }

    $game['spaceship']['fuel'] -= $distance / 2;

    $game = enemyTurn($telegramId, $game);

    updateGame($telegramId, $game);

    return $game;
}