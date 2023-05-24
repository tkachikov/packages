<?php
declare(strict_types=1);

namespace Tkachikov\Packages\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'vendor',
        'name',
        'info',
    ];

    protected $casts = [
        'info' => 'array',
    ];

    /**
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return "$this->vendor/$this->name";
    }
}
