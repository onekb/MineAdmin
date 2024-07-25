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

namespace App\Http\Admin\Request;

use Mine\MineFormRequest;

class NoticeRequest extends MineFormRequest
{
    /**
     * 公共规则.
     */
    public function commonRules(): array
    {
        return [];
    }

    /**
     * 新增数据验证规则.
     */
    public function saveRules(): array
    {
        return [
            'title' => 'required',
            'type' => 'required',
            'content' => 'required',
        ];
    }

    /**
     * 更新数据验证规则.
     */
    public function updateRules(): array
    {
        return [
            'title' => 'required',
            'type' => 'required',
            'content' => 'required',
        ];
    }

    /**
     * 字段映射名称.
     */
    public function attributes(): array
    {
        return [
            'title' => '公告标题',
            'type' => '公告类型',
            'content' => '公告内容',
        ];
    }
}
