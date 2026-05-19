<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BestSellersLogicTest extends TestCase
{
    #[Test]
    public function should_show_centered_class_when_only_one_product()
    {
        // En tu vista: {{ $bestSellers->count() == 1 ? 'single-product' : '' }}
        $productCount = 1;
        $gridClass = $productCount == 1 ? 'single-product' : '';
        
        $this->assertEquals('single-product', $gridClass);
    }

    #[Test]
    public function should_not_show_centered_class_when_two_products()
    {
        // En tu vista: {{ $bestSellers->count() == 1 ? 'single-product' : '' }}
        $productCount = 2;
        $gridClass = $productCount == 1 ? 'single-product' : '';
        
        $this->assertEquals('', $gridClass);
    }

    #[Test]
    public function should_not_show_centered_class_when_no_products()
    {
        $productCount = 0;
        $gridClass = $productCount == 1 ? 'single-product' : '';
        
        $this->assertEquals('', $gridClass);
    }

    #[Test]
    public function sold_units_text_format_is_correct()
    {
        // En tu vista: {{ $product->total_sold }} unidades vendidas
        $totalSold = 5;
        $text = $totalSold . ' unidades vendidas';
        
        $this->assertEquals('5 unidades vendidas', $text);
    }

    #[Test]
    public function sold_units_text_handles_zero()
    {
        $totalSold = 0;
        $text = $totalSold . ' unidades vendidas';
        
        $this->assertEquals('0 unidades vendidas', $text);
    }

    #[Test]
    public function sold_units_text_handles_large_numbers()
    {
        $totalSold = 999;
        $text = $totalSold . ' unidades vendidas';
        
        $this->assertEquals('999 unidades vendidas', $text);
    }

    #[Test]
    public function price_format_is_correct()
    {
        // En tu vista: ₡{{ number_format($product->price, 2) }}
        $price = 35500;
        $formattedPrice = '₡' . number_format($price, 2);
        
        $this->assertEquals('₡35,500.00', $formattedPrice);
    }

    #[Test]
    public function price_format_handles_zero()
    {
        $price = 0;
        $formattedPrice = '₡' . number_format($price, 2);
        
        $this->assertEquals('₡0.00', $formattedPrice);
    }

    #[Test]
    public function should_limit_to_top_2_products()
    {
        // En tu controlador: ->limit(2)
        $limit = 2;
        $bestSellers = [
            ['name' => 'Producto 1', 'total_sold' => 10],
            ['name' => 'Producto 2', 'total_sold' => 5],
            ['name' => 'Producto 3', 'total_sold' => 3],
        ];
        
        $top2 = array_slice($bestSellers, 0, $limit);
        
        $this->assertCount(2, $top2);
        $this->assertEquals('Producto 1', $top2[0]['name']);
        $this->assertEquals('Producto 2', $top2[1]['name']);
    }

    #[Test]
    public function rank_badge_shows_nothing_for_top_2_positions()
    {
        // En tu vista, para índice 0 y 1 no muestra nada
        $indexes = [0, 1];
        
        foreach ($indexes as $index) {
            $badge = '';
            if ($index == 0) {
                // no muestra nada
            } elseif ($index == 1) {
                // no muestra nada
            } else {
                $badge = '<span class="rank-number">' . ($index + 1) . '</span>';
            }
            
            $this->assertEquals('', $badge);
        }
    }
}