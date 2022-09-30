<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    public function products()    // plural because there will be many products
    {
        return $this->hasMany(Product::class);
    }


    
}
