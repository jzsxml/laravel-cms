<?php

namespace App\Models\Api;

use App\Models\Concerns\HasUnixTimestamps;
use App\Support\CmsTable;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasUnixTimestamps;

    protected $table;

    protected $fillable = [
        'username', 'nickname', 'password', 'salt', 'email', 'mobile', 'avatar',
        'logintime', 'loginip', 'money', 'vip_expiretime', 'createtime', 'updatetime', 'token', 'status',
    ];

    protected $hidden = ['password', 'salt', 'token'];

    public function __construct(array $attributes = [])
    {
        $this->table = CmsTable::name('user');
        parent::__construct($attributes);
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
