<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ingredient extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'unit'];

    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'recipe_ingredients')
            ->withPivot('quantity', 'note')
            ->withTimestamps();
    }
    public function recipeIngredients()
    {
        return $this->hasMany(RecipeIngredient::class);
    }
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($record) {
            if ($record->recipeIngredients()->exists()) {

                throw new \Exception('Tidak bisa menghapus bahan. Bahan ini sudah digunakan di resep.');
            }
        });
    }
}
