<template>
  <div class="min-h-screen flex bg-gray-100 dark:bg-gray-900">
    <!-- Sidebar -->
    <aside :class="['bg-white dark:bg-gray-800 shadow-lg transition-all duration-300', sidebarOpen ? 'w-64' : 'w-20']">
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
        <nav class="flex-1 px-2 py-4 space-y-2">
          <a
            v-for="item in menuItems"
            :key="item.label"
            :href="item.route"
            @click.prevent="router.visit(item.route)"
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
        </nav>

        <!-- User Info -->
        <div class="border-t dark:border-gray-700 p-4">
          <div v-if="sidebarOpen" class="space-y-2">
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
              {{ user.username }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400">
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
    <div class="flex-1 flex flex-col">
      <!-- Top Navbar -->
      <nav class="bg-white dark:bg-gray-800 shadow-sm h-16 flex items-center px-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
          {{ title }}
        </h2>
      </nav>

      <!-- Content Area -->
      <main class="flex-1 p-6 overflow-y-auto">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import Button from 'primevue/button';
import type { User } from '@/types';

defineProps<{
  title: string;
}>();

const page = usePage();
const user = (page.props.auth?.user || page.props.user) as User;

const sidebarOpen = ref(true);

const menuItems = [
  { label: 'Inicio', icon: 'pi pi-home', route: '/' },
  { label: 'Horarios', icon: 'pi pi-calendar', route: '/horarios' },
  { label: 'Usuarios', icon: 'pi pi-users', route: '/usuarios' },
  { label: 'Reportes', icon: 'pi pi-chart-bar', route: '/reportes' },
  { label: 'Configuración', icon: 'pi pi-cog', route: '/configuracion' },
];

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value;
};

const isActive = (route: string) => {
  return window.location.pathname === route || window.location.pathname.startsWith(route + '/');
};

const logout = () => {
  router.post('/logout');
};
</script>
