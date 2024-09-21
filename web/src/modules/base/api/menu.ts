import type { PageList, ResponseStruct } from '#/global'

export interface MenuVo {
  id?: number
  parent_id?: number
  name: string
  code: string
  icon: string
  route: string
  component: string
  redirect: string
  meta?: Record<string, any>
  type: string
  status: number
  sort: number
  remark: string
}

export function page(): Promise<ResponseStruct<PageList<MenuVo>>> {
  return useHttp().get('/admin/menu/list')
}

export function create(data: MenuVo): Promise<ResponseStruct<null>> {
  return useHttp().post('/admin/menu', data)
}

export function save(id: number, data: MenuVo): Promise<ResponseStruct<null>> {
  return useHttp().put(`/admin/menu/${id}`, data)
}

export function deleteByIds(ids: number): Promise<ResponseStruct<null>> {
  return useHttp().delete('/admin/menu', ids)
}
