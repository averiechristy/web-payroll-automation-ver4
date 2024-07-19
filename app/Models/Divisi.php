<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Divisi extends Model
{
    use HasFactory;

    protected $fillable = [
        'divisi',
        'created_by',
        'updated_by',
    ];

    
    public function penempatan()
    {

        return $this->hasMany(Penempatan::class);
    }
}
