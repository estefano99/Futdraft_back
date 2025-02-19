<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditoriaLog extends Model
{
    use HasFactory;

    protected $table = "auditoria_log";
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'evento',
        'timestamp'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
