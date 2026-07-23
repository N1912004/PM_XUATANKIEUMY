<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IngredientType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class, 'ingredient_type_id');
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'ingredient_type_supplier');
    }
}
