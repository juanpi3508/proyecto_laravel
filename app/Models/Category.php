<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categorias';
    protected $primaryKey = 'id_categoria';

    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $casts = [
        'id_categoria' => 'string',
    ];

    protected $fillable = [
        'id_categoria',
        'cat_descripcion',
    ];

    public function productos()
    {
        return $this->hasMany(
            Product::class,
            'id_categoria',
            'id_categoria'
        );
    }

    public static function paraCatalogo()
    {
        return static::orderBy('cat_descripcion')->get();
    }


}
