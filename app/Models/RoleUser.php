<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use OwenIt\Auditing\Contracts\Auditable;

class RoleUser extends Pivot implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'role_user';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'user_name',
        'role_name',
    ];
}
