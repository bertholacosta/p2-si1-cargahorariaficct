<template>
  <AppLayout title="Configuración de Notificaciones">
    <div class="container mx-auto px-4 py-6 max-w-4xl">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">
          Configuración de Notificaciones
        </h1>
        <p class="text-sm text-gray-600 mt-1">
          Personaliza cómo y cuándo deseas recibir notificaciones
        </p>
      </div>

      <!-- Card de Configuración -->
      <Card>
        <template #title>
          <div class="flex items-center gap-2">
            <i class="pi pi-cog text-blue-600"></i>
            <span>Preferencias de Notificaciones</span>
          </div>
        </template>
        <template #content>
          <div class="space-y-6">
            <!-- Notificaciones de Inicio de Sesión -->
            <div class="border-b pb-6">
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <div class="flex items-center gap-2 mb-2">
                    <i class="pi pi-lock text-blue-600 text-xl"></i>
                    <h3 class="text-lg font-semibold text-gray-800">
                      Notificaciones de Inicio de Sesión
                    </h3>
                  </div>
                  <p class="text-sm text-gray-600 mb-3">
                    Recibe una notificación cada vez que alguien inicie sesión en tu cuenta.
                    Esto te ayuda a detectar accesos no autorizados.
                  </p>
                  <div class="bg-blue-50 border-l-4 border-blue-500 p-3 rounded">
                    <div class="flex items-start gap-2">
                      <i class="pi pi-info-circle text-blue-600 mt-0.5"></i>
                      <div class="text-sm text-blue-800">
                        <strong>Información incluida:</strong>
                        <ul class="list-disc list-inside mt-1 ml-2">
                          <li>Dirección IP del dispositivo</li>
                          <li>Navegador utilizado</li>
                          <li>Fecha y hora del acceso</li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="ml-6">
                  <InputSwitch
                    v-model="configuracion.notificaciones_inicio_sesion"
                    @change="actualizarConfiguracion"
                    :disabled="guardando"
                  />
                </div>
              </div>
            </div>

            <!-- Estado de las notificaciones -->
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="flex items-center gap-3">
                <i
                  :class="[
                    'text-2xl',
                    configuracion.notificaciones_inicio_sesion
                      ? 'pi pi-bell text-green-600'
                      : 'pi pi-bell-slash text-gray-400'
                  ]"
                ></i>
                <div>
                  <h4 class="font-semibold text-gray-800">
                    {{ estadoTexto }}
                  </h4>
                  <p class="text-sm text-gray-600">
                    {{ estadoDescripcion }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Información adicional -->
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
              <div class="flex gap-3">
                <i class="pi pi-exclamation-triangle text-yellow-600 text-xl"></i>
                <div class="flex-1">
                  <h4 class="font-semibold text-yellow-800 mb-1">
                    Recomendación de Seguridad
                  </h4>
                  <p class="text-sm text-yellow-700">
                    Te recomendamos mantener activadas las notificaciones de inicio de sesión
                    para una mayor seguridad de tu cuenta. Si recibes una notificación de un
                    inicio de sesión que no reconoces, contacta inmediatamente al administrador.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </template>
      </Card>

      <!-- Card de Historial (Opcional - para futuras notificaciones) -->
      <Card class="mt-6">
        <template #title>
          <div class="flex items-center gap-2">
            <i class="pi pi-list text-purple-600"></i>
            <span>Tipos de Notificaciones</span>
          </div>
        </template>
        <template #content>
          <div class="space-y-3">
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <div class="flex items-center gap-3">
                <i class="pi pi-lock text-blue-600"></i>
                <div>
                  <div class="font-medium text-gray-800">Inicio de Sesión</div>
                  <div class="text-sm text-gray-600">Accesos a tu cuenta</div>
                </div>
              </div>
              <Tag
                :value="configuracion.notificaciones_inicio_sesion ? 'Activado' : 'Desactivado'"
                :severity="configuracion.notificaciones_inicio_sesion ? 'success' : 'secondary'"
              />
            </div>

            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <div class="flex items-center gap-3">
                <i class="pi pi-bell text-orange-600"></i>
                <div>
                  <div class="font-medium text-gray-800">Recordatorios de Clase</div>
                  <div class="text-sm text-gray-600">Avisos de clases próximas</div>
                </div>
              </div>
              <Tag value="Siempre activado" severity="info" />
            </div>

            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <div class="flex items-center gap-3">
                <i class="pi pi-envelope text-purple-600"></i>
                <div>
                  <div class="font-medium text-gray-800">Mensajes del Administrador</div>
                  <div class="text-sm text-gray-600">Comunicaciones importantes</div>
                </div>
              </div>
              <Tag value="Siempre activado" severity="info" />
            </div>

            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <div class="flex items-center gap-3">
                <i class="pi pi-calendar text-green-600"></i>
                <div>
                  <div class="font-medium text-gray-800">Asistencias</div>
                  <div class="text-sm text-gray-600">Recordatorios de asistencia</div>
                </div>
              </div>
              <Tag value="Siempre activado" severity="info" />
            </div>
          </div>

          <Divider />

          <p class="text-sm text-gray-500 text-center">
            <i class="pi pi-info-circle mr-1"></i>
            Algunas notificaciones críticas no pueden ser desactivadas por razones de seguridad y funcionalidad del sistema.
          </p>
        </template>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Card from 'primevue/card';
import InputSwitch from 'primevue/inputswitch';
import Tag from 'primevue/tag';
import Divider from 'primevue/divider';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const guardando = ref(false);

// Configuración
const configuracion = ref({
  notificaciones_inicio_sesion: true,
});

// Textos dinámicos
const estadoTexto = computed(() => {
  return configuracion.value.notificaciones_inicio_sesion
    ? 'Notificaciones Activas'
    : 'Notificaciones Desactivadas';
});

const estadoDescripcion = computed(() => {
  return configuracion.value.notificaciones_inicio_sesion
    ? 'Recibirás notificaciones cuando alguien inicie sesión en tu cuenta'
    : 'No recibirás notificaciones de inicio de sesión';
});

// Métodos
const cargarConfiguracion = async () => {
  try {
    const response = await axios.get('/notificaciones/configuracion');
    configuracion.value = response.data;
  } catch (error) {
    console.error('Error al cargar configuración:', error);
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'No se pudo cargar la configuración',
      life: 3000,
    });
  }
};

const actualizarConfiguracion = async () => {
  guardando.value = true;

  try {
    const response = await axios.put('/notificaciones/configuracion', configuracion.value);

    toast.add({
      severity: 'success',
      summary: 'Guardado',
      detail: response.data.message,
      life: 3000,
    });
  } catch (error: any) {
    console.error('Error al actualizar configuración:', error);
    
    // Revertir el cambio en caso de error
    configuracion.value.notificaciones_inicio_sesion = !configuracion.value.notificaciones_inicio_sesion;
    
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: error.response?.data?.message || 'No se pudo actualizar la configuración',
      life: 3000,
    });
  } finally {
    guardando.value = false;
  }
};

// Cargar configuración al montar
onMounted(() => {
  cargarConfiguracion();
});
</script>

<style scoped>
:deep(.p-card-title) {
  font-size: 1.1rem;
  margin-bottom: 0;
}

:deep(.p-card-content) {
  padding-top: 1rem;
}
</style>
