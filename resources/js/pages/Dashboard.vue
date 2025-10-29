<template>
  <AppLayout title="Dashboard">
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
  </AppLayout>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import Card from 'primevue/card';
import type { User } from '@/types';

const page = usePage();
const user = (page.props.auth?.user || page.props.user) as User;
const { puedeVer } = usePermissions();
</script>
