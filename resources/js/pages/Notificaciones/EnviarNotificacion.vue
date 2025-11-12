<template>
  <AppLayout title="Enviar Notificación">
    <div class="container mx-auto px-4 py-6 max-w-5xl">
      <!-- Header -->
      <div class="mb-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-800">
              Enviar Notificación a Usuario
            </h1>
            <p class="text-sm text-gray-600 mt-1">
              Envía un mensaje personalizado a un usuario específico
            </p>
          </div>
          <Button
            label="Volver"
            icon="pi pi-arrow-left"
            @click="$inertia.visit('/notificaciones/gestion')"
            severity="secondary"
            outlined
          />
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Formulario de envío -->
        <div class="lg:col-span-2">
          <Card>
            <template #title>
              <div class="flex items-center gap-2">
                <i class="pi pi-send text-blue-600"></i>
                <span>Datos del Mensaje</span>
              </div>
            </template>
            <template #content>
              <div class="space-y-4">
                <!-- Selección de usuario -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Seleccionar Usuario *
                  </label>
                  <Dropdown
                    v-model="form.id_usuario"
                    :options="usuarios"
                    optionLabel="username"
                    optionValue="id"
                    placeholder="Buscar y seleccionar usuario..."
                    :filter="true"
                    filterPlaceholder="Buscar por nombre o email..."
                    class="w-full"
                    emptyMessage="No se encontraron usuarios"
                    @change="onUsuarioSeleccionado"
                  >
                    <template #value="slotProps">
                      <div v-if="slotProps.value && usuarioSeleccionado" class="flex items-center gap-3">
                        <Avatar
                          :label="usuarioSeleccionado.username.charAt(0).toUpperCase()"
                          :class="getRolColor(usuarioSeleccionado.rol)"
                          shape="circle"
                        />
                        <div>
                          <div class="font-medium">{{ usuarioSeleccionado.username }}</div>
                          <div class="text-xs text-gray-500">{{ usuarioSeleccionado.email }}</div>
                        </div>
                      </div>
                      <span v-else class="text-gray-400">{{ slotProps.placeholder }}</span>
                    </template>
                    <template #option="slotProps">
                      <div class="flex items-center gap-3 py-2">
                        <Avatar
                          :label="slotProps.option.username.charAt(0).toUpperCase()"
                          :class="getRolColor(slotProps.option.rol)"
                          shape="circle"
                        />
                        <div class="flex-1">
                          <div class="font-medium">{{ slotProps.option.username }}</div>
                          <div class="text-xs text-gray-500">
                            {{ slotProps.option.email }}
                          </div>
                        </div>
                        <Tag :value="slotProps.option.rol" :severity="getRolSeverity(slotProps.option.rol)" />
                      </div>
                    </template>
                  </Dropdown>
                </div>

                <!-- Título -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Título del Mensaje *
                  </label>
                  <InputText
                    v-model="form.titulo"
                    placeholder="Ej: Actualización importante del sistema"
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
                    rows="8"
                    placeholder="Escribe aquí el mensaje que deseas enviar al usuario..."
                    class="w-full"
                  />
                  <small class="text-gray-500">
                    {{ form.mensaje.length }} caracteres
                  </small>
                </div>

                <!-- Plantillas rápidas -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Plantillas Rápidas
                  </label>
                  <div class="flex flex-wrap gap-2">
                    <Button
                      label="Recordatorio"
                      icon="pi pi-bell"
                      @click="aplicarPlantilla('recordatorio')"
                      size="small"
                      outlined
                    />
                    <Button
                      label="Aviso"
                      icon="pi pi-info-circle"
                      @click="aplicarPlantilla('aviso')"
                      size="small"
                      outlined
                    />
                    <Button
                      label="Urgente"
                      icon="pi pi-exclamation-triangle"
                      @click="aplicarPlantilla('urgente')"
                      size="small"
                      outlined
                    />
                    <Button
                      label="Felicitaciones"
                      icon="pi pi-star"
                      @click="aplicarPlantilla('felicitaciones')"
                      size="small"
                      outlined
                    />
                  </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex gap-3 pt-4 border-t">
                  <Button
                    label="Enviar Notificación"
                    icon="pi pi-send"
                    @click="enviarNotificacion"
                    :loading="enviando"
                    :disabled="!formularioValido"
                  />
                  <Button
                    label="Limpiar"
                    icon="pi pi-refresh"
                    @click="limpiarFormulario"
                    severity="secondary"
                    outlined
                  />
                  <Button
                    label="Vista Previa"
                    icon="pi pi-eye"
                    @click="mostrarVistaPrevia = true"
                    severity="info"
                    outlined
                    :disabled="!formularioValido"
                  />
                </div>
              </div>
            </template>
          </Card>
        </div>

        <!-- Panel lateral con información -->
        <div class="space-y-4">
          <!-- Información del usuario seleccionado -->
          <Card v-if="usuarioSeleccionado">
            <template #title>
              <span class="text-sm font-medium">Usuario Seleccionado</span>
            </template>
            <template #content>
              <div class="space-y-3">
                <div class="flex items-center gap-3">
                  <Avatar
                    :label="usuarioSeleccionado.username.charAt(0).toUpperCase()"
                    :class="getRolColor(usuarioSeleccionado.rol)"
                    size="large"
                    shape="circle"
                  />
                  <div>
                    <div class="font-bold">{{ usuarioSeleccionado.username }}</div>
                    <div class="text-xs text-gray-500">{{ usuarioSeleccionado.email }}</div>
                  </div>
                </div>
                
                <Divider />
                
                <div>
                  <div class="text-xs text-gray-500 mb-1">Rol</div>
                  <Tag
                    :value="usuarioSeleccionado.rol"
                    :severity="getRolSeverity(usuarioSeleccionado.rol)"
                  />
                </div>
                
                <div v-if="usuarioSeleccionado.docente">
                  <div class="text-xs text-gray-500 mb-1">Nombre Completo</div>
                  <div class="font-medium">
                    {{ usuarioSeleccionado.docente.nombre }}
                    {{ usuarioSeleccionado.docente.apellido }}
                  </div>
                </div>
              </div>
            </template>
          </Card>

          <!-- Ayuda -->
          <Card>
            <template #title>
              <div class="flex items-center gap-2 text-sm">
                <i class="pi pi-info-circle text-blue-600"></i>
                <span>Consejos</span>
              </div>
            </template>
            <template #content>
              <ul class="space-y-2 text-sm text-gray-600">
                <li class="flex gap-2">
                  <i class="pi pi-check text-green-500 mt-0.5"></i>
                  <span>Sé claro y conciso en tu mensaje</span>
                </li>
                <li class="flex gap-2">
                  <i class="pi pi-check text-green-500 mt-0.5"></i>
                  <span>Usa un título descriptivo</span>
                </li>
                <li class="flex gap-2">
                  <i class="pi pi-check text-green-500 mt-0.5"></i>
                  <span>Verifica el destinatario antes de enviar</span>
                </li>
                <li class="flex gap-2">
                  <i class="pi pi-check text-green-500 mt-0.5"></i>
                  <span>Las notificaciones son permanentes</span>
                </li>
              </ul>
            </template>
          </Card>
        </div>
      </div>
    </div>

    <!-- Dialog de Vista Previa -->
    <Dialog
      v-model:visible="mostrarVistaPrevia"
      modal
      header="Vista Previa de la Notificación"
      :style="{ width: '500px' }"
    >
      <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
        <div class="flex items-start gap-3">
          <i class="pi pi-bell text-blue-600 text-xl mt-1"></i>
          <div class="flex-1">
            <h4 class="font-bold text-gray-800 mb-2">{{ form.titulo }}</h4>
            <p class="text-gray-700 whitespace-pre-wrap">{{ form.mensaje }}</p>
            <div class="mt-3 text-xs text-gray-500">
              <i class="pi pi-clock mr-1"></i>
              Enviado: Ahora
            </div>
          </div>
        </div>
      </div>
      
      <template #footer>
        <Button
          label="Cerrar"
          icon="pi pi-times"
          @click="mostrarVistaPrevia = false"
          severity="secondary"
          outlined
        />
      </template>
    </Dialog>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';
import Avatar from 'primevue/avatar';
import Tag from 'primevue/tag';
import Divider from 'primevue/divider';
import Dialog from 'primevue/dialog';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';

interface Usuario {
  id: number;
  username: string;
  email: string;
  rol: string;
  docente?: {
    nombre: string;
    apellido: string;
  } | null;
}

const props = defineProps<{
  usuarios: Usuario[];
}>();

const toast = useToast();
const enviando = ref(false);
const mostrarVistaPrevia = ref(false);

// Formulario
const form = ref({
  id_usuario: null as number | null,
  titulo: '',
  mensaje: '',
});

// Usuario seleccionado
const usuarioSeleccionado = computed(() => {
  if (!form.value.id_usuario) return null;
  return props.usuarios.find(u => u.id === form.value.id_usuario) || null;
});

// Validación
const formularioValido = computed(() => {
  return (
    form.value.id_usuario !== null &&
    form.value.titulo.trim().length > 0 &&
    form.value.mensaje.trim().length > 0
  );
});

// Métodos
const onUsuarioSeleccionado = () => {
  // Se puede agregar lógica adicional aquí
};

const aplicarPlantilla = (tipo: string) => {
  const plantillas: Record<string, { titulo: string; mensaje: string }> = {
    recordatorio: {
      titulo: '🔔 Recordatorio Importante',
      mensaje: 'Este es un recordatorio sobre...',
    },
    aviso: {
      titulo: 'ℹ️ Aviso',
      mensaje: 'Te informamos que...',
    },
    urgente: {
      titulo: '⚠️ Atención Urgente',
      mensaje: 'Necesitamos tu atención inmediata sobre...',
    },
    felicitaciones: {
      titulo: '⭐ Felicitaciones',
      mensaje: '¡Excelente trabajo! Te felicitamos por...',
    },
  };

  if (plantillas[tipo]) {
    form.value.titulo = plantillas[tipo].titulo;
    form.value.mensaje = plantillas[tipo].mensaje;
  }
};

const limpiarFormulario = () => {
  form.value = {
    id_usuario: null,
    titulo: '',
    mensaje: '',
  };
};

const enviarNotificacion = async () => {
  if (!formularioValido.value) return;

  enviando.value = true;

  try {
    const response = await axios.post('/notificaciones/enviar-a-usuario', form.value);

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

const getRolColor = (rol: string): string => {
  switch (rol) {
    case 'Administrador':
      return 'bg-orange-500 text-white';
    case 'Docente':
      return 'bg-blue-500 text-white';
    default:
      return 'bg-gray-500 text-white';
  }
};

const getRolSeverity = (rol: string): 'success' | 'info' | 'warn' | 'danger' | 'secondary' | 'contrast' | undefined => {
  switch (rol) {
    case 'Administrador':
      return 'warn';
    case 'Docente':
      return 'info';
    default:
      return 'secondary';
  }
};
</script>

<style scoped>
:deep(.p-dropdown-filter) {
  width: 100%;
}
</style>
