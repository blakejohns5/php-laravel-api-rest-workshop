<?php

namespace Tests\Unit;

use App\Services\ProductPriceService;
use Exception;
use PHPUnit\Framework\TestCase;

class ProductPriceTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_price_is_correctly_formatted()
    {
        $formatter = new ProductPriceService();

        $this->assertEquals('1,00 €', $formatter->format(100));
        $this->assertEquals('0,01 €', $formatter->format(1));
        $this->assertEquals('1,05 €', $formatter->format(105));
        $this->assertEquals('1.050,00 €', $formatter->format(105000));
        $this->assertEquals('-1.000,00 €', $formatter->format(-100000));
    }

    public function test_negative_prices_are_not_allowed()
    {
        $this->expectException(Exception::class);  // matches to exception in the class: 'Price cannot be negative'

        $formatter = new ProductPriceService();
        $formatter->format(-100);
    }
}
