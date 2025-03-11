<?php

namespace App\Library\DataPermission\Aspects;

use App\Library\DataPermission\Attribute\DataScope;
use Hyperf\Context\Context;
use Hyperf\Database\Query\Builder;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

#[Aspect]
final class DataScopeAspect extends AbstractAspect
{

    public const CONTEXT_KEY = 'data_permission';

    public array $annotations = [
        DataScope::class
    ];

    public array $classes = [
        Builder::class.'::update',
        Builder::class.'::delete',
        Builder::class.'::runSelect',
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (
            isset($proceedingJoinPoint->getAnnotationMetadata()->class[DataScope::class]) ||
            isset($proceedingJoinPoint->getAnnotationMetadata()->method[DataScope::class])
        ){
            return $this->handleDataScope($proceedingJoinPoint);
        }

        if ($proceedingJoinPoint->className === Builder::class){
            if ($proceedingJoinPoint->methodName==='runSelect'){
                return $this->handleSelect($proceedingJoinPoint);
            }
            if ($proceedingJoinPoint->methodName==='delete'){
                return $this->handleDelete($proceedingJoinPoint);
            }
            if ($proceedingJoinPoint->methodName==='update'){
               return $this->handleUpdate($proceedingJoinPoint);
            }
        }
        return $proceedingJoinPoint->process();
    }

    protected function handleDelete(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return $proceedingJoinPoint->process();
    }

    protected function handleUpdate(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return $proceedingJoinPoint->process();
    }

    protected function handleSelect(ProceedingJoinPoint $proceedingJoinPoint)
    {
        /**
         * @var Builder $builder
         */
        $builder = $proceedingJoinPoint->getInstance();
        if (Context::has(self::CONTEXT_KEY)){
            // todo 做数据权限处理
        }
        return $proceedingJoinPoint->process();
    }


    protected function handleDataScope(ProceedingJoinPoint $proceedingJoinPoint)
    {
        Context::set(self::CONTEXT_KEY, 1);
        $result =  $proceedingJoinPoint->process();
        Context::destroy(self::CONTEXT_KEY);
        return $result;
    }
}