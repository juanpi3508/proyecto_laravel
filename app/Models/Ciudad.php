<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ciudad extends Model
{
    protected $table = 'ciudades';
    protected $primaryKey = 'id_ciudad';

    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_ciudad',
        'ciu_descripcion',
    ];

    /**
     * Obtiene las ciudades ordenadas por descripción para selectores.
     */
    public static function paraSelector(): \Illuminate\Support\Collection
    {
        return static::orderBy('ciu_descripcion')->get();
    }
}
