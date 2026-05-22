<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\InfoCarousel;

class InfoCarouselTest extends TestCase
{
    /** @test */
    public function los_campos_permitidos_para_guardar_en_masa_son_correctos()
    {
        $camposPermitidos = [
            'message',
            'link',
            'link_text',
            'order_position',
            'active'
        ];

        $modelo = new InfoCarousel();
        
        $this->assertEquals($camposPermitidos, $modelo->getFillable());
    }

    /** @test */
    public function los_campos_tienen_el_tipo_de_dato_correcto()
    {
        $modelo = new InfoCarousel();
        $tiposDeDatos = $modelo->getCasts();

        $this->assertArrayHasKey('active', $tiposDeDatos);
        $this->assertEquals('boolean', $tiposDeDatos['active']);
        
        $this->assertArrayHasKey('order_position', $tiposDeDatos);
        $this->assertEquals('integer', $tiposDeDatos['order_position']);
    }

    /** @test */
    public function el_modelo_usa_la_tabla_correcta_en_base_de_datos()
    {
        $modelo = new InfoCarousel();
        
        $this->assertEquals('info_carousel', $modelo->getTable());
    }

    /** @test */
    public function el_modelo_hereda_de_la_clase_base_de_eloquent()
    {
        $modelo = new InfoCarousel();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Model::class, $modelo);
    }

    /** @test */
    public function la_clase_del_modelo_existe()
    {
        $this->assertTrue(class_exists(\App\Models\InfoCarousel::class));
    }

    /** @test */
    public function existe_el_metodo_para_obtener_solo_mensajes_activos()
    {
        $this->assertTrue(method_exists(InfoCarousel::class, 'getActiveItems'));
    }

    /** @test */
    public function el_modelo_tiene_los_campos_correctos_para_guardar()
    {
        $modelo = new InfoCarousel();
        
        $this->assertContains('message', $modelo->getFillable());
        $this->assertContains('link', $modelo->getFillable());
        $this->assertContains('link_text', $modelo->getFillable());
        $this->assertContains('order_position', $modelo->getFillable());
        $this->assertContains('active', $modelo->getFillable());
    }

    /** @test */
    public function el_modelo_usa_fechas_automaticas_de_creacion_y_actualizacion()
    {
        $modelo = new InfoCarousel();
        
        $this->assertTrue($modelo->usesTimestamps());
    }
}