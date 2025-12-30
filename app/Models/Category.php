<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    // Nombre real de la tabla
    protected $table = 'categorias';

    // Primary key real (CHAR(3))
    protected $primaryKey = 'id_categoria';

    // PK NO es autoincremental (porque es CHAR)
    public $incrementing = false;

    // Tipo de la PK
    protected $keyType = 'string';

    // La tabla no tiene timestamps (created_at, updated_at)
    public $timestamps = false;

    // Casts para evitar character vs integer en Postgres
    protected $casts = [
        'id_categoria' => 'string',
    ];

    // Relación: Una categoría tiene muchos productos
    public function productos()
    {
        return $this->hasMany(
            Product::class,
            'id_categoria',   // FK en products
            'id_categoria'    // PK en categorias
        );
    }
}
