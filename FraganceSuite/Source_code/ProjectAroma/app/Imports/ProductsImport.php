<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Product([
            'name'             => $row['name'] ?? null,
            'brand'            => $row['brand'] ?? null,
            'category'         => $row['categories'] ?? $row['category'] ?? null,
            'pathimg'          => $row['images'] ?? $row['pathimg'] ?? null,
            'price'            => isset($row['price']) ? (float) $row['price'] : 0,
            'stock'            => isset($row['inventory_bodega_san_jose']) ? (int) $row['inventory_bodega_san_jose'] : 0,
            'description'      => $row['description'] ?? null,
            'shortDescription' => $row['short_description'] ?? ($row['short_description'] ?? null),
            'active'           => $this->toBool($row['status'] ?? true),
            'decant'           => $this->toBool($row['decant'] ?? false),
        ]);
    }

    private function toBool($value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        $v = strtolower(trim((string) $value));
        return in_array($v, [
            '1','true','yes','si','sí',
            'active','activo',
            'publish','published',
            'enabled'
        ], true);
    }
}
