<template>
  <div class="notificaciones-panel">
    <!-- Badge con contador -->
    <div class="relative">
      <Button
        icon="pi pi-bell"
        text
        rounded
        @click="togglePanel"
        severity="secondary"
        class="relative"
      />
      <Badge
        v-if="contadorNoLeidas > 0"
        :value="contadorNoLeidas"
        severity="danger"
        class="absolute -top-1 -right-1 min-w-[1.25rem] h-5 flex items-center justify-center"
      />
    </div>

    <!-- Panel deslizable -->
    <Sidebar
      v-model:visible="panelVisible"
      position="right"
      class="w-full md:w-[28rem]"
      :modal="true"
    >
      <template #header>
        <div class="flex items-center justify-between w-full pr-4">
          <div class="flex items-center gap-2">
            <i class="pi pi-bell text-xl"></i>
            <span class="font-semibold text-lg">Notificaciones</span>
            <Badge
              v-if="contadorNoLeidas > 0"
              :value="contadorNoLeidas"
              severity="danger"
            />
          </div>
        </div>
      </template>

      <!-- Filtros -->
      <div class="mb-4 flex gap-2">
        <Button
          label="Todas"
          :severity="filtro === 'todas' ? 'primary' : 'secondary'"
          @click="filtro = 'todas'; cargarNotificaciones()"
          size="small"
          outlined
        />
        <Button
          label="No leídas"
          :severity="filtro === 'no_leidas' ? 'primary' : 'secondary'"
          @click="filtro = 'no_leidas'; cargarNotificaciones()"
          size="small"
          outlined
        />
      </div>

      <!-- Acción rápida -->
      <div v-if="contadorNoLeidas > 0" class="mb-4">
        <Button
          label="Marcar todas como leídas"
          icon="pi pi-check"
          @click="marcarTodasComoLeidas"
          severity="info"
          outlined
          size="small"
          class="w-full"
        />
      </div>

      <!-- Lista de notificaciones -->
      <div v-if="cargando" class="text-center py-8">
        <ProgressSpinner style="width: 50px; height: 50px" />
        <p class="text-sm text-gray-500 mt-2">Cargando notificaciones...</p>
      </div>

      <ScrollPanel v-else class="h-[calc(100vh-200px)]">
        <div v-if="notificaciones.length === 0" class="text-center py-12">
          <i class="pi pi-inbox text-6xl text-gray-300 mb-4 block"></i>
          <p class="text-gray-500">No tienes notificaciones</p>
        </div>

        <div v-else class="space-y-2">
          <div
            v-for="notificacion in notificaciones"
            :key="notificacion.id"
            class="notificacion-item p-3 rounded-lg border transition-all hover:shadow-md cursor-pointer"
            :class="{
              'bg-blue-50 border-blue-200': !notificacion.leida,
              'bg-white border-gray-200': notificacion.leida
            }"
            @click="marcarComoLeida(notificacion)"
          >
            <div class="flex items-start gap-3">
              <!-- Ícono -->
              <div
                class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center"
                :class="getColorClass(notificacion.tipo)"
              >
                <i :class="getIcono(notificacion.tipo)" class="text-white"></i>
              </div>

              <!-- Contenido -->
              <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2 mb-1">
                  <h4 class="font-semibold text-sm text-gray-900 leading-tight">
                    {{ notificacion.titulo }}
                  </h4>
                  <Button
                    icon="pi pi-times"
                    text
                    rounded
                    size="small"
                    severity="danger"
                    @click.stop="eliminarNotificacion(notificacion.id)"
                    class="flex-shrink-0"
                  />
                </div>

                <p class="text-sm text-gray-700 mb-2 leading-snug">
                  {{ notificacion.mensaje }}
                </p>

                <div class="flex items-center justify-between text-xs text-gray-500">
                  <span>{{ formatearFecha(notificacion.created_at) }}</span>
                  <Badge
                    v-if="!notificacion.leida"
                    value="Nueva"
                    severity="info"
                    size="small"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
      </ScrollPanel>
    </Sidebar>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import Button from 'primevue/button';
import Badge from 'primevue/badge';
import Sidebar from 'primevue/sidebar';
import ScrollPanel from 'primevue/scrollpanel';
import ProgressSpinner from 'primevue/progressspinner';
import axios from 'axios';
import { formatDistanceToNow } from 'date-fns';
import { es } from 'date-fns/locale';

interface Notificacion {
  id: number;
  tipo: string;
  titulo: string;
  mensaje: string;
  leida: boolean;
  created_at: string;
  datos?: any;
}

// Estado
const panelVisible = ref(false);
const notificaciones = ref<Notificacion[]>([]);
const contadorNoLeidas = ref(0);
const cargando = ref(false);
const filtro = ref<'todas' | 'no_leidas'>('todas');
let intervalId: number | null = null;

// Métodos
const togglePanel = () => {
  panelVisible.value = !panelVisible.value;
  if (panelVisible.value) {
    cargarNotificaciones();
  }
};

const cargarNotificaciones = async () => {
  cargando.value = true;
  try {
    const response = await axios.get('/notificaciones', {
      params: {
        solo_no_leidas: filtro.value === 'no_leidas',
        limite: 50,
      },
    });

    notificaciones.value = response.data.notificaciones;
    contadorNoLeidas.value = response.data.no_leidas;
  } catch (error) {
    console.error('Error al cargar notificaciones:', error);
  } finally {
    cargando.value = false;
  }
};

const actualizarContador = async () => {
  try {
    const response = await axios.get('/notificaciones/contador');
    contadorNoLeidas.value = response.data.no_leidas;
  } catch (error) {
    console.error('Error al actualizar contador:', error);
  }
};

const marcarComoLeida = async (notificacion: Notificacion) => {
  if (!notificacion.leida) {
    try {
      await axios.post(`/notificaciones/${notificacion.id}/leer`);
      notificacion.leida = true;
      contadorNoLeidas.value = Math.max(0, contadorNoLeidas.value - 1);
    } catch (error) {
      console.error('Error al marcar como leída:', error);
    }
  }
};

const marcarTodasComoLeidas = async () => {
  try {
    await axios.post('/notificaciones/leer-todas');
    notificaciones.value.forEach(n => {
      n.leida = true;
    });
    contadorNoLeidas.value = 0;
  } catch (error) {
    console.error('Error al marcar todas como leídas:', error);
  }
};

const eliminarNotificacion = async (id: number) => {
  try {
    await axios.delete(`/notificaciones/${id}`);
    notificaciones.value = notificaciones.value.filter(n => n.id !== id);
    actualizarContador();
  } catch (error) {
    console.error('Error al eliminar notificación:', error);
  }
};

const getIcono = (tipo: string): string => {
  switch (tipo) {
    case 'recordatorio_clase':
      return 'pi pi-calendar';
    case 'mensaje_admin':
      return 'pi pi-megaphone';
    case 'inicio_sesion':
      return 'pi pi-sign-in';
    case 'asistencia':
      return 'pi pi-check-circle';
    default:
      return 'pi pi-bell';
  }
};

const getColorClass = (tipo: string): string => {
  switch (tipo) {
    case 'recordatorio_clase':
      return 'bg-blue-500';
    case 'mensaje_admin':
      return 'bg-orange-500';
    case 'inicio_sesion':
      return 'bg-green-500';
    case 'asistencia':
      return 'bg-indigo-500';
    default:
      return 'bg-gray-500';
  }
};

const formatearFecha = (fecha: string): string => {
  return formatDistanceToNow(new Date(fecha), { addSuffix: true, locale: es });
};

// Polling para actualizar el contador cada 30 segundos
onMounted(() => {
  actualizarContador();
  intervalId = window.setInterval(actualizarContador, 30000);
});

onUnmounted(() => {
  if (intervalId) {
    clearInterval(intervalId);
  }
});
</script>

<style scoped>
.notificacion-item {
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
