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
            href="#"
            @click.prevent="currentView = item.view"
            :class="[
              'flex items-center gap-3 px-4 py-3 rounded-lg transition-colors',
              currentView === item.view
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
          {{ currentViewTitle }}
        </h2>
      </nav>

      <!-- Content Area -->
      <main class="flex-1 p-6 overflow-y-auto">
        <!-- Dashboard Home -->
        <div v-if="currentView === 'home'" class="space-y-6">
          <!-- Card de Bienvenida -->
          <Card>
            <template #title>
              <div class="flex items-center gap-2">
                <i class="pi pi-user text-blue-500"></i>
                Bienvenido, {{ user.username }}!
              </div>
            </template>
            <template #content>
              <p class="text-gray-600 dark:text-gray-400">
                Has iniciado sesión exitosamente en el sistema de gestión de horarios de la FICCT.
              </p>
            </template>
          </Card>

          <!-- Grid de Cards -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Card de Información -->
            <Card>
              <template #title>
                <div class="flex items-center gap-2">
                  <i class="pi pi-info-circle text-blue-500"></i>
                  Información de Usuario
                </div>
              </template>
              <template #content>
                <div class="space-y-2">
                  <p class="text-sm"><strong>Email:</strong> {{ user.email }}</p>
                  <p class="text-sm"><strong>Rol:</strong> {{ user.rol?.nombre }}</p>
                  <p class="text-sm"><strong>Username:</strong> {{ user.username }}</p>
                </div>
              </template>
            </Card>

            <!-- Card de Permisos -->
            <Card>
              <template #title>
                <div class="flex items-center gap-2">
                  <i class="pi pi-shield text-green-500"></i>
                  Permisos
                </div>
              </template>
              <template #content>
                <div class="space-y-1 max-h-40 overflow-y-auto">
                  <p v-for="permiso in user.rol?.permisos" :key="permiso.id" class="text-sm">
                    <i class="pi pi-check text-green-500 mr-2"></i>
                    {{ permiso.nombre }}
                  </p>
                </div>
              </template>
            </Card>

            <!-- Card de Estadísticas -->
            <Card>
              <template #title>
                <div class="flex items-center gap-2">
                  <i class="pi pi-chart-bar text-purple-500"></i>
                  Estadísticas
                </div>
              </template>
              <template #content>
                <div class="space-y-3">
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Horarios Activos</span>
                    <span class="text-lg font-bold text-blue-500">12</span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Usuarios</span>
                    <span class="text-lg font-bold text-green-500">45</span>
                  </div>
                </div>
              </template>
            </Card>
          </div>
        </div>

        <!-- Horarios View -->
        <div v-if="currentView === 'horarios'">
          <Card>
            <template #title>Gestión de Horarios</template>
            <template #content>
              <p class="text-gray-600 dark:text-gray-400">
                Aquí puedes gestionar los horarios académicos.
              </p>
            </template>
          </Card>
        </div>

        <!-- Usuarios View -->
        <div v-if="currentView === 'usuarios'">
          <Card>
            <template #title>Gestión de Usuarios</template>
            <template #content>
              <p class="text-gray-600 dark:text-gray-400">
                Aquí puedes gestionar los usuarios del sistema.
              </p>
            </template>
          </Card>
        </div>

        <!-- Reportes View -->
        <div v-if="currentView === 'reportes'">
          <Card>
            <template #title>Reportes</template>
            <template #content>
              <p class="text-gray-600 dark:text-gray-400">
                Aquí puedes generar y visualizar reportes.
              </p>
            </template>
          </Card>
        </div>

        <!-- Configuración View -->
        <div v-if="currentView === 'configuracion'">
          <Card>
            <template #title>Configuración</template>
            <template #content>
              <p class="text-gray-600 dark:text-gray-400">
                Aquí puedes ajustar las configuraciones del sistema.
              </p>
            </template>
          </Card>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
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

const sidebarOpen = ref(true);
const currentView = ref('home');

const menuItems = [
  { label: 'Inicio', icon: 'pi pi-home', view: 'home' },
  { label: 'Horarios', icon: 'pi pi-calendar', view: 'horarios' },
  { label: 'Usuarios', icon: 'pi pi-users', view: 'usuarios' },
  { label: 'Reportes', icon: 'pi pi-chart-bar', view: 'reportes' },
  { label: 'Configuración', icon: 'pi pi-cog', view: 'configuracion' },
];

const currentViewTitle = computed(() => {
  const item = menuItems.find(i => i.view === currentView.value);
  return item?.label || 'Dashboard';
});

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value;
};

const logout = () => {
  router.post('/logout');
};
</script>
