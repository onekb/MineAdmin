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

class DictDataRequest extends MineFormRequest
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
            'label' => 'required',
            'code' => 'required',
            'value' => 'required',
            'type_id' => 'required',
        ];
    }

    /**
     * 更新数据验证规则.
     */
    public function updateRules(): array
    {
        return [
            'label' => 'required',
            'code' => 'required',
            'value' => 'required',
        ];
    }

    /**
     * 修改状态数据验证规则.
     */
    public function changeStatusRules(): array
    {
        return [
            'id' => 'required',
            'status' => 'required',
        ];
    }

    /**
     * 字段映射名称.
     */
    public function attributes(): array
    {
        return [
            'id' => '字典ID',
            'name' => '字典名称',
            'code' => '字典标识',
            'value' => '字典',
            'status' => '字典状态',
        ];
    }
}
