<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kitchen extends Model
{
    protected $fillable = [
        'area_id',
        'name',
        'type',
        'capacity',
        'manager_id',
        'status',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }
}
