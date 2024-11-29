<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecipeResource\Pages;
use App\Filament\Resources\RecipeResource\RelationManagers;
use App\Models\Ingredient;
use App\Models\Recipe;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\Action;

class RecipeResource extends Resource
{
    protected static ?string $model = Recipe::class;

    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(100),

                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),

                Forms\Components\Textarea::make('instructions')
                    ->rows(8)
                    ->required(),

                Forms\Components\Repeater::make('ingredients')
                    ->schema([
                        Forms\Components\Select::make('ingredient_id')
                            ->label('Ingredient')
                            ->options(Ingredient::query()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),

                        Forms\Components\TextInput::make('quantity')
                            ->numeric()
                            ->label('Quantity'),

                        Forms\Components\TextInput::make('note')
                            ->label('Note'),
                    ])
                    ->columns(3)
                    ->required()
                    ->hidden(fn($livewire) => $livewire instanceof \App\Filament\Resources\RecipeResource\Pages\CreateRecipe)
                    ->afterStateHydrated(function ($component, $record) {

                        if ($record) {
                            $component->state(
                                $record->ingredients->map(function ($ingredient) {
                                    return [
                                        'ingredient_id' => $ingredient->id,
                                        'quantity' => $ingredient->pivot->quantity,
                                        'note' => $ingredient->pivot->note,
                                    ];
                                })->toArray()
                            );
                        }
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('category.name')->sortable()->label('Category'),

            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Recipe Details')
                    ->modalButton('Close')
                    ->modalWidth('lg')
                    ->modalSubheading('Details of the selected recipe.')
                    ->action(function ($record) {})
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->disabled()
                            ->default(fn($record) => $record->name),

                        Forms\Components\TextInput::make('category_name')
                            ->label('Category')
                            ->disabled()
                            ->default(fn($record) => $record->category->name),

                        Forms\Components\Textarea::make('instructions')
                            ->label('Instructions')
                            ->rows(8)
                            ->disabled()
                            ->default(fn($record) => $record->instructions),

                        Forms\Components\Textarea::make('ingredients_text')
                            ->label('Ingredients')
                            ->hint('Ingredient Name - Quantity - Note')
                            ->disabled()
                            ->afterStateHydrated(function ($component, $record) {

                                if ($record) {
                                    $component->state(
                                        $record->ingredients->map(function ($ingredient) {
                                            return $ingredient->name . ' - ' . $ingredient->pivot->quantity . ' - ' . $ingredient->pivot->note;
                                        })->implode("\n")
                                    );
                                }
                            })
                            ->afterStateUpdated(function ($state, $component) {

                                $ingredients = collect(explode("\n", $state))->map(function ($line) {
                                    [$name, $quantity, $note] = array_map('trim', explode('-', $line, 3));
                                    return [
                                        'name' => $name,
                                        'quantity' => $quantity,
                                        'note' => $note ?? '',
                                    ];
                                });
                            }),

                    ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecipes::route('/'),
            'create' => Pages\CreateRecipe::route('/create'),
            'edit' => Pages\EditRecipe::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
