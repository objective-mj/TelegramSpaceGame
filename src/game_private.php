<?php

function newBoard()
{

}

function enemyTurn($telegramId, $game = null)
{
    if (!$game)
        $game = getGame($telegramId);

    return $game;
}

function checkCoords($x, $y, $q, $game)
{
    $enemies = $game['enemies'];
    $meteors = $game['meteors'];
    $tanks = $game['tanks'];

    $check = function ($items) use ($x, $y, $q) {
        foreach ($items as $item) {
            if ($item['coords_q'] != $q)
                continue;
            if ( $x < $item['coords_x'] + 10 && $x > $item['coords_x'] - 10  &&
                 $y < $item['coords_y'] + 10 && $y > $item['coords_y'] - 10 )
                 return false;
        }
        return true;
    };

    if (!$check($tanks))
        throw new Exception('tank_event');

    return $check($enemies) && $check($meteors);

}

function quadronCoords($items, $q)
{
    $temps;
    foreach ($items as $item)
    {
        if ($q != $item['coords_q'])
            continue;
        $x = round(( ($item['coords_x'] + 100) / 5) +1) ;
        $y = round(( ($item['coords_y'] + 100) / 10) +1) ;

        $temps[$y] = $temps[$y] ?: [];
        $temps[$y][] = $x;
    }
    return $temps;
}

function visualiseQuadron($game)
{
    $spaceShip = $game['spaceship'];
    $enemies = $game['enemies'];
    $meteors = $game['meteors'];
    $tanks = $game['tanks'];

    $spaceShipX = round(( ($spaceShip['coords_x'] + 100) / 5 ) + 1) ;
    $spaceShipY = round(( ($spaceShip['coords_y'] + 100) / 10 ) + 1) ;

    $enemies = quadronCoords($enemies, $spaceShip['coords_q']);
    $meteors = quadronCoords($meteors, $spaceShip['coords_q']);
    $tanks = quadronCoords($tanks, $spaceShip['coords_q']);

    $row_divs = "+= = = = = = = = = = = = = = = = = = = = =+\n";
    $row_fill = "| . . . . . . . . . . . . . . . . . . . . |\n";

    $board = "";
    for ($i = 0; $i < 23; $i++) {
        switch ($i) {
            case 0:
            case 22:
                $row = $row_divs;
                break;
            default:
                $row = $row_fill;
        }

        if ($spaceShipY == $i)
            $row = substr_replace($row, 'ᐂ', $spaceShipX, 1);

        if ($enemies[$i]) {
            foreach ($enemies[$i] as $value)
                $row = substr_replace($row, 'Ϙ', $value, 1);
        }

        if ($meteors[$i]) {
            foreach ($meteors[$i] as $value)
                $row = substr_replace($row, '*', $value, 1);
        }

        if ($tanks[$i]) {
            foreach ($tanks[$i] as $value)
                $row = substr_replace($row, '΢', $value, 1);
        }

        $board .= $row;
    }
    return $board;
}

function visualiseBoard($game)
{
    $spaceShip = $game['spaceship'];
    $enemies = $game['enemies'];
    $meteors = $game['meteors'];
    $tanks = $game['tanks'];

    $values = [
        1 => 0,
        2 => 0,
        3 => 0,
        4 => 0,
        5 => 0,
        6 => 0,
        7 => 0,
        8 => 0,
        9 => 0,
    ];

    foreach ($enemies as $item) {
        $values[$item['coords_q']] += 100;
    }
    foreach ($meteors as $item) {
        $values[$item['coords_q']] += 1;
    }
    foreach ($tanks as $item) {
        $values[$item['coords_q']] += 10;
    }

    foreach ($values as $key => $value) {
        if ($value < 1) {
            $values[$key] = " 000 ";
        }
        elseif ($value < 10) {
            $values[$key] = " 00{$value} ";
        }
        elseif ($value < 100) {
            $values[$key] = " 0{$value} ";
        }
        else {
            $values[$key] = " {$value} ";
        }
    }

    $values[$spaceShip['coords_q']] = substr_replace($values[$spaceShip['coords_q']] , '{', 0, 1);
    $values[$spaceShip['coords_q']] = substr_replace($values[$spaceShip['coords_q']] , '}', 4, 1);

    $board =  "*= = =*= = =*= = =*\n";
    $board .= "*{$values[1]}|{$values[2]}|{$values[3]}*\n";
    $board .= "*- - -*- - -*- - -*\n";
    $board .= "*{$values[4]}|{$values[5]}|{$values[6]}*\n";
    $board .= "*- - -*- - -*- - -*\n";
    $board .= "*{$values[7]}|{$values[8]}|{$values[9]}*\n";
    $board .= "*= = =*= = =*= = =*\n";


    return $board;
}