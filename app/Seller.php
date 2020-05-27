<?php

namespace App;

use App\Scopes\SellerGlobalScope;
use App\Transformers\SellerTransformer;
use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    protected $table = 'users';

    public $transformer = SellerTransformer::class;

    protected static function boot() 
    {
        parent::boot();
        static::addGlobalScope(new SellerGlobalScope);
    }

    public function products() {
        return $this->hasMany(Product::class);
    }
}
