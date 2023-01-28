<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repositories extends Model
{
    use HasFactory;
    // Repositoriesで編集できる値はid,gh_account_id,repos_name,owner_id,owner_name,Create_dateのみ
    protected $guarded=[
        'created_at',
        'updated_at',
    ];
}
