<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Recipe extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'category_id', 'instructions'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'recipe_ingredients')
            ->withPivot('quantity', 'note')
            ->withTimestamps();
    }
    public function getIngredientsForRepeaterAttribute()
    {
        $data = $this->ingredients->map(function ($ingredient) {
            return [
                'ingredient_id' => $ingredient->id,
                'quantity' => $ingredient->pivot->quantity,
                'note' => $ingredient->pivot->note,
            ];
        });

        // Log the ingredients for debugging
        Log::info('Ingredients for Repeater:', $data->toArray());

        return $data;
    }
}
