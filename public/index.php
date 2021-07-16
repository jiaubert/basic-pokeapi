<?php
/*
 * This file is part of the basic-pokeapi package.
 *
 * (c) Jimmy Aubert
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__ . '/../vendor/autoload.php';


$pokedex = new \Hb\BasicPokeapi\Pokedex();

header('Content-Type: application/json');

echo json_encode($pokedex->getAllPokemon());
