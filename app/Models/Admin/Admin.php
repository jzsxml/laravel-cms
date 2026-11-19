<?php

namespace App\Models\Admin;

use App\Models\Concerns\HasUnixTimestamps;
use App\Support\CmsTable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Admin extends Model
{
    use HasUnixTimestamps;

    protected $table;

    protected $fillable = [
        'username', 'nickname', 'password', 'salt', 'avatar', 'email', 'mobile',
        'loginfailure', 'logintime', 'loginip', 'createtime', 'updatetime', 'token', 'status',
    ];

    protected $hidden = ['password', 'salt', 'token'];

    public function __construct(array $attributes = [])
    {
        $this->table = CmsTable::name('admin');
        parent::__construct($attributes);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(
            AuthGroup::class,
            CmsTable::name('auth_group_access'),
            'uid',
            'group_id'
        );
    }

    public static function encryptPassword(string $password, string $salt): string
    {
        return md5(md5($password).$salt);
    }

    public function verifyPassword(string $password): bool
    {
        return hash_equals($this->password, self::encryptPassword($password, $this->salt));
    }
}
