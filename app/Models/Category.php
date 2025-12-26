<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    // Nombre real de la tabla
    protected $table = 'categorias';

    // Primary key real
    protected $primaryKey = 'id_categoria';

    // La tabla no tiene timestamps (created_at, updated_at)
    public $timestamps = false;

    // Campos modificables
    protected $fillable = [
        'nombre',
        'estado'
    ];

    // Relación: Una categoría tiene muchos productos
    public function productos()
    {
        return $this->hasMany(Product::class, 'id_categoria');
    }
}
