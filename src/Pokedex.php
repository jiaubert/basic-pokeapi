<?php

/*
 * This file is part of the basic-pokeapi package.
 *
 * (c) Benjamin Georgeault
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hb\BasicPokeapi;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class to get DATA from https://pokeapi.co/.
 *
 * @author Benjamin Georgeault
 */
class Pokedex
{
    /**
     * Attribute to save the built HttpClient to use it in all methods.
     */
    private HttpClientInterface $client;

    public function __construct()
    {
        // Build the HttpClient with base URL from https://pokeapi.co/.
        $this->client = HttpClient::createForBaseUri('https://pokeapi.co/api/v2/');
    }

    /**
     * Recursive method to retrieve all Pokemons in one call.
     *
     * @param int $offset
     * @return array
     */
    public function getAllPokemon(int $offset = 0, int $limit = 50): array
    {
        // Get pokemons from https://pokeapi.co/ by offset.
        $response = $this->client->request('GET', 'pokemon', [
            'query' => [
                'offset' => $offset,
                'limit' => $limit,
            ],
        ]);

        // If the response does not have 200 for status code, throw exception.
        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException('Error from Pokeapi.co');
        }

        // Return data from response as PHP Array. The method read JSON data and convert it to PHP array.
        $data = $response->toArray();

        // Init pokemons array.
        $pokemons = [];

        // Parse all pokemons return by https://pokeapi.co/ for the current HTTP request.
        foreach ($data['results'] as $pokemon) {
            // Try to match pokemon's id from the URL given. If no match, throw exception.
            if (!preg_match('/([0-9]+)\/?$/', $pokemon['url'], $matches)) {
                throw new \RuntimeException('Cannot match given url for pokemon ' . $pokemon['name']);
            }

            // Get id from matches. index 0 get the full match, next indexes (1, 2, etc…) get data surround by ()
            // in the regex.
            $id = $matches[1]; //  => 25

            // Add pokemon data to the pokemons array.
            $pokemons[] = [
                'id' => $id,
                'name' => $pokemon['name'],
            ];
        }

        // Check if a next page exist.
        if ($data['next']) {
            // Try to retrieve the offset value from next URL. If no match, throw exception.
            if (!preg_match('/\?.*offset=([0-9]+)/', $data['next'], $matches)) {
                throw new \RuntimeException('Cannot match offset on next page.');
            }

            // Get next offset.
            $nextOffset = $matches[1];

            // Recurive call to getAllPokemon with the new next offset.
            $nextPokemons = $this->getAllPokemon($nextOffset, $limit);

            // Merge current pokemons with the next pokemons.
            $pokemons = array_merge($pokemons, $nextPokemons);
        }

        return $pokemons;
    }

    /**
     * Retrieve data for Pikachu.
     *
     * @return array
     */
    public function getPikachu(): array
    {
        // Send the HTTP request to https://pokeapi.co/ for Pikachu.
        $response = $this->client->request('GET', 'pokemon/25');

        // If the response does not have 200 for status code, throw exception.
        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException('Error from Pokeapi.co');
        }

        // Return data from response as PHP Array. The method read JSON data and convert it to PHP array.
        return $response->toArray();
    }

    /**
     * Select parts of data for Pikachu.
     *
     * @return array
     */
    public function getCleanPikachu(): array
    {
        // Retrieve data from https://pokeapi.co/.
        $data = $this->getPikachu();

        // Filter some attribute only.
        $clean = array_intersect_key($data, array_flip(['id', 'name', 'weight', 'base_experience']));

        // Add image attribute from front_default sprites.
        $clean['image'] = $data['sprites']['front_default'];

        return $clean;

//        // same as lines before.
//        return [
//            'id' => $data['id'],
//            'name' => $data['name'],
//            'weight' => $data['weight'],
//            'base_experience' => $data['base_experience'],
//            'image' => $data['sprites']['front_default'],
//        ];
    }
}
