<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Issues extends Model
{
    use HasFactory;

    protected $table = 'issues';

    protected $guarded = ['created_at', 'updated_at'];

    // 主キーのカラム名指定
    protected $primaryKey = 'id';
    // オートインクリメント無効化
    public $incrementing = false;
    // データ型を指定h
    protected $KeyType = 'integer';
}
