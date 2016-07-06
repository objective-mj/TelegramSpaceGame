<?php

/*
* input [-100..100] [1..9]
* output [1..10[11..20[..29]
*/
function longY($y, $q)
{
    if ( $q < 1 || $q > 9 || $y < -100 || $y > 100)
        return false;
    $ones = round( ( ($y + 100) / 25 ) + 1);
    switch ($q) {
        case 1 || 2 || 3:
            $tens = 0;
            break;
        case 4 || 5 || 6:
            $tens = 10;
            break;
        case 7 || 8 || 9:
            $tens = 20;
            break;
    }
    return $tens + $ones;
}

/*
* input [-100..100] [1..9]
* output odds in [1..53]
*/
function longX($x, $q)
{
    if ( $q < 1 || $q > 9 || $x < -100 || $x > 100)
        return false;
    $ones = round( ( ($x + 100) / 25 ) );
    $ones = $ones + $ones + 1;

    switch ($q) {
        case 1 || 4 || 7:
            $tens = 0;
            break;
        case 2 || 5 || 8:
            $tens = 18;
            break;
        case 3 || 6 || 9:
            $tens = 36;
            break;
    }
    return $tens + $ones;
}

function longCoords($items)
{
    $temps;
    foreach ($items as $item) {
        $x = longX($item['coords_x'], $item['coords_q'];
        $y = longY($item['coords_y'], $item['coords_q']);
        $temps[$y] = $temps[$y] ?: [];
        $temps[$y][] = $x;
    }
    return $temps;
}

function visualiseGame($spaceShip, $enemies, $metoers, $tanks)
{
    $spaceShipX = longX($spaceShip['coords_x'], $spaceShip['coords_q']);
    $spaceShipY = longY($spaceShip['coords_y'], $spaceShip['coords_q']);

    $enemies = longCoords($enemies);
    $metoers = longCoords($metoers);
    $tanks = longCoords($tanks);

    $row_0_10_20_30 = "+= = = = = = = = =+= = = = = = = = =+= = = = = = = = =+\n";
    $row_others     = "|                 |                 |                 |\n";

    $board = "";
    for (int $i = 0; $i < 31; $i++) {
        switch ($i) {
            case 0 || 10 || 20 || 30:
                $row = $row_0_10_20_30;
            default:
                $row = $row_others;
        }

        if ($spaceShipY = $i)
            $row = substr_replace($row, 'ᐂ', $spaceShipX, 1);

        if ($enemies[$i]) {
            foreach ($enemies[$i] as $value)
                $row = substr_replace($row, 'Ϙ', $value, 1);
        }

        if ($metoers[$i]) {
            foreach ($metoers[$i] as $value)
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