<?php

namespace App\Models;

use App\Traits\HasSchemaAccessors;
use App\Traits\HandlesMariaDBBigIntegers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasSnowflakePrimary;

/**
 * Class Network
 *
 * Represents a Network in the application.
 */
class Network extends Model
{
    use HasFactory, HasSnowflakePrimary, HandlesMariaDBBigIntegers, HasSchemaAccessors;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'networks';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'string',
        'meta' => 'array',
        'created_at' => 'datetime',
        'created_by' => 'string',
        'created_at' => 'datetime',
        'created_by' => 'string',
        'deleted_at' => 'datetime',
        'deleted_by' => 'string',
    ];

    /**
     * Allow mass assignment of a model.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Get the admins associated with the network.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function admins()
    {
        return $this->belongsToMany(Admin::class, 'admin_network');
    }

    /**
     * Get the partners associated with the network.
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function partners()
    {
        return $this->hasMany(Partner::class);
    }

    /**
     * Get the clubs associated with the network.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function clubs()
    {
        return $this->hasMany(Club::class);
    }

    /**
     * Get the affiliates associated with the network.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function affiliates()
    {
        return $this->hasMany(Affiliate::class);
    }
}
