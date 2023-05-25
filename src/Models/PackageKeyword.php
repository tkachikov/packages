<?php
declare(strict_types=1);

namespace Tkachikov\Packages\Models;

use Illuminate\Database\Eloquent\Model;

class PackageKeyword extends Model
{
    public $timestamps = false;

    protected $table = 'package_keyword';

    protected $fillable = [
        'package_id',
        'keyword_id',
    ];
}
