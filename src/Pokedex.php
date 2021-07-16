<?php

/*
 * This file is part of the basic-pokeapi package.
 *
 * (c) Jimmy Aubert
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hb\BasicPokeapi;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class Pokedex
 *
 * @author Jimmy Aubert
 */
class Pokedex
{
    private HttpClientInterface $client;

    public function __construct()
    {
        $this->client = HttpClient::createForBaseUri('https://pokeapi.co/api/v2/');
    }

    public function getPokemon(int $id): array
    {
        $response = $this->client->request('GET', 'pokemon/'.$id);

        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException('Error from Pokeapi.co');
        }

        $data = $response->toArray();

        $clean = array_intersect_key($data, array_flip(['id', 'name', 'weight', 'base_experience']));
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

    public function getPikachu(): array
    {
        $response = $this->client->request('GET', 'pokemon/25');

        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException('Error from Pokeapi.co');
        }

        return $response->toArray();


//        return [
//            'name' => 'Pikachu',
//            'id' => 25,
//            'types' => [
//                'electric',
//            ],
//        ];
    }

    public function getAllPokemon(int $offset = 0): array
    {
        $response = $this->client->request('GET', 'pokemon', [
            'query' => [
                'offset' => $offset,
            ],
        ]);

        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException('Error from Pokeapi.co');
        }

        $data = $response->toArray();

        $pokemons = [];
        foreach ($data['results'] as $pokemon) {
            if (!preg_match('/([0-9]+)\/?$/', $pokemon['url'], $matches)) {
                throw new \RuntimeException('Cannot match given url for pokemon ' . $pokemon['name']);
            }

            $id = $matches[1]; //  => 25

            $pokemons[] = [
                'id' => $id,
                'name' => $pokemon['name'],
            ];
        }

        // next page
        if ($data['next']) {
            if (!preg_match('/\?.*offset=([0-9]+)/', $data['next'], $matches)) {
                throw new \RuntimeException('Cannot match offset on next page.');
            }

            $nextOffset = $matches[1];

            $nextPokemons = $this->getAllPokemon($nextOffset);

            $pokemons = array_merge($pokemons, $nextPokemons);
        }

        return $pokemons;
    }
}
