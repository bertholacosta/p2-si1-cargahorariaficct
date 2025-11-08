<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800 p-4">
    <Card class="w-full max-w-md shadow-2xl">
      <template #header>
        <div class="text-center pt-6 px-6">
          <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-2">
            Bienvenido
          </h1>
          <p class="text-gray-600 dark:text-gray-400">
            Ingresa tus credenciales para continuar
          </p>
        </div>
      </template>
      
      <template #content>
        <form @submit.prevent="handleLogin" class="space-y-4">
          <div class="flex flex-col gap-1">
            <label for="email" class="font-semibold text-sm text-gray-700 dark:text-gray-300">
              Correo Electrónico
            </label>
            <InputText
              id="email"
              v-model="form.email"
              type="email"
              placeholder="usuario@ejemplo.com"
              :class="{ 'p-invalid': form.errors.email }"
              class="w-full"
            />
            <small v-if="form.errors.email" class="text-red-500">{{ form.errors.email }}</small>
          </div>

          <div class="flex flex-col gap-1 ">
            <label for="password" class="font-semibold text-sm text-gray-700 dark:text-gray-300">
              Contraseña
            </label>
            <Password
              id="password"
              v-model="form.password"
              placeholder="Ingresa tu contraseña"
              :class="{ 'p-invalid': form.errors.password }"
              :feedback="false"
              toggleMask
              inputClass="w-full"
              class="w-full"
            />
            <small v-if="form.errors.password" class="text-red-500">{{ form.errors.password }}</small>
          </div>

          <div class="pt-4">
          <Button
            type="submit"
            label="Iniciar Sesión"
            icon="pi pi-sign-in"
            :loading="form.processing"
            class="w-full "
            severity="primary"
          />
          </div>

          <div class="flex justify-start">
            <a href="#" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 py-2">
              ¿Olvidaste tu contraseña?
            </a>
          </div>

        </form>
      </template>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';

const form = useForm({
  email: '',
  password: '',
});

const handleLogin = () => {
  form.post('/login', {
    onSuccess: () => {
      // Redirige al dashboard después del login exitoso
    },
    onError: () => {
      // Los errores se manejan automáticamente por Inertia
    },
  });
};
</script>

<style scoped>
/* Estilos personalizados opcionales */
</style>
