<?php

namespace App\Admin\Models;

use App\Models\HospitalType;
use App\Models\JLAccount;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Encore\Admin\Auth\Database\HasPermissions;

/**
 * Class Administrator.
 *
 * @property Role[] $roles
 */
class Administrator extends Model implements AuthenticatableContract
{
    use Authenticatable;
    use HasPermissions;
    use DefaultDatetimeFormat;

    private $_hospitalList;

    protected $fillable = ['username', 'password', 'name', 'avatar'];

    public function hospital()
    {
        return $this->belongsToMany(HospitalType::class, 'admin_user_has_hospital', 'admin_user_id', 'hospital_id');
    }

    public function account()
    {
        return $this->belongsToMany(JLAccount::class, 'account_link_user', 'account_id', 'user_id');
    }

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $connection = config('admin.database.connection') ?: config('database.default');

        $this->setConnection($connection);

        $this->setTable(config('admin.database.users_table'));

        parent::__construct($attributes);
    }

    public function getHospitalListAttribute()
    {
        if (!$this->_hospitalList) {
            $hospital            = $this->hospital();
            $this->_hospitalList = $hospital->exists() ? $hospital : HospitalType::all();

        }
        return $this->_hospitalList;
    }

    /**
     * Get avatar attribute.
     *
     * @param string $avatar
     *
     * @return string
     */
    public function getAvatarAttribute($avatar)
    {
        if (url()->isValidUrl($avatar)) {
            return $avatar;
        }

        $disk = config('admin.upload.disk');

        if ($avatar && array_key_exists($disk, config('filesystems.disks'))) {
            return Storage::disk(config('admin.upload.disk'))->url($avatar);
        }

        $default = config('admin.default_avatar') ?: '/vendor/laravel-admin/AdminLTE/dist/img/user2-160x160.jpg';

        return admin_asset($default);
    }

    /**
     * A user has and belongs to many roles.
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        $pivotTable = config('admin.database.role_users_table');

        $relatedModel = config('admin.database.roles_model');

        return $this->belongsToMany($relatedModel, $pivotTable, 'user_id', 'role_id');
    }

    /**
     * A User has and belongs to many permissions.
     *
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        $pivotTable = config('admin.database.user_permissions_table');

        $relatedModel = config('admin.database.permissions_model');

        return $this->belongsToMany($relatedModel, $pivotTable, 'user_id', 'permission_id');
    }
}
