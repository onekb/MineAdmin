<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */

namespace HyperfTests\Feature\Admin\Permission;

use App\Constants\User\Status;
use App\Constants\User\Type;
use App\Http\Common\ResultCode;
use App\Model\Permission\Role;
use App\Model\Permission\User;
use Hyperf\Collection\Arr;
use Hyperf\Stringable\Str;
use HyperfTests\Feature\Admin\ControllerCase;

/**
 * @internal
 * @coversNothing
 */
class UserControllerTest extends ControllerCase
{
    public function testPageList(): void
    {
        $token = $this->token;

        $noTokenResult = $this->get('/admin/user/list');
        $this->assertSame(Arr::get($noTokenResult, 'code'), ResultCode::UNAUTHORIZED->value);

        $result = $this->get('/admin/user/list', ['token' => $token]);
        $this->assertSame(Arr::get($result, 'code'), ResultCode::FORBIDDEN->value);
        $enforce = $this->getEnforce();
        $this->assertFalse($enforce->hasPermissionForUser($this->user->username, 'user:list'));
        $this->assertTrue($enforce->addPermissionForUser($this->user->username, 'user:list'));
        $this->assertTrue($enforce->hasPermissionForUser($this->user->username, 'user:list'));
        $result = $this->get('/admin/user/list', ['token' => $token]);
        $this->assertSame(Arr::get($result, 'code'), ResultCode::SUCCESS->value);
        $this->assertSame(Arr::get($result, 'data.total'), User::withTrashed()->count());
        $this->assertTrue($enforce->deletePermissionForUser($this->user->username, 'user:list'));
        $result = $this->get('/admin/user/list', ['token' => $token]);
        $this->assertSame(Arr::get($result, 'code'), ResultCode::FORBIDDEN->value);
    }

    public function testCreate(): void
    {
        $token = $this->token;
        $attributes = [
            'username',
            'user_type',
            'nickname',
        ];
        foreach ($attributes as $attribute) {
            $result = $this->post('/admin/user', [$attribute => '']);
            $this->assertSame(Arr::get($result, 'code'), ResultCode::UNPROCESSABLE_ENTITY->value);
        }
        $fillAttributes = [
            'username' => Str::random(),
            'user_type' => 100,
            'nickname' => Str::random(),
        ];
        $result = $this->post('/admin/user', $fillAttributes);
        $this->assertSame(Arr::get($result, 'code'), ResultCode::UNAUTHORIZED->value);
        $result = $this->post('/admin/user', $fillAttributes, ['Authorization' => 'Bearer ' . $token]);
        $this->assertSame(Arr::get($result, 'code'), ResultCode::FORBIDDEN->value);
        $enforce = $this->getEnforce();
        $this->assertFalse($enforce->hasPermissionForUser($this->user->username, 'user:create'));
        $this->assertTrue($enforce->addPermissionForUser($this->user->username, 'user:create'));
        $this->assertTrue($enforce->hasPermissionForUser($this->user->username, 'user:create'));
        $result = $this->post('/admin/user', $fillAttributes, ['Authorization' => 'Bearer ' . $token]);
        $this->assertSame(Arr::get($result, 'code'), ResultCode::SUCCESS->value);
        $this->assertIsString($this->getToken(User::query()->where('username', $fillAttributes['username'])->first()));
        User::query()->where('username', $fillAttributes['username'])->forceDelete();
        $fillAttributes = [
            'username' => Str::random(),
            'user_type' => 100,
            'nickname' => Str::random(),
            'phone' => Str::random(8),
            'email' => Str::random(10) . '@qq.com',
            'avatar' => 'https://www.baidu.com',
            'signed' => 'test',
            'dashboard' => 'test',
            'status' => 1,
            'backend_setting' => ['test'],
            'remark' => 'test',
        ];
        $result = $this->post('/admin/user', $fillAttributes, ['Authorization' => 'Bearer ' . $token]);
        $this->assertSame(Arr::get($result, 'code'), ResultCode::SUCCESS->value);
        $this->assertIsString($this->getToken(User::query()->where('username', $fillAttributes['username'])->first()));
        User::query()->where('username', $fillAttributes['username'])->forceDelete();
    }

    public function testDelete(): void
    {
        $user = User::create([
            'username' => Str::random(),
            'user_type' => 100,
            'nickname' => Str::random(),
        ]);
        $token = $this->token;
        $result = $this->delete('/admin/user', [], ['Authorization' => 'Bearer ' . $token]);
        $this->assertSame(Arr::get($result, 'code'), ResultCode::FORBIDDEN->value);
        $enforce = $this->getEnforce();
        $this->assertFalse($enforce->hasPermissionForUser($this->user->username, 'user:delete'));
        $this->assertTrue($enforce->addPermissionForUser($this->user->username, 'user:delete'));
        $this->assertTrue($enforce->hasPermissionForUser($this->user->username, 'user:delete'));
        $result = $this->delete('/admin/user', [$user->getKey()], ['Authorization' => 'Bearer ' . $token]);
        $this->assertSame(Arr::get($result, 'code'), ResultCode::SUCCESS->value);
        $user->refresh();
        $this->assertTrue($user->trashed());
        $user->forceDelete();
    }

    public function testSave(): void
    {
        $user = User::create([
            'username' => Str::random(),
            'user_type' => 100,
            'nickname' => Str::random(),
        ]);
        $token = $this->token;
        $result = $this->put('/admin/user/' . $user->id);
        $this->assertSame(Arr::get($result, 'code'), ResultCode::UNPROCESSABLE_ENTITY->value);
        $fillAttributes = [
            'username' => Str::random(),
            'user_type' => 100,
            'nickname' => Str::random(),
            'phone' => Str::random(8),
            'email' => Str::random(10) . '@qq.com',
            'avatar' => 'https://www.baidu.com',
            'signed' => 'test',
            'dashboard' => 'test',
            'status' => 1,
            'backend_setting' => ['testxx'],
            'remark' => 'test',
        ];
        $result = $this->put('/admin/user/' . $user->id, $fillAttributes);
        $this->assertSame(Arr::get($result, 'code'), ResultCode::UNAUTHORIZED->value);
        $result = $this->put('/admin/user/' . $user->id, $fillAttributes, ['Authorization' => 'Bearer ' . $token]);
        $this->assertSame(Arr::get($result, 'code'), ResultCode::FORBIDDEN->value);
        $enforce = $this->getEnforce();
        $this->assertFalse($enforce->hasPermissionForUser($this->user->username, 'user:save'));
        $this->assertTrue($enforce->addPermissionForUser($this->user->username, 'user:save'));
        $this->assertTrue($enforce->hasPermissionForUser($this->user->username, 'user:save'));
        $result = $this->put('/admin/user/' . $user->id, $fillAttributes, ['Authorization' => 'Bearer ' . $token]);
        $this->assertSame(Arr::get($result, 'code'), ResultCode::SUCCESS->value);
        $user->refresh();
        $this->assertSame($user->username, $fillAttributes['username']);
        $this->assertSame($user->user_type, Type::from($fillAttributes['user_type']));
        $this->assertSame($user->nickname, $fillAttributes['nickname']);
        $this->assertSame($user->phone, $fillAttributes['phone']);
        $this->assertSame($user->email, $fillAttributes['email']);
        $this->assertSame($user->avatar, $fillAttributes['avatar']);
        $this->assertSame($user->signed, $fillAttributes['signed']);
        $this->assertSame($user->dashboard, $fillAttributes['dashboard']);
        $this->assertSame($user->status, Status::from($fillAttributes['status']));
        $this->assertSame($user->backend_setting, $fillAttributes['backend_setting']);
        $this->assertSame($user->remark, $fillAttributes['remark']);
        $user->forceDelete();
    }

    public function testUpdateInfo(): void
    {
        $user = $this->user;
        $token = $this->token;
        $result = $this->put('/admin/user');
        $this->assertSame(Arr::get($result, 'code'), ResultCode::UNPROCESSABLE_ENTITY->value);
        $fillAttributes = [
            'username' => Str::random(),
            'user_type' => 100,
            'nickname' => Str::random(),
            'phone' => Str::random(8),
            'email' => Str::random(10) . '@qq.com',
            'avatar' => 'https://www.baidu.com',
            'signed' => 'test',
            'dashboard' => 'test',
            'status' => 1,
            'backend_setting' => ['testxx'],
            'remark' => 'test',
        ];
        $result = $this->put('/admin/user', $fillAttributes);
        $this->assertSame(Arr::get($result, 'code'), ResultCode::UNAUTHORIZED->value);
        $result = $this->put('/admin/user', $fillAttributes, ['Authorization' => 'Bearer ' . $token]);
        $this->assertSame(Arr::get($result, 'code'), ResultCode::FORBIDDEN->value);
        $enforce = $this->getEnforce();
        $this->assertFalse($enforce->hasPermissionForUser($this->user->username, 'user:info'));
        $this->assertTrue($enforce->addPermissionForUser($this->user->username, 'user:info'));
        $this->assertTrue($enforce->hasPermissionForUser($this->user->username, 'user:info'));
        $result = $this->put('/admin/user', $fillAttributes, ['Authorization' => 'Bearer ' . $token]);
        $this->assertSame(Arr::get($result, 'code'), ResultCode::SUCCESS->value);
        $user->refresh();
        $this->assertSame($user->username, $fillAttributes['username']);
        $this->assertSame($user->user_type, Type::from($fillAttributes['user_type']));
        $this->assertSame($user->nickname, $fillAttributes['nickname']);
        $this->assertSame($user->phone, $fillAttributes['phone']);
        $this->assertSame($user->email, $fillAttributes['email']);
        $this->assertSame($user->avatar, $fillAttributes['avatar']);
        $this->assertSame($user->signed, $fillAttributes['signed']);
        $this->assertSame($user->dashboard, $fillAttributes['dashboard']);
        $this->assertSame($user->status, Status::from($fillAttributes['status']));
        $this->assertSame($user->backend_setting, $fillAttributes['backend_setting']);
        $this->assertSame($user->remark, $fillAttributes['remark']);
        $user->forceDelete();
    }

    public function testResetPassword(): void
    {
        $token = $this->token;
        $user = $this->user;
        $oldPassword = $user->password;
        $result = $this->put('/admin/user/password');
        $this->assertSame(Arr::get($result, 'code'), ResultCode::UNAUTHORIZED->value);
        $result = $this->put('/admin/user/password', [], ['Authorization' => 'Bearer ' . $token]);
        $this->assertSame(Arr::get($result, 'code'), ResultCode::FORBIDDEN->value);
        $enforce = $this->getEnforce();
        $this->assertFalse($enforce->hasPermissionForUser($this->user->username, 'user:password'));
        $this->assertTrue($enforce->addPermissionForUser($this->user->username, 'user:password'));
        $this->assertTrue($enforce->hasPermissionForUser($this->user->username, 'user:password'));
        $result = $this->put('/admin/user/password', [], ['Authorization' => 'Bearer ' . $token]);
        $this->assertSame(Arr::get($result, 'code'), ResultCode::SUCCESS->value);
        $user->refresh();
        $this->assertNotSame($oldPassword, $user->password);
    }

    public function testBatchGrantRolesForUser(): void
    {
        $token = $this->token;
        $user = $this->user;
        $roles = [
            Role::create([
                'name' => Str::random(10),
                'code' => Str::random(10),
                'sort' => rand(1, 100),
                'status' => rand(0, 1),
                'remark' => Str::random(),
            ]),
            Role::create([
                'name' => Str::random(10),
                'code' => Str::random(10),
                'sort' => rand(1, 100),
                'status' => rand(0, 1),
                'remark' => Str::random(),
            ]),
            Role::create([
                'name' => Str::random(10),
                'code' => Str::random(10),
                'sort' => rand(1, 100),
                'status' => rand(0, 1),
                'remark' => Str::random(),
            ]),
        ];
        $roleIds = array_map(fn ($role) => $role->id, $roles);
        $roleCodes = array_map(fn ($role) => $role->code, $roles);
        $result = $this->put('/admin/user/' . $user->id . '/role', ['role_codes' => $roleCodes]);
        $this->assertSame(Arr::get($result, 'code'), ResultCode::UNAUTHORIZED->value);
        $result = $this->put('/admin/user/' . $user->id . '/role', ['role_codes' => $roleCodes], ['Authorization' => 'Bearer ' . $token]);
        $this->assertSame(Arr::get($result, 'code'), ResultCode::FORBIDDEN->value);
        $enforce = $this->getEnforce();
        $this->assertFalse($enforce->hasPermissionForUser($this->user->username, 'user:role'));
        $this->assertTrue($enforce->addPermissionForUser($this->user->username, 'user:role'));
        $this->assertTrue($enforce->hasPermissionForUser($this->user->username, 'user:role'));
        $result = $this->put('/admin/user/' . $user->id . '/role', ['role_codes' => $roleCodes], ['Authorization' => 'Bearer ' . $token]);
        $this->assertSame(Arr::get($result, 'code'), ResultCode::SUCCESS->value);
        $user->refresh();
        $this->assertSame($user->roles()->pluck('role.id')->toArray(), $roleIds);
    }
}
