<?php

namespace App\Models;

use App\QueryBuilders\MemberQueryBuilder;
use App\Traits\HasIdentifier;
use App\Traits\HasSchemaAccessors;
use App\Traits\HandlesMariaDBBigIntegers;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\File;
use Kra8\Snowflake\HasSnowflakePrimary;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Class Member
 *
 * Represents a Member in the application.
 */
class Member extends Authenticatable implements HasLocalePreference, HasMedia
{
    use HasApiTokens, Notifiable, InteractsWithMedia, HasIdentifier, HasSnowflakePrimary, HandlesMariaDBBigIntegers, HasSchemaAccessors;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'members';

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'string',
        'affiliate_id' => 'string',
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'meta' => 'array',
        'created_at' => 'datetime',
        'created_by' => 'string',
        'created_at' => 'datetime',
        'created_by' => 'string',
        'deleted_at' => 'datetime',
        'deleted_by' => 'string',
    ];

    /**
     * The attributes that should not be exposed by API and other public responses.
     *
     * @var array
     */
    protected $hiddenForPublic = [
        'affiliate_id',
        'role',
        'member_number',
        'display_name',
        'birthday',
        'gender',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'account_expires_at',
        'premium_expires_at',
        'country_code',
        'phone_prefix',
        'phone_country',
        'phone',
        'phone_e164',
        'is_vip',
        'is_active',
        'accepts_text_messages',
        'is_undeletable',
        'is_uneditable',
        'number_of_emails_received',
        'number_of_text_messages_received',
        'number_of_reviews_written',
        'number_of_ratings_given',
        'meta',
        'media',
        'deleted_at',
        'deleted_by',
        'created_by',
        'updated_by'
    ];

    public function hideForPublic() 
    {
        $this->makeHidden($this->hiddenForPublic);
    
        return $this;
    }

    /**
     * Allow mass assignment of a model.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Append programmatically added columns.
     *
     * @var array
     */
    protected $appends = [
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function newEloquentBuilder($query)
    {
        return new MemberQueryBuilder($query);
    }

    /**
     * Get the user's preferred locale.
     *
     * @return string
     */
    public function preferredLocale()
    {
        $locale = $this->locale;
        $defaultLocale = config('app.locale');

        return File::exists(lang_path().'/'.$locale) ? $locale : $defaultLocale;
    }

    /**
     * Check if user has a specific role.
     *
     * @param  array|string  $roles
     */
    public function hasRole($roles): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];

        return in_array($this->role, $roles);
    }

    /**
     * Register media collections for the model.
     */
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('avatar')
            ->singleFile()
            ->registerMediaConversions(function (Media $media) {
                // First conversion: small
                $this
                    ->addMediaConversion('small')
                    ->fit(Manipulations::FIT_MAX, 80, 80)
                    ->keepOriginalImageFormat();

                // Second conversion: medium
                $this
                    ->addMediaConversion('medium')
                    ->fit(Manipulations::FIT_MAX, 320, 320)
                    ->keepOriginalImageFormat();
            });
    }

    /**
     * Retrieve the value of an attribute or a dynamically generated image URL.
     *
     * @param  string  $key The attribute key or the image key with a specific conversion.
     * @return mixed The value of the attribute or the image conversion URL.
     *
     * @throws \Illuminate\Database\Eloquent\RelationNotFoundException If the relationship is not found.
     */
    public function __get($key)
    {
        if (substr($key, 0, 7) === 'avatar-') {
            return $this->getAvatarUrl(substr($key, 7, strlen($key)));
        }

        return parent::__get($key);
    }

    /**
     * Get the avatar URL.
     *
     * @return string|null
     */
    public function getAvatarAttribute()
    {
        return $this->getAvatarUrl();
    }

    /**
     * Get the avatar URL with a specific conversion.
     *
     * @param  string|null  $conversion
     * @return string|null
     */
    public function getAvatarUrl($conversion = '')
    {
        if ($this->getFirstMediaUrl('avatar') !== '') {
            $media = $this->getMedia('avatar');

            // Get the resized image URL with the specified conversion
            return $media[0]->getFullUrl($conversion);
        } else {
            return null;
        }
    }

    /**
     * Get the cards associated with the member.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cards()
    {
        return $this->belongsToMany(Card::class, 'card_member')->withTimestamps();
    }

    /**
     * Get the transactions associated with the member.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
