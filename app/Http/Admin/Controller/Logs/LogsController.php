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

namespace App\Http\Admin\Controller\Logs;

use App\Service\Logs\LoginLogService;
use App\Service\Logs\OperLogService;
use App\Service\Logs\QueueLogService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use Hyperf\HttpServer\Annotation\GetMapping;
use Mine\Annotation\Auth;
use Mine\Annotation\OperationLog;
use Mine\Annotation\Permission;
use Mine\MineController;
use Psr\Http\Message\ResponseInterface;

/**
 * 日志控制器
 * Class LogsController.
 */
#[Controller(prefix: 'system/logs'), Auth]
class LogsController extends MineController
{
    /**
     * 登录日志服务
     */
    #[Inject]
    protected LoginLogService $loginLogService;

    /**
     * 操作日志服务
     */
    #[Inject]
    protected OperLogService $operLogService;

    /**
     * 队列日志服务
     */
    #[Inject]
    protected QueueLogService $queueLogService;

    /**
     * 获取登录日志列表.
     */
    #[GetMapping('getLoginLogPageList'), Permission('system:loginLog')]
    public function getLoginLogPageList(): ResponseInterface
    {
        return $this->success($this->loginLogService->getPageList($this->request->all()));
    }

    /**
     * 获取操作日志列表.
     */
    #[GetMapping('getOperLogPageList'), Permission('system:operLog')]
    public function getOperLogPageList(): ResponseInterface
    {
        return $this->success($this->operLogService->getPageList($this->request->all()));
    }

    /**
     * 获取队列日志列表.
     */
    #[GetMapping('getQueueLogPageList'), Permission('system:queueLog')]
    public function getQueueLogPageList(): ResponseInterface
    {
        return $this->success($this->queueLogService->getPageList($this->request->all()));
    }

    /**
     * 删除队列日志.
     */
    #[DeleteMapping('deleteQueueLog'), Permission('system:queueLog:delete'), OperationLog]
    public function deleteQueueLog(): ResponseInterface
    {
        return $this->queueLogService->delete((array) $this->request->input('ids', [])) ? $this->success() : $this->error();
    }

    /**
     * 删除操作日志.
     */
    #[DeleteMapping('deleteOperLog'), Permission('system:operLog:delete'), OperationLog]
    public function deleteOperLog(): ResponseInterface
    {
        return $this->operLogService->delete((array) $this->request->input('ids', [])) ? $this->success() : $this->error();
    }

    /**
     * 删除登录日志.
     */
    #[DeleteMapping('deleteLoginLog'), Permission('system:loginLog:delete'), OperationLog]
    public function deleteLoginLog(): ResponseInterface
    {
        return $this->loginLogService->delete((array) $this->request->input('ids', [])) ? $this->success() : $this->error();
    }
}
