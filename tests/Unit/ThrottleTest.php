<?php

namespace StatamicRadPack\Shopify\Tests\Unit;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Shopify\Clients\Graphql;
use Shopify\Clients\HttpResponse;
use StatamicRadPack\Shopify\Tests\TestCase;
use StatamicRadPack\Shopify\Traits\ThrottlesShopifyRequests;

class ThrottleTest extends TestCase
{
    /**
     * Return a test class that uses the trait and captures sleep calls.
     */
    private function makeSubject(): object
    {
        return new class
        {
            use ThrottlesShopifyRequests;

            public int $sleptSeconds = 0;

            protected function throttleSleep(int $seconds): void
            {
                $this->sleptSeconds += $seconds;
            }

            public function run(Graphql $client, array $params): mixed
            {
                return $this->queryWithThrottle($client, $params);
            }
        };
    }

    #[Test]
    public function returns_response_when_no_throttle_info()
    {
        $subject = $this->makeSubject();

        $body = '{"data":{"product":null}}';
        $mock = $this->mock(Graphql::class, function (MockInterface $mock) use ($body) {
            $mock->shouldReceive('query')->once()->andReturn(new HttpResponse(status: 200, body: $body));
        });

        $response = $subject->run($mock, ['query' => '{}']);

        $this->assertSame(['data' => ['product' => null]], $response->getDecodedBody());
        $this->assertSame(0, $subject->sleptSeconds);
    }

    #[Test]
    public function does_not_sleep_when_available_points_are_above_threshold()
    {
        $subject = $this->makeSubject();

        $body = json_encode([
            'data' => [],
            'extensions' => [
                'cost' => [
                    'throttleStatus' => [
                        'currentlyAvailable' => 1500.0,
                        'restoreRate' => 100.0,
                        'maximumAvailable' => 2000.0,
                    ],
                ],
            ],
        ]);

        $mock = $this->mock(Graphql::class, function (MockInterface $mock) use ($body) {
            $mock->shouldReceive('query')->once()->andReturn(new HttpResponse(status: 200, body: $body));
        });

        $subject->run($mock, ['query' => '{}']);

        $this->assertSame(0, $subject->sleptSeconds);
    }

    #[Test]
    public function sleeps_when_available_points_drop_below_threshold()
    {
        $subject = $this->makeSubject();

        // 100 available, restoreRate 100/s → need to recover 400 points → 4s sleep
        $body = json_encode([
            'data' => [],
            'extensions' => [
                'cost' => [
                    'throttleStatus' => [
                        'currentlyAvailable' => 100.0,
                        'restoreRate' => 100.0,
                        'maximumAvailable' => 2000.0,
                    ],
                ],
            ],
        ]);

        $mock = $this->mock(Graphql::class, function (MockInterface $mock) use ($body) {
            $mock->shouldReceive('query')->once()->andReturn(new HttpResponse(status: 200, body: $body));
        });

        $subject->run($mock, ['query' => '{}']);

        $this->assertSame(4, $subject->sleptSeconds);
    }

    #[Test]
    public function sleeps_at_minimum_one_second()
    {
        $subject = $this->makeSubject();

        // 499 available — just under threshold, very high restore rate → ceil((1) / 1000) = 1s minimum
        $body = json_encode([
            'data' => [],
            'extensions' => [
                'cost' => [
                    'throttleStatus' => [
                        'currentlyAvailable' => 499.0,
                        'restoreRate' => 1000.0,
                        'maximumAvailable' => 2000.0,
                    ],
                ],
            ],
        ]);

        $mock = $this->mock(Graphql::class, function (MockInterface $mock) use ($body) {
            $mock->shouldReceive('query')->once()->andReturn(new HttpResponse(status: 200, body: $body));
        });

        $subject->run($mock, ['query' => '{}']);

        $this->assertSame(1, $subject->sleptSeconds);
    }
}
