<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gh_accounts extends Model
{
    use HasFactory;
// Gh_accountで編集できる値はuser_idとgh_account_idのみ
    protected $guarded=[
        'id',
        'created_at',
        'updated_at',
    ];
}
