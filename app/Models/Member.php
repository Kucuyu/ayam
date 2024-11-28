<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    protected $table = 'member';
    protected $primaryKey = 'id_member';
    protected $guarded = [];

    // Accessor untuk status berdasarkan poin
    public function getStatusAttribute()
    {
        if ($this->poin >= 6000) {
            return 'Platinum';
        } elseif ($this->poin >= 4000) {
            return 'Gold';
        } elseif ($this->poin >= 2000) {
            return 'Bronze';
        } else {
            return 'Regular';
        }
    }
}
