<?php

namespace Database\Seeders;

use App\Models\Admin\Admin;
use App\Models\Admin\AuthGroup;
use App\Models\Admin\AuthRule;
use App\Models\Admin\Config;
use App\Support\CmsTable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CmsAdminSeeder extends Seeder
{
    public function run(): void
    {
        $now = time();

        $rules = [
            ['pid' => 0, 'name' => 'index/index', 'title' => '控制台', 'icon' => 'fa fa-dashboard', 'ismenu' => 1, 'weigh' => 999],
            ['pid' => 0, 'name' => 'auth', 'title' => '权限管理', 'icon' => 'fa fa-group', 'ismenu' => 1, 'weigh' => 100, 'type' => 'menu'],
            ['pid' => 0, 'name' => 'general', 'title' => '常规管理', 'icon' => 'fa fa-cogs', 'ismenu' => 1, 'weigh' => 90, 'type' => 'menu'],
        ];

        $ruleModels = [];
        foreach ($rules as $item) {
            $ruleModels[$item['name']] = AuthRule::query()->create([
                'type' => $item['type'] ?? 'file',
                'pid' => $item['pid'],
                'name' => $item['name'],
                'title' => $item['title'],
                'icon' => $item['icon'],
                'ismenu' => $item['ismenu'],
                'weigh' => $item['weigh'],
                'status' => 'normal',
                'createtime' => $now,
                'updatetime' => $now,
            ]);
        }

        $children = [
            ['parent' => 'auth', 'name' => 'auth/rule/index', 'title' => '菜单规则', 'icon' => 'fa fa-list'],
            ['parent' => 'auth', 'name' => 'auth/group/index', 'title' => '角色组', 'icon' => 'fa fa-users'],
            ['parent' => 'general', 'name' => 'general/config/index', 'title' => '系统配置', 'icon' => 'fa fa-cog'],
            ['parent' => 'general', 'name' => 'general/config/save', 'title' => '保存配置', 'icon' => '', 'ismenu' => 0],
            ['parent' => 'general', 'name' => 'general/attachment/index', 'title' => '附件管理', 'icon' => 'fa fa-file'],
            ['parent' => 'general', 'name' => 'general/adminlog/index', 'title' => '管理员日志', 'icon' => 'fa fa-history'],
            ['parent' => 'general', 'name' => 'ajax/upload', 'title' => '上传', 'icon' => '', 'ismenu' => 0],
        ];

        foreach ($children as $child) {
            AuthRule::query()->create([
                'type' => 'file',
                'pid' => $ruleModels[$child['parent']]->id,
                'name' => $child['name'],
                'title' => $child['title'],
                'icon' => $child['icon'],
                'ismenu' => $child['ismenu'] ?? 1,
                'weigh' => 0,
                'status' => 'normal',
                'createtime' => $now,
                'updatetime' => $now,
            ]);
        }

        $uploadRule = AuthRule::query()->where('name', 'ajax/upload')->first();
        if ($uploadRule) {
            foreach ([
                'ajax/uploadchunk' => '分片上传',
                'ajax/uploadmerge' => '合并上传',
            ] as $name => $title) {
                AuthRule::query()->firstOrCreate(
                    ['name' => $name],
                    [
                        'type' => 'file',
                        'pid' => $uploadRule->id,
                        'title' => $title,
                        'ismenu' => 0,
                        'status' => 'normal',
                        'createtime' => $now,
                        'updatetime' => $now,
                    ]
                );
            }
        }

        $group = AuthGroup::query()->create([
            'pid' => 0,
            'name' => '超级管理员',
            'rules' => '*',
            'status' => 'normal',
            'createtime' => $now,
            'updatetime' => $now,
        ]);

        $salt = bin2hex(random_bytes(8));
        $password = Admin::encryptPassword('123456', $salt);

        $admin = Admin::query()->create([
            'username' => 'admin',
            'nickname' => 'Admin',
            'password' => $password,
            'salt' => $salt,
            'email' => 'admin@example.com',
            'status' => 'normal',
            'createtime' => $now,
            'updatetime' => $now,
        ]);

        DB::table(CmsTable::name('auth_group_access'))->insert([
            'uid' => $admin->id,
            'group_id' => $group->id,
        ]);

        $configs = [
            ['name' => 'sitename', 'group' => 'basic', 'title' => '站点名称', 'type' => 'string', 'value' => 'Laravel CMS'],
            ['name' => 'cdnurl', 'group' => 'basic', 'title' => 'CDN 地址', 'type' => 'string', 'value' => ''],
            ['name' => 'version', 'group' => 'basic', 'title' => '版本号', 'type' => 'string', 'value' => 'P1'],
        ];

        foreach ($configs as $config) {
            Config::query()->create($config);
        }
    }
}
