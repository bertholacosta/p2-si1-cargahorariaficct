<template>
  <div class="min-h-screen flex bg-gray-100 dark:bg-gray-900">
    <!-- Toast para notificaciones -->
    <Toast position="top-right" />
    
    <!-- Overlay para móvil -->
    <div
      v-if="sidebarOpen && isMobile"
      @click="closeSidebar"
      class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
    ></div>

    <!-- Sidebar -->
    <aside
      :class="[
        'bg-white dark:bg-gray-800 shadow-lg transition-all duration-300 z-50',
        'fixed lg:relative inset-y-0 left-0',
        sidebarOpen ? 'w-64 translate-x-0' : '-translate-x-full lg:translate-x-0 lg:w-20'
      ]"
    >
      <div class="flex flex-col h-full">
        <!-- Logo/Header -->
        <div class="h-16 flex items-center justify-between px-4 border-b dark:border-gray-700">
          <h1 v-if="sidebarOpen" class="text-lg font-bold text-gray-800 dark:text-white">
            FICCT
          </h1>
          <!-- Botón único del menú -->
          <Button
            :icon="sidebarOpen ? 'pi pi-chevron-left' : 'pi pi-bars'"
            @click="toggleSidebar"
            text
            rounded
            class="text-gray-600 dark:text-gray-300"
          />
        </div>

        <!-- Menu Items -->
        <nav class="flex-1 px-2 py-4 space-y-2 overflow-y-auto">
          <!-- Regular menu items -->
          <a
            v-for="item in menuItems"
            :key="item.label"
            :href="item.route"
            @click.prevent="navigateTo(item.route)"
            :class="[
              'flex items-center gap-3 px-4 py-3 rounded-lg transition-colors cursor-pointer',
              isActive(item.route)
                ? 'bg-blue-500 text-white'
                : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
            ]"
          >
            <i :class="item.icon"></i>
            <span v-if="sidebarOpen">{{ item.label }}</span>
          </a>

          <!-- Horarios dropdown menu -->
          <div v-if="showHorariosMenu">
            <div
              @click="toggleHorariosMenu"
              :class="[
                'flex items-center gap-3 px-4 py-3 rounded-lg transition-colors cursor-pointer',
                'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
              ]"
            >
              <i class="pi pi-table"></i>
              <span v-if="sidebarOpen" class="flex-1">Horarios</span>
              <i
                v-if="sidebarOpen"
                :class="['pi transition-transform', horariosMenuOpen ? 'pi-chevron-down' : 'pi-chevron-right']"
              ></i>
            </div>

            <!-- Horarios submenu items -->
            <div v-if="horariosMenuOpen && sidebarOpen" class="ml-4 space-y-1">
              <a
                v-for="horariosItem in filteredHorariosItems"
                :key="horariosItem.label"
                :href="horariosItem.route"
                @click.prevent="navigateTo(horariosItem.route)"
                :class="[
                  'flex items-center gap-3 px-4 py-2 rounded-lg transition-colors cursor-pointer text-sm',
                  isActive(horariosItem.route)
                    ? 'bg-blue-500 text-white'
                    : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
                ]"
              >
                <i :class="horariosItem.icon"></i>
                <span>{{ horariosItem.label }}</span>
              </a>
            </div>
          </div>

          <!-- Administración dropdown menu -->
          <div v-if="showAdminMenu">
            <div
              @click="toggleAdminMenu"
              :class="[
                'flex items-center gap-3 px-4 py-3 rounded-lg transition-colors cursor-pointer',
                'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
              ]"
            >
              <i class="pi pi-shield"></i>
              <span v-if="sidebarOpen" class="flex-1">Administración</span>
              <i
                v-if="sidebarOpen"
                :class="['pi transition-transform', adminMenuOpen ? 'pi-chevron-down' : 'pi-chevron-right']"
              ></i>
            </div>

            <!-- Admin submenu items -->
            <div v-if="adminMenuOpen && sidebarOpen" class="ml-4 space-y-1">
              <a
                v-for="adminItem in filteredAdminItems"
                :key="adminItem.label"
                :href="adminItem.route"
                @click.prevent="navigateTo(adminItem.route)"
                :class="[
                  'flex items-center gap-3 px-4 py-2 rounded-lg transition-colors cursor-pointer text-sm',
                  isActive(adminItem.route)
                    ? 'bg-blue-500 text-white'
                    : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
                ]"
              >
                <i :class="adminItem.icon"></i>
                <span>{{ adminItem.label }}</span>
              </a>
            </div>
          </div>
        </nav>

        <!-- User Info -->
        <div class="border-t dark:border-gray-700 p-4">
          <div v-if="sidebarOpen" class="space-y-2">
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate">
              {{ user.username }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
              {{ user.rol?.nombre }}
            </p>
            <Button
              label="Cerrar Sesión"
              icon="pi pi-sign-out"
              @click="logout"
              severity="danger"
              outlined
              class="w-full"
              size="small"
            />
          </div>
          <Button
            v-else
            icon="pi pi-sign-out"
            @click="logout"
            severity="danger"
            text
            rounded
            class="w-full"
          />
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col min-w-0">
      <!-- Top Navbar -->
      <nav class="bg-white dark:bg-gray-800 shadow-sm h-16 flex items-center justify-between px-4 lg:px-6">
        <div class="flex items-center">
          <!-- Botón menú para móvil (solo cuando sidebar está oculto) -->
          <Button
            v-if="!sidebarOpen && isMobile"
            icon="pi pi-bars"
            @click="openSidebar"
            text
            rounded
            class="mr-3 text-gray-600 dark:text-gray-300"
          />
          <h2 class="text-lg lg:text-xl font-semibold text-gray-800 dark:text-white truncate">
            {{ title }}
          </h2>
        </div>
        
        <!-- Notificaciones -->
        <NotificacionesPanel />
      </nav>

      <!-- Content Area -->
      <main class="flex-1 p-4 lg:p-6 overflow-y-auto">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import NotificacionesPanel from '@/components/NotificacionesPanel.vue';
import { router, usePage } from '@inertiajs/vue3';
import { usePermissions } from '@/composables/usePermissions';
import { useToast } from 'primevue/usetoast';
import Button from 'primevue/button';
import Toast from 'primevue/toast';
import type { User } from '@/types';

const adminMenuOpen = ref(false);

defineProps<{
  title: string;
}>();

const page = usePage();
const user = (page.props.auth?.user || page.props.user) as User;
const { puedeVer } = usePermissions();
const toast = useToast();

const sidebarOpen = ref(false);
const isMobile = ref(false);

// Mostrar mensajes flash
watch(() => page.props.flash, (flash: any) => {
  if (flash?.success) {
    toast.add({
      severity: 'success',
      summary: 'Éxito',
      detail: flash.success,
      life: 3000
    });
  }
  if (flash?.error) {
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: flash.error,
      life: 5000
    });
  }
}, { deep: true });

const allMenuItems = [
  { label: 'Inicio', icon: 'pi pi-home', route: '/', permiso: null },
  { label: 'Asistencias', icon: 'pi pi-check-circle', route: '/asistencias', permiso: 'asistencias.ver' },
  { label: 'Reportes Asistencias', icon: 'pi pi-chart-bar', route: '/asistencias/reportes', permiso: 'asistencias.reportes' },
  { label: 'Asignaciones', icon: 'pi pi-calendar', route: '/asignaciones', permiso: 'asignaciones.ver' },
  { label: 'Gestiones', icon: 'pi pi-history', route: '/gestiones', permiso: 'gestiones.ver' },
  { label: 'Docentes', icon: 'pi pi-id-card', route: '/docentes', permiso: 'docentes.ver' },
  { label: 'Materias', icon: 'pi pi-book', route: '/materias', permiso: 'materias.ver' },
  { label: 'Grupos', icon: 'pi pi-users', route: '/grupos', permiso: 'grupos.ver' },
  { label: 'Aulas', icon: 'pi pi-building', route: '/aulas', permiso: 'aulas.ver' },
  { label: 'Módulos', icon: 'pi pi-th-large', route: '/modulos', permiso: 'modulos.ver' },
  { label: 'Reportes', icon: 'pi pi-chart-bar', route: '/reportes', permiso: 'reportes.ver' },
  { label: 'Configuración', icon: 'pi pi-cog', route: '/configuracion', permiso: 'configuracion.ver' },
];

const horariosMenuItems = [
  { label: 'Días', icon: 'pi pi-sun', route: '/dias', permiso: 'dias.ver' },
  { label: 'Bloques Horarios', icon: 'pi pi-clock', route: '/horas', permiso: 'horas.ver' },
  { label: 'Horarios', icon: 'pi pi-table', route: '/horarios', permiso: 'horarios.ver' },
];

const adminMenuItems = [
  { label: 'Usuarios', icon: 'pi pi-users', route: '/usuarios', permiso: 'usuarios.ver' },
  { label: 'Roles', icon: 'pi pi-shield', route: '/roles', permiso: 'roles.ver' },
  { label: 'Permisos', icon: 'pi pi-lock', route: '/permisos', permiso: 'permisos.ver' },
  { label: 'Días No Laborables', icon: 'pi pi-calendar-times', route: '/dias-no-laborables', permiso: 'asistencias.gestionar' },
  { label: 'Bitácora', icon: 'pi pi-file-edit', route: '/bitacora', permiso: 'bitacora.ver' },
];

// Filtrar items del menú según permisos del usuario
const menuItems = computed(() => {
  return allMenuItems.filter(item => {
    // Si no requiere permiso (como Inicio), mostrarlo siempre
    if (!item.permiso) return true;
    // Si requiere permiso, verificar que el usuario lo tenga
    return puedeVer(item.permiso.split('.')[0]);
  });
});

const filteredHorariosItems = computed(() => {
  return horariosMenuItems.filter(item => {
    if (!item.permiso) return true;
    return puedeVer(item.permiso.split('.')[0]);
  });
});

const filteredAdminItems = computed(() => {
  return adminMenuItems.filter(item => {
    if (!item.permiso) return true;
    return puedeVer(item.permiso.split('.')[0]);
  });
});

const showHorariosMenu = computed(() => filteredHorariosItems.value.length > 0);
const showAdminMenu = computed(() => filteredAdminItems.value.length > 0);

const horariosMenuOpen = ref(false);

const toggleHorariosMenu = () => {
  horariosMenuOpen.value = !horariosMenuOpen.value;
};

const toggleAdminMenu = () => {
  adminMenuOpen.value = !adminMenuOpen.value;
};

const checkMobile = () => {
  isMobile.value = window.innerWidth < 1024;
  if (!isMobile.value) {
    sidebarOpen.value = true;
  } else {
    sidebarOpen.value = false;
  }
};

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value;
};

const openSidebar = () => {
  sidebarOpen.value = true;
};

const closeSidebar = () => {
  if (isMobile.value) {
    sidebarOpen.value = false;
  }
};

const navigateTo = (route: string) => {
  router.visit(route);
  closeSidebar();
};

const isActive = (route: string) => {
  return window.location.pathname === route || window.location.pathname.startsWith(route + '/');
};

const logout = () => {
  // Enviar la fecha/hora del cliente al hacer logout
  router.post('/logout', {
    client_time: new Date().toISOString(),
  });
};

onMounted(() => {
  checkMobile();
  window.addEventListener('resize', checkMobile);
});

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile);
});
</script>
