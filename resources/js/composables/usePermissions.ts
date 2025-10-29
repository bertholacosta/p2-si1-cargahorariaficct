import { usePage } from '@inertiajs/vue3';
import type { User } from '@/types';

/**
 * Composable para verificar permisos del usuario autenticado
 */
export function usePermissions() {
  const page = usePage();
  const user = (page.props.auth?.user || page.props.user) as User;

  /**
   * Verifica si el usuario tiene un permiso específico
   * @param slug - Slug del permiso (ej: 'usuarios.ver', 'docentes.crear')
   * @returns true si tiene el permiso, false en caso contrario
   */
  const tienePermiso = (slug: string): boolean => {
    if (!user || !user.rol || !user.rol.permisos) {
      return false;
    }

    return user.rol.permisos.some((permiso: any) => permiso.slug === slug);
  };

  /**
   * Verifica si el usuario tiene al menos uno de los permisos especificados
   * @param slugs - Array de slugs de permisos
   * @returns true si tiene al menos uno, false en caso contrario
   */
  const tieneAlgunPermiso = (slugs: string[]): boolean => {
    return slugs.some(slug => tienePermiso(slug));
  };

  /**
   * Verifica si el usuario tiene todos los permisos especificados
   * @param slugs - Array de slugs de permisos
   * @returns true si tiene todos, false en caso contrario
   */
  const tieneTodosPermisos = (slugs: string[]): boolean => {
    return slugs.every(slug => tienePermiso(slug));
  };

  /**
   * Verifica si el usuario puede ver un módulo (tiene permiso .ver)
   * @param modulo - Nombre del módulo (ej: 'usuarios', 'docentes')
   * @returns true si puede ver, false en caso contrario
   */
  const puedeVer = (modulo: string): boolean => {
    return tienePermiso(`${modulo}.ver`);
  };

  /**
   * Verifica si el usuario puede crear en un módulo
   * @param modulo - Nombre del módulo
   * @returns true si puede crear, false en caso contrario
   */
  const puedeCrear = (modulo: string): boolean => {
    return tienePermiso(`${modulo}.crear`);
  };

  /**
   * Verifica si el usuario puede editar en un módulo
   * @param modulo - Nombre del módulo
   * @returns true si puede editar, false en caso contrario
   */
  const puedeEditar = (modulo: string): boolean => {
    return tienePermiso(`${modulo}.editar`);
  };

  /**
   * Verifica si el usuario puede eliminar en un módulo
   * @param modulo - Nombre del módulo
   * @returns true si puede eliminar, false en caso contrario
   */
  const puedeEliminar = (modulo: string): boolean => {
    return tienePermiso(`${modulo}.eliminar`);
  };

  return {
    tienePermiso,
    tieneAlgunPermiso,
    tieneTodosPermisos,
    puedeVer,
    puedeCrear,
    puedeEditar,
    puedeEliminar,
    user,
  };
}
