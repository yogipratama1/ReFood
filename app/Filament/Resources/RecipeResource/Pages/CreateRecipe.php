<?php

namespace App\Filament\Resources\RecipeResource\Pages;

use App\Filament\Resources\RecipeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateRecipe extends CreateRecord
{
    protected static string $resource = RecipeResource::class;

    protected function afterSave(): void
    {
        $ingredients = $this->data['ingredients'] ?? [];

        $syncData = collect($ingredients)->mapWithKeys(function ($ingredient) {
            return [
                $ingredient['ingredient_id'] => [
                    'quantity' => $ingredient['quantity'] ?? null,
                    'note' => $ingredient['note'] ?? null,
                ],
            ];
        });

        // Sync the ingredients with the recipe
        $this->record->ingredients()->sync($syncData);
    }
}
