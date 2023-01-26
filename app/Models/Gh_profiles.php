<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gh_profiles extends Model
{
    use HasFactory;
// Gh_profilesで編集できる値はid,acunt_id,access_tokenのみ
    protected $guarded=[
        'created_at',
        'updated_at',
    ];

}
