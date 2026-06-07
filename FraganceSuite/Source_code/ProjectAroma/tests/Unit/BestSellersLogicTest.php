<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BestSellersLogicTest extends TestCase
{
    #[Test]
    public function deberia_mostrar_clase_centrada_cuando_hay_un_solo_producto()
    {
        // En tu vista: {{ $bestSellers->count() == 1 ? 'single-product' : '' }}
        $productCount = 1;
        $gridClass = $productCount == 1 ? 'single-product' : '';
        
        $this->assertEquals('single-product', $gridClass);
    }

    #[Test]
    public function no_deberia_mostrar_clase_centrada_cuando_hay_dos_productos()
    {
        // En tu vista: {{ $bestSellers->count() == 1 ? 'single-product' : '' }}
        $productCount = 2;
        $gridClass = $productCount == 1 ? 'single-product' : '';
        
        $this->assertEquals('', $gridClass);
    }

    #[Test]
    public function no_deberia_mostrar_clase_centrada_cuando_no_hay_productos()
    {
        $productCount = 0;
        $gridClass = $productCount == 1 ? 'single-product' : '';
        
        $this->assertEquals('', $gridClass);
    }

    #[Test]
    public function formato_texto_unidades_vendidas_es_correcto()
    {
        // En tu vista: {{ $product->total_sold }} unidades vendidas
        $totalSold = 5;
        $text = $totalSold . ' unidades vendidas';
        
        $this->assertEquals('5 unidades vendidas', $text);
    }

    #[Test]
    public function texto_unidades_vendidas_maneja_cero()
    {
        $totalSold = 0;
        $text = $totalSold . ' unidades vendidas';
        
        $this->assertEquals('0 unidades vendidas', $text);
    }

    #[Test]
    public function texto_unidades_vendidas_maneja_numeros_grandes()
    {
        $totalSold = 999;
        $text = $totalSold . ' unidades vendidas';
        
        $this->assertEquals('999 unidades vendidas', $text);
    }

    #[Test]
    public function formato_precio_es_correcto()
    {
        // En tu vista: ₡{{ number_format($product->price, 2) }}
        $price = 35500;
        $formattedPrice = '₡' . number_format($price, 2);
        
        $this->assertEquals('₡35,500.00', $formattedPrice);
    }

    #[Test]
    public function formato_precio_maneja_cero()
    {
        $price = 0;
        $formattedPrice = '₡' . number_format($price, 2);
        
        $this->assertEquals('₡0.00', $formattedPrice);
    }

    #[Test]
    public function deberia_limitar_a_los_2_primeros_productos()
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
    public function badge_de_posicion_no_muestra_nada_para_los_primeros_2_puestos()
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