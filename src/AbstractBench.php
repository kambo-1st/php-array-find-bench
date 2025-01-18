<?php

namespace Kambo\Benchmark;
use Generator;

/**
 * @Groups("cosine_similarity")
 */
abstract class AbstractBench
{
    private array $dataSets = [];

    public function __construct()
    {
        // Pregenerate datasets of different sizes
        $baseAnimals = ['dog', 'cat', 'horse', 'duck', 'goose'];
        $this->dataSets = [
            '100 elements' => $this->generateAnimalArray($baseAnimals, 100, 'cow99'),
            '10,000 elements' => $this->generateAnimalArray($baseAnimals, 10000, 'cow9999'),
            '100,000 elements' => $this->generateAnimalArray($baseAnimals, 100000, 'cow99999'),
        ];
    }

    /**
     * Provides the pregenerated datasets.
     */
    public function provideData(): Generator
    {
        yield $this->dataSets;
    }

    /**
     * Generate an array with a given number of elements, and ensure a specific match is present.
     */
    private function generateAnimalArray(array $baseAnimals, int $size, string $match): array
    {
        $animals = [];
        for ($i = 0; $i < $size; $i++) {
            $animals[] = $baseAnimals[array_rand($baseAnimals)] . $i;
        }
        $animals[$size - 1] = $match; // Ensure the target match is present.
        return $animals;
    }
}