<?php

namespace App\Filament\Resources\RecipeResource\Pages;

use App\Filament\Resources\RecipeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditRecipe extends EditRecord
{
    protected static string $resource = RecipeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
    protected function afterSave(): void
    {
        $ingredients = $this->data['ingredients'] ?? [];

        $syncData = collect($ingredients)->mapWithKeys(function ($ingredient) {
            return [
                $ingredient['ingredient_id'] => [
                    'quantity' => $ingredient['quantity'],
                    'note' => $ingredient['note'],
                ],
            ];
        });


        $this->record->ingredients()->sync($syncData);
    }
}
