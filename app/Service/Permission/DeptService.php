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

namespace App\Service\Permission;

use App\Repository\Permission\DeptRepository;
use Mine\Abstracts\AbstractService;
use Mine\Exception\NormalStatusException;

class DeptService extends AbstractService
{
    /**
     * @var DeptRepository
     */
    public $repository;

    public function __construct(DeptRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getTreeList(?array $params = null, bool $isScope = true): array
    {
        $params = array_merge(['orderBy' => 'sort', 'orderType' => 'desc'], $params);
        return parent::getTreeList($params, $isScope);
    }

    /**
     * 获取部门领导列表.
     */
    public function getLeaderList(?array $params = null): array
    {
        return $this->repository->getLeaderList($params);
    }

    /**
     * 新增部门领导
     */
    public function addLeader(array $data): bool
    {
        $users = [];
        foreach ($data['users'] as $item) {
            $users[] = array_merge(['created_at' => date('Y-m-d H:i:s')], $item);
        }
        return $this->repository->addLeader((int) $data['id'], $users);
    }

    /**
     * 删除部门领导
     */
    public function delLeader(array $data): bool
    {
        $users = [];
        foreach ($data['ids'] ?? [] as $id) {
            $users[] = ['user_id' => $id];
        }
        return $this->repository->delLeader((int) $data['id'], $users);
    }

    /**
     * 获取前端选择树.
     */
    public function getSelectTree(): array
    {
        return $this->repository->getSelectTree();
    }

    /**
     * 新增部门.
     */
    public function save(array $data): mixed
    {
        return $this->repository->save($this->handleData($data));
    }

    /**
     * 更新部门.
     */
    public function update(mixed $id, array $data): bool
    {
        return $this->repository->update($id, $this->handleData($data));
    }

    /**
     * 真实删除部门.
     */
    public function realDel(array $ids): ?array
    {
        // 跳过的部门
        $ctuIds = [];
        if (count($ids)) {
            foreach ($ids as $id) {
                if (! $this->checkChildrenExists((int) $id)) {
                    $this->repository->realDelete([$id]);
                } else {
                    $ctuIds[] = $id;
                }
            }
        }
        return count($ctuIds) ? $this->repository->getDeptName($ctuIds) : null;
    }

    /**
     * 检查子部门是否存在.
     */
    public function checkChildrenExists(int $id): bool
    {
        return $this->repository->checkChildrenExists($id);
    }

    /**
     * 处理数据.
     */
    protected function handleData(array $data): array
    {
        $pid = $data['parent_id'] ?? 0;

        if (isset($data['id']) && $data['id'] == $pid) {
            throw new NormalStatusException(t('system.parent_dept_error'), 500);
        }

        if ($pid === 0) {
            $data['level'] = $data['parent_id'] = '0';
        } else {
            $data['level'] = $this->read($data['parent_id'])->level . ',' . $data['parent_id'];
        }

        return $data;
    }
}
