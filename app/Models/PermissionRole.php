<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use OwenIt\Auditing\Contracts\Auditable;

class PermissionRole extends Pivot implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'permission_role';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'role_name',
        'permission_name',
    ];
}
