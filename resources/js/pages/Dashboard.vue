<template>
  <div class="min-h-screen flex bg-gray-100 dark:bg-gray-900">
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
          <Button
            :icon="sidebarOpen ? 'pi pi-times' : 'pi pi-bars'"
            @click="toggleSidebar"
            text
            rounded
            class="text-gray-600 dark:text-gray-300"
          />
        </div>

        <!-- Menu Items -->
        <nav class="flex-1 px-2 py-4 space-y-2 overflow-y-auto">
          <a
            v-for="item in menuItems"
            :key="item.label"
            :href="item.route"
            @click.prevent="navigateTo(item.route)"
            :class="[
              'flex items-center gap-3 px-4 py-3 rounded-lg transition-colors cursor-pointer',
              currentRoute === item.route
                ? 'bg-blue-500 text-white'
                : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
            ]"
          >
            <i :class="item.icon"></i>
            <span v-if="sidebarOpen">{{ item.label }}</span>
          </a>
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
      <nav class="bg-white dark:bg-gray-800 shadow-sm h-16 flex items-center px-4 lg:px-6">
        <!-- Botón menú móvil -->
        <Button
          icon="pi pi-bars"
          @click="openSidebar"
          text
          rounded
          class="lg:hidden mr-3 text-gray-600 dark:text-gray-300"
        />
        <h2 class="text-lg lg:text-xl font-semibold text-gray-800 dark:text-white truncate">
          {{ currentViewTitle }}
        </h2>
      </nav>

      <!-- Content Area -->
      <main class="flex-1 flex flex-col p-4 lg:p-6 overflow-hidden">
        <!-- Dashboard Home -->
        <div class="flex flex-col h-full space-y-4 lg:space-y-6">
          <!-- Card de Bienvenida -->
          <Card class="shrink-0">
            <template #title>
              <div class="flex items-center gap-2">
                <i class="pi pi-user text-blue-500 text-lg sm:text-xl"></i>
                <span class="text-base sm:text-lg lg:text-xl">Bienvenido, {{ user.username }}!</span>
              </div>
            </template>
            <template #content>
              <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400">
                Has iniciado sesión exitosamente en el sistema de gestión de horarios de la FICCT.
              </p>
            </template>
          </Card>

          <!-- Grid de Cards con scroll -->
          <div class="flex-1 overflow-y-auto">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6 pb-4">
            <!-- Card de Información -->
            <Card class="h-full">
              <template #title>
                <div class="flex items-center gap-2">
                  <i class="pi pi-info-circle text-blue-500 text-base sm:text-lg"></i>
                  <span class="text-sm sm:text-base">Información de Usuario</span>
                </div>
              </template>
              <template #content>
                <div class="space-y-2">
                  <p class="text-xs sm:text-sm break-words">
                    <strong>Email:</strong> {{ user.email }}
                  </p>
                  <p class="text-xs sm:text-sm">
                    <strong>Rol:</strong> {{ user.rol?.nombre }}
                  </p>
                  <p class="text-xs sm:text-sm">
                    <strong>Username:</strong> {{ user.username }}
                  </p>
                </div>
              </template>
            </Card>

            <!-- Card de Permisos -->
            <Card class="h-full">
              <template #title>
                <div class="flex items-center gap-2">
                  <i class="pi pi-shield text-green-500 text-base sm:text-lg"></i>
                  <span class="text-sm sm:text-base">Permisos</span>
                </div>
              </template>
              <template #content>
                <div class="space-y-1 max-h-40 overflow-y-auto">
                  <p v-for="permiso in user.rol?.permisos" :key="permiso.id" class="text-xs sm:text-sm">
                    <i class="pi pi-check text-green-500 mr-2 text-xs"></i>
                    {{ permiso.nombre }}
                  </p>
                </div>
              </template>
            </Card>

            <!-- Card de Estadísticas -->
            <Card class="h-full sm:col-span-2 lg:col-span-1">
              <template #title>
                <div class="flex items-center gap-2">
                  <i class="pi pi-chart-bar text-purple-500 text-base sm:text-lg"></i>
                  <span class="text-sm sm:text-base">Estadísticas</span>
                </div>
              </template>
              <template #content>
                <div class="space-y-3">
                  <div class="flex justify-between items-center">
                    <span class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">Horarios Activos</span>
                    <span class="text-lg sm:text-xl font-bold text-blue-500">12</span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">Usuarios</span>
                    <span class="text-lg sm:text-xl font-bold text-green-500">45</span>
                  </div>
                </div>
              </template>
            </Card>
          </div>
            </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import Card from 'primevue/card';
import Button from 'primevue/button';

interface Permiso {
  id: number;
  nombre: string;
}

interface Rol {
  id: number;
  nombre: string;
  permisos: Permiso[];
}

interface User {
  id: number;
  username: string;
  email: string;
  rol?: Rol;
}

const props = defineProps<{
  user: User;
}>();

const sidebarOpen = ref(false);
const isMobile = ref(false);
const currentRoute = ref('/');

const menuItems = [
  { label: 'Inicio', icon: 'pi pi-home', route: '/' },
  { label: 'Horarios', icon: 'pi pi-calendar', route: '/horarios' },
  { label: 'Usuarios', icon: 'pi pi-users', route: '/usuarios' },
  { label: 'Reportes', icon: 'pi pi-chart-bar', route: '/reportes' },
  { label: 'Configuración', icon: 'pi pi-cog', route: '/configuracion' },
];

const currentViewTitle = computed(() => {
  const item = menuItems.find(i => i.route === currentRoute.value);
  return item?.label || 'Dashboard';
});

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
  if (route === '/') {
    currentRoute.value = route;
  } else {
    router.visit(route);
  }
  closeSidebar();
};

const logout = () => {
  router.post('/logout');
};

onMounted(() => {
  checkMobile();
  window.addEventListener('resize', checkMobile);
});

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile);
});
</script>
