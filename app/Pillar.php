<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pillar extends Model
{
    public function planXS()
    {
        return $this->hasOne('App\Plan')->where('size', 'XS');
    }

    public function planS()
    {
        return $this->hasOne('App\Plan')->where('size', 'S');
    }

    public function planM()
    {
        return $this->hasOne('App\Plan')->where('size', 'M');
    }

    public function planL()
    {
        return $this->hasOne('App\Plan')->where('size', 'L');
    }

    public function planXL()
    {
        return $this->hasOne('App\Plan')->where('size', 'XL');
    }
}
