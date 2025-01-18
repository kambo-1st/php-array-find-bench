<?php declare(strict_types=1);

use Kambo\Benchmark\AbstractBench;

use PhpBench\Attributes as Bench;

/**
 * @Iterations(100)
 * @Revs(100)
 * @Warmup(100)
 * @OutputTimeUnit("microseconds", precision=5)
 */
class ForeachBench extends AbstractBench
{
    #[Bench\ParamProviders(['provideData'])]
    public function benchMin($params): mixed
    {
        $animal = null;
        $animals = $params['100 elements'];
        foreach ($animals as $value) {
            if (str_starts_with($value, 'cow')) {
                $animal = $value;
                break;
            }
        }

        return $animal;
    }

    #[Bench\ParamProviders(['provideData'])]
    public function benchMiddle($params): mixed
    {
        $animal = null;
        $animals = $params['10,000 elements'];
        foreach ($animals as $value) {
            if (str_starts_with($value, 'cow')) {
                $animal = $value;
                break;
            }
        }

        return $animal;
    }

    #[Bench\ParamProviders(['provideData'])]
    public function benchMax($params): mixed
    {
        $animal = null;
        $animals = $params['100,000 elements'];
        foreach ($animals as $value) {
            if (str_starts_with($value, 'cow')) {
                $animal = $value;
                break;
            }
        }

        return $animal;
    }
}
