<?php declare(strict_types=1);

use Kambo\Benchmark\AbstractBench;

use PhpBench\Attributes as Bench;

/**
 * @Iterations(100)
 * @Revs(100)
 * @Warmup(100)
 * @OutputTimeUnit("microseconds", precision=5)
 */
class ArrayFindPurePHPBench extends AbstractBench
{
    #[Bench\ParamProviders(['provideData'])]
    public function benchMin($params): mixed
    {
        $animals = $params['100 elements'];
        return custom_array_find(
            $animals,
            static fn(string $value): bool => str_starts_with($value, 'cow')
        );
    }

    #[Bench\ParamProviders(['provideData'])]
    public function benchMiddle($params): mixed
    {
        $animals = $params['10,000 elements'];
        return custom_array_find(
            $animals,
            static fn(string $value): bool => str_starts_with($value, 'cow')
        );
    }

    #[Bench\ParamProviders(['provideData'])]
    public function benchMax($params): mixed
    {
        $animals = $params['100,000 elements'];
        return custom_array_find(
            $animals,
            static fn(string $value): bool => str_starts_with($value, 'cow')
        );
    }
}

function custom_array_find(array $array, callable $callback): mixed
{
    foreach ($array as $value) {
        if ($callback($value)) {
            return $value;
        }
    }
    return null;
}
