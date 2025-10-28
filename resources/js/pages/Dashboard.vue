<template>
  <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
    <!-- Navbar -->
    <nav class="bg-white dark:bg-gray-800 shadow-lg">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
              Sistema de Horarios - FICCT
            </h1>
          </div>
          
          <div class="flex items-center gap-4">
            <div class="text-right">
              <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ user.username }}
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                {{ user.rol?.nombre }}
              </p>
            </div>
            
            <Button
              label="Cerrar Sesión"
              icon="pi pi-sign-out"
              @click="logout"
              severity="danger"
              outlined
            />
          </div>
        </div>
      </div>
    </nav>

    <!-- Contenido Principal -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Card de Bienvenida -->
          <Card class="md:col-span-3">
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
                <p><strong>Email:</strong> {{ user.email }}</p>
                <p><strong>Rol:</strong> {{ user.rol?.nombre }}</p>
                <p><strong>Username:</strong> {{ user.username }}</p>
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
              <div class="space-y-1">
                <p v-for="permiso in user.rol?.permisos" :key="permiso.id" class="text-sm">
                  <i class="pi pi-check text-green-500 mr-2"></i>
                  {{ permiso.nombre }}
                </p>
              </div>
            </template>
          </Card>

          <!-- Card de Acciones -->
          <Card>
            <template #title>
              <div class="flex items-center gap-2">
                <i class="pi pi-cog text-purple-500"></i>
                Acciones Rápidas
              </div>
            </template>
            <template #content>
              <div class="flex flex-col gap-2">
                <Button label="Ver Horarios" icon="pi pi-calendar" severity="info" outlined />
                <Button label="Gestionar Usuarios" icon="pi pi-users" severity="success" outlined />
                <Button label="Configuración" icon="pi pi-cog" severity="secondary" outlined />
              </div>
            </template>
          </Card>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
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

defineProps<{
  user: User;
}>();

const logout = () => {
  router.post('/logout');
};
</script>
