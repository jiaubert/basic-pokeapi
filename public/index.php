<?php
/*
 * This file is part of the basic-pokeapi package.
 *
 * (c) Benjamin Georgeault
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// Require the autoloader from Composer. Allow your page to use class from dependencies and src directory.
require_once __DIR__ . '/../vendor/autoload.php';

// Create new instance of Pokedex.
$pokedex = new \Hb\BasicPokeapi\Pokedex();

// Set the Content-Type HTTP header for the response to JSON type.
header('Content-Type: application/json');

if (!isset($_SERVER['PATH_INFO']) || '/pokemon' === $_SERVER['PATH_INFO']) {
    // Query data with Pokedex and convert to JSON to be send to the client.
    echo json_encode($pokedex->getAllPokemon());
} elseif ('/pikachu' === $_SERVER['PATH_INFO']) {
    echo json_encode($pokedex->getCleanPikachu());
} else {
    http_response_code(404);
    echo json_encode([
        'error' => 'Wrong path.',
    ]);
}
