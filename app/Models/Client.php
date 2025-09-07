<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'name','contact_name','email','phone','address','lat','lng'
    ];

    // Atributo calculado para link directo a Google Maps
    protected $appends = ['maps_url'];

    public function getMapsUrlAttribute(): ?string
    {
        if (is_null($this->lat) || is_null($this->lng)) return null;
        return "https://www.google.com/maps/dir/?api=1&destination={$this->lat},{$this->lng}";
    }
}
