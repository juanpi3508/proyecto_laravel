<?php

namespace App\Models;

use App\Constants\CategoryColumns as CatCol;
use App\Constants\ProductColumns as ProdCol;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = CatCol::TABLE;
    protected $primaryKey = CatCol::PK;

    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $casts = [
        CatCol::PK => 'string',
    ];

    protected $fillable = [
        CatCol::PK,
        CatCol::DESCRIPCION,
    ];

    public function productos()
    {
        return $this->hasMany(
            Product::class,
            ProdCol::CATEGORIA_ID,
            CatCol::PK
        );
    }

    public static function paraCatalogo()
    {
        return static::orderBy(CatCol::DESCRIPCION)->get();
    }
}
