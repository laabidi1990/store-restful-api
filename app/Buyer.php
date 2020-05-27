<?php

namespace App;

use App\Scopes\BuyerGlobalScope;
use App\Transformers\BuyerTransformer;
use Illuminate\Database\Eloquent\Model;

class Buyer extends Model
{
    protected $table = 'users';

    public $transformer = BuyerTransformer::class;

    protected static function boot() 
    {
        parent::boot();
        static::addGlobalScope(new BuyerGlobalScope);
    }

    public function transactions() 
    {
        return $this->hasMany(Transaction::class);
    }


}
