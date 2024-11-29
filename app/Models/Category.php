<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'description'];

    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }
    protected static function boot()
    {
        parent::boot();
        static::deleting(function ($record) {

            if ($record->recipes()->exists()) {
                throw new \Exception('Tidak bisa menghapus kategori. Kategori ini sudah digunakan di resep.');
            }
        });
    }
}
