<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    protected $fillable = [
        'client_id','supervisor_id','tecnico_id','scheduled_at',
        'check_in_at','check_in_lat','check_in_lng',
        'check_out_at','check_out_lat','check_out_lng',
        'notes'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'check_in_at'  => 'datetime',
        'check_out_at' => 'datetime',
    ];

    public function client()     { return $this->belongsTo(Client::class); }
    public function supervisor() { return $this->belongsTo(User::class, 'supervisor_id'); }
    public function tecnico()    { return $this->belongsTo(User::class, 'tecnico_id'); }
}
