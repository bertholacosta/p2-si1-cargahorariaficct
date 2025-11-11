<template>
  <AppLayout title="Gestión de Notificaciones">
    <div class="container mx-auto px-4 py-6 max-w-7xl">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Enviar Notificaciones Masivas</h1>
        <p class="text-sm text-gray-600 mt-1">Envía mensajes a múltiples usuarios</p>
      </div>

      <!-- Formulario de envío -->
      <Card>
        <template #content>
          <div class="space-y-4">
            <!-- Selección de destinatarios -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Destinatarios *
              </label>
              <MultiSelect
                v-model="form.destinatarios"
                :options="usuariosOptions"
                optionLabel="label"
                optionValue="value"
                placeholder="Seleccionar usuarios"
                :filter="true"
                class="w-full"
                display="chip"
              >
                <template #option="slotProps">
                  <div class="flex items-center gap-2">
                    <i :class="getRolIcon(slotProps.option.rol)"></i>
                    <span>{{ slotProps.option.label }}</span>
                    <Tag :value="slotProps.option.rol" size="small" />
                  </div>
                </template>
              </MultiSelect>
              <small class="text-gray-500">
                {{ form.destinatarios.length }} usuario(s) seleccionado(s)
              </small>
            </div>

            <!-- Selección rápida por rol -->
            <div class="flex gap-2 flex-wrap">
              <Button
                label="Todos los Docentes"
                icon="pi pi-users"
                @click="seleccionarPorRol('Docente')"
                severity="info"
                outlined
                size="small"
              />
              <Button
                label="Todos los Administradores"
                icon="pi pi-shield"
                @click="seleccionarPorRol('Administrador')"
                severity="warn"
                outlined
                size="small"
              />
              <Button
                label="Todos los Usuarios"
                icon="pi pi-users"
                @click="seleccionarTodos"
                outlined
                size="small"
              />
              <Button
                label="Limpiar Selección"
                icon="pi pi-times"
                @click="form.destinatarios = []"
                severity="danger"
                outlined
                size="small"
              />
            </div>

            <!-- Título -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Título del Mensaje *
              </label>
              <InputText
                v-model="form.titulo"
                placeholder="Ejemplo: Mantenimiento programado del sistema"
                class="w-full"
                maxlength="200"
              />
              <small class="text-gray-500">
                {{ form.titulo.length }}/200 caracteres
              </small>
            </div>

            <!-- Mensaje -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Mensaje *
              </label>
              <Textarea
                v-model="form.mensaje"
                rows="6"
                placeholder="Escribe el mensaje que deseas enviar..."
                class="w-full"
              />
              <small class="text-gray-500">
                {{ form.mensaje.length }} caracteres
              </small>
            </div>

            <!-- Botones -->
            <div class="flex gap-3 pt-4">
              <Button
                label="Enviar Notificación"
                icon="pi pi-send"
                @click="enviarNotificacion"
                :loading="enviando"
                :disabled="!formularioValido"
              />
              <Button
                label="Limpiar Formulario"
                icon="pi pi-refresh"
                @click="limpiarFormulario"
                severity="secondary"
                outlined
              />
            </div>
          </div>
        </template>
      </Card>

      <!-- Historial reciente (opcional) -->
      <Card class="mt-6">
        <template #title>
          <div class="flex items-center gap-2">
            <i class="pi pi-history text-blue-600"></i>
            <span>Estadísticas</span>
          </div>
        </template>
        <template #content>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="text-center p-4 bg-blue-50 rounded-lg">
              <i class="pi pi-users text-3xl text-blue-600 mb-2 block"></i>
              <p class="text-2xl font-bold text-blue-700">{{ usuarios.length }}</p>
              <p class="text-sm text-gray-600">Usuarios Totales</p>
            </div>
            
            <div class="text-center p-4 bg-green-50 rounded-lg">
              <i class="pi pi-user text-3xl text-green-600 mb-2 block"></i>
              <p class="text-2xl font-bold text-green-700">
                {{ usuarios.filter(u => u.rol === 'Docente').length }}
              </p>
              <p class="text-sm text-gray-600">Docentes</p>
            </div>
            
            <div class="text-center p-4 bg-orange-50 rounded-lg">
              <i class="pi pi-shield text-3xl text-orange-600 mb-2 block"></i>
              <p class="text-2xl font-bold text-orange-700">
                {{ usuarios.filter(u => u.rol === 'Administrador').length }}
              </p>
              <p class="text-sm text-gray-600">Administradores</p>
            </div>
          </div>
        </template>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import MultiSelect from 'primevue/multiselect';
import Tag from 'primevue/tag';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';

const props = defineProps<{
  usuarios: any[];
}>();

const toast = useToast();
const enviando = ref(false);

// Formulario
const form = ref({
  destinatarios: [] as number[],
  titulo: '',
  mensaje: '',
});

// Opciones de usuarios
const usuariosOptions = computed(() =>
  props.usuarios.map(u => ({
    label: `${u.username} (${u.email})`,
    value: u.id,
    rol: u.rol.nombre,
  }))
);

// Validación
const formularioValido = computed(() => {
  return (
    form.value.destinatarios.length > 0 &&
    form.value.titulo.trim().length > 0 &&
    form.value.mensaje.trim().length > 0
  );
});

// Métodos
const seleccionarPorRol = (rol: string) => {
  form.value.destinatarios = props.usuarios
    .filter(u => u.rol.nombre === rol)
    .map(u => u.id);
};

const seleccionarTodos = () => {
  form.value.destinatarios = props.usuarios.map(u => u.id);
};

const limpiarFormulario = () => {
  form.value = {
    destinatarios: [],
    titulo: '',
    mensaje: '',
  };
};

const enviarNotificacion = async () => {
  if (!formularioValido.value) return;

  enviando.value = true;

  try {
    const response = await axios.post('/notificaciones/mensaje-masivo', form.value);

    toast.add({
      severity: 'success',
      summary: 'Éxito',
      detail: response.data.message,
      life: 5000,
    });

    limpiarFormulario();
  } catch (error: any) {
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: error.response?.data?.message || 'Error al enviar notificación',
      life: 5000,
    });
  } finally {
    enviando.value = false;
  }
};

const getRolIcon = (rol: string): string => {
  switch (rol) {
    case 'Administrador':
      return 'pi pi-shield text-orange-500';
    case 'Docente':
      return 'pi pi-user text-blue-500';
    default:
      return 'pi pi-user text-gray-500';
  }
};
</script>
