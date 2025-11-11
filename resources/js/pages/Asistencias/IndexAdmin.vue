<template>
  <AppLayout title="Gestión de Asistencias">
    <div class="container mx-auto px-4 py-6 max-w-7xl">
      <!-- Header -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">Gestión de Asistencias</h1>
          <p class="text-sm text-gray-600 mt-1">Panel de administración</p>
        </div>
        
        <div class="flex gap-2">
          <Button
            label="Días No Laborables"
            icon="pi pi-calendar-times"
            @click="irADiasNoLaborables"
            outlined
          />
          <Button
            label="Exportar Reporte"
            icon="pi pi-download"
            @click="exportarReporte"
            severity="success"
          />
        </div>
      </div>

      <!-- Filtros -->
      <Card class="mb-6">
        <template #content>
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Gestión *</label>
              <Select
                v-model="filtros.id_gestion"
                :options="gestiones"
                optionLabel="label"
                optionValue="id"
                placeholder="Seleccionar gestión"
                class="w-full"
                @change="alCambiarGestion"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Docente</label>
              <Select
                v-model="filtros.codigo_docente"
                :options="docentesOptions"
                optionLabel="label"
                optionValue="value"
                placeholder="Todos los docentes"
                class="w-full"
                :filter="true"
                showClear
                @change="aplicarFiltros"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Inicio</label>
              <DatePicker
                v-model="filtros.fecha_inicio"
                placeholder="Fecha inicio"
                dateFormat="dd/mm/yy"
                class="w-full"
                showIcon
                :disabled="true"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Fin</label>
              <DatePicker
                v-model="filtros.fecha_fin"
                placeholder="Fecha fin"
                dateFormat="dd/mm/yy"
                class="w-full"
                showIcon
                :disabled="true"
              />
            </div>
          </div>

          <div class="flex gap-2 mt-4">
            <Button
              label="Buscar"
              icon="pi pi-search"
              @click="buscarAsistencias"
              :loading="cargando"
            />
            <Button
              label="Limpiar Filtros"
              icon="pi pi-filter-slash"
              @click="limpiarFiltros"
              severity="secondary"
              outlined
            />
          </div>
        </template>
      </Card>

      <!-- Estadísticas Generales -->
      <div v-if="estadisticasGenerales" class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <Card class="bg-gradient-to-br from-blue-500 to-blue-600 text-white">
          <template #content>
            <div class="text-center">
              <i class="pi pi-calendar text-3xl mb-2 block opacity-80"></i>
              <p class="text-2xl font-bold">{{ estadisticasGenerales.total }}</p>
              <p class="text-sm opacity-90">Total Registros</p>
            </div>
          </template>
        </Card>

        <Card class="bg-gradient-to-br from-green-500 to-green-600 text-white">
          <template #content>
            <div class="text-center">
              <i class="pi pi-check-circle text-3xl mb-2 block opacity-80"></i>
              <p class="text-2xl font-bold">{{ estadisticasGenerales.presentes }}</p>
              <p class="text-sm opacity-90">Presentes</p>
            </div>
          </template>
        </Card>

        <Card class="bg-gradient-to-br from-red-500 to-red-600 text-white">
          <template #content>
            <div class="text-center">
              <i class="pi pi-times-circle text-3xl mb-2 block opacity-80"></i>
              <p class="text-2xl font-bold">{{ estadisticasGenerales.faltas }}</p>
              <p class="text-sm opacity-90">Faltas</p>
            </div>
          </template>
        </Card>

        <Card class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white">
          <template #content>
            <div class="text-center">
              <i class="pi pi-file-edit text-3xl mb-2 block opacity-80"></i>
              <p class="text-2xl font-bold">{{ estadisticasGenerales.justificadas }}</p>
              <p class="text-sm opacity-90">Justificadas</p>
            </div>
          </template>
        </Card>

        <Card class="bg-gradient-to-br from-indigo-500 to-indigo-600 text-white">
          <template #content>
            <div class="text-center">
              <i class="pi pi-percentage text-3xl mb-2 block opacity-80"></i>
              <p class="text-2xl font-bold">{{ estadisticasGenerales.porcentaje }}%</p>
              <p class="text-sm opacity-90">Asistencia</p>
            </div>
          </template>
        </Card>
      </div>

      <!-- Tabla de Asistencias -->
      <Card>
        <template #title>
          <div class="flex items-center gap-2">
            <i class="pi pi-list text-blue-600"></i>
            <span>Registros de Asistencia</span>
          </div>
        </template>
        <template #content>
          <DataTable
            v-model:selection="asistenciasSeleccionadas"
            :value="asistencias"
            :paginator="true"
            :rows="15"
            :rowsPerPageOptions="[15, 30, 50]"
            stripedRows
            showGridlines
            :loading="cargando"
            responsiveLayout="stack"
            breakpoint="768px"
            :globalFilterFields="['docente.nombre', 'docente.apellidos', 'asignacion.grupo_materia.materia.nombre']"
          >
            <template #header>
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">
                  {{ asistencias.length }} registros encontrados
                </span>
                <IconField iconPosition="left">
                  <InputIcon class="pi pi-search" />
                  <InputText
                    v-model="filtroGlobal"
                    placeholder="Buscar..."
                    size="small"
                  />
                </IconField>
              </div>
            </template>

            <Column selectionMode="multiple" :style="{ width: '3rem' }" :exportable="false" />
            
            <Column field="fecha" header="Fecha" :sortable="true">
              <template #body="slotProps">
                {{ formatearFecha(slotProps.data.fecha, 'DD/MM/YYYY') }}
              </template>
            </Column>
            
            <Column field="docente.nombre" header="Docente" :sortable="true">
              <template #body="slotProps">
                {{ slotProps.data.docente.nombre }} {{ slotProps.data.docente.apellidos }}
              </template>
            </Column>
            
            <Column field="asignacion.grupo_materia.materia.nombre" header="Materia" :sortable="true" />
            
            <Column field="asignacion.grupo_materia.grupo.numero" header="Grupo" :sortable="true">
              <template #body="slotProps">
                Grupo {{ slotProps.data.asignacion.grupo_materia.grupo.numero }}
              </template>
            </Column>
            
            <Column field="asignacion.horario.hora.hora_inicio" header="Horario" :sortable="true">
              <template #body="slotProps">
                {{ slotProps.data.asignacion.horario.hora.hora_inicio }}-{{ slotProps.data.asignacion.horario.hora.hora_fin }}
              </template>
            </Column>
            
            <Column field="estado" header="Estado" :sortable="true">
              <template #body="slotProps">
                <Tag
                  :value="slotProps.data.estado"
                  :severity="getEstadoSeverity(slotProps.data.estado)"
                  :icon="getEstadoIcon(slotProps.data.estado)"
                />
              </template>
            </Column>
            
            <Column field="registrada_por_docente" header="Registro" :sortable="true">
              <template #body="slotProps">
                <Tag
                  :value="slotProps.data.registrada_por_docente ? 'Docente' : 'Sistema'"
                  :severity="slotProps.data.registrada_por_docente ? 'info' : 'secondary'"
                  size="small"
                />
              </template>
            </Column>
            
            <Column header="Acciones" :style="{ width: '120px' }">
              <template #body="slotProps">
                <div class="flex gap-1">
                  <Button
                    icon="pi pi-pencil"
                    text
                    rounded
                    severity="info"
                    @click="editarAsistencia(slotProps.data)"
                    v-tooltip.top="'Editar'"
                  />
                  <Button
                    v-if="slotProps.data.justificacion"
                    icon="pi pi-eye"
                    text
                    rounded
                    severity="success"
                    @click="verJustificacion(slotProps.data)"
                    v-tooltip.top="'Ver justificación'"
                  />
                </div>
              </template>
            </Column>

            <template #empty>
              <div class="text-center py-8 text-gray-500">
                <i class="pi pi-inbox text-5xl mb-3 block"></i>
                <p>No se encontraron registros de asistencia</p>
                <p class="text-sm mt-2">Ajusta los filtros para mostrar resultados</p>
              </div>
            </template>
          </DataTable>
        </template>
      </Card>

      <!-- Dialog para editar asistencia -->
      <Dialog
        v-model:visible="dialogEditarVisible"
        header="Editar Asistencia"
        :modal="true"
        class="w-[95vw] sm:w-[500px]"
      >
        <div v-if="asistenciaEditando" class="space-y-4">
          <div class="bg-gray-50 border rounded-lg p-3">
            <p class="text-sm"><strong>Docente:</strong> {{ asistenciaEditando.docente.nombre }} {{ asistenciaEditando.docente.apellidos }}</p>
            <p class="text-sm"><strong>Fecha:</strong> {{ formatearFecha(asistenciaEditando.fecha, 'DD/MM/YYYY') }}</p>
            <p class="text-sm"><strong>Materia:</strong> {{ asistenciaEditando.asignacion.grupo_materia.materia.nombre }}</p>
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Estado *</label>
            <Select
              v-model="formEditar.estado"
              :options="estadosOptions"
              optionLabel="label"
              optionValue="value"
              placeholder="Seleccionar estado"
              class="w-full"
            />
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Observación</label>
            <Textarea
              v-model="formEditar.observacion"
              rows="3"
              placeholder="Observaciones adicionales..."
              class="w-full"
            />
          </div>
        </div>

        <template #footer>
          <div class="flex gap-2">
            <Button
              label="Cancelar"
              @click="dialogEditarVisible = false"
              severity="secondary"
              outlined
            />
            <Button
              label="Guardar Cambios"
              icon="pi pi-check"
              @click="guardarEdicion"
              :loading="formEditar.processing"
            />
          </div>
        </template>
      </Dialog>

      <!-- Dialog para ver justificación -->
      <Dialog
        v-model:visible="dialogJustificacionVisible"
        header="Detalle de Justificación"
        :modal="true"
        class="w-[95vw] sm:w-[600px]"
      >
        <div v-if="justificacionVista" class="space-y-4">
          <div class="bg-gray-50 border rounded-lg p-3">
            <p class="text-sm"><strong>Docente:</strong> {{ justificacionVista.asistencia.docente.nombre }} {{ justificacionVista.asistencia.docente.apellidos }}</p>
            <p class="text-sm"><strong>Fecha de Falta:</strong> {{ formatearFecha(justificacionVista.asistencia.fecha, 'DD/MM/YYYY') }}</p>
            <p class="text-sm"><strong>Fecha de Justificación:</strong> {{ formatearFecha(justificacionVista.fecha_justificacion, 'DD/MM/YYYY HH:mm') }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Descripción:</label>
            <p class="text-sm text-gray-800 bg-gray-50 p-3 rounded border">
              {{ justificacionVista.descripcion }}
            </p>
          </div>

          <div v-if="justificacionVista.archivo">
            <label class="block text-sm font-medium text-gray-700 mb-2">Documento:</label>
            <Button
              label="Descargar documento"
              icon="pi pi-download"
              @click="descargarArchivo(justificacionVista.archivo)"
              outlined
              size="small"
            />
          </div>
        </div>

        <template #footer>
          <Button
            label="Cerrar"
            @click="dialogJustificacionVisible = false"
          />
        </template>
      </Dialog>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Select from 'primevue/select';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import DatePicker from 'primevue/datepicker';
import InputText from 'primevue/inputtext';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import { format } from 'date-fns';
import { es } from 'date-fns/locale';
import axios from 'axios';

const props = defineProps<{
  docentes: any[];
  gestiones: any[];
  gestionActual: any;
}>();

// Estado
const asistencias = ref<any[]>([]);
const asistenciasSeleccionadas = ref<any[]>([]);
const cargando = ref(false);
const filtroGlobal = ref('');
const dialogEditarVisible = ref(false);
const dialogJustificacionVisible = ref(false);
const asistenciaEditando = ref<any>(null);
const justificacionVista = ref<any>(null);

// Filtros
const filtros = ref({
  id_gestion: props.gestionActual?.id,
  codigo_docente: null as string | null,
  fecha_inicio: null as Date | null,
  fecha_fin: null as Date | null,
});

// Opciones
const docentesOptions = computed(() => [
  { label: 'Todos los docentes', value: null },
  ...props.docentes.map(d => ({
    label: `${d.nombre} ${d.apellidos}`,
    value: d.codigo,
  })),
]);

const estadosOptions = [
  { label: 'Presente', value: 'Presente' },
  { label: 'Falta', value: 'Falta' },
  { label: 'Justificada', value: 'Justificada' },
  { label: 'Licencia', value: 'Licencia' },
];

// Estadísticas computadas
const estadisticasGenerales = computed(() => {
  if (asistencias.value.length === 0) return null;
  
  const total = asistencias.value.length;
  const presentes = asistencias.value.filter(a => a.estado === 'Presente').length;
  const faltas = asistencias.value.filter(a => a.estado === 'Falta').length;
  const justificadas = asistencias.value.filter(a => a.estado === 'Justificada').length;
  const porcentaje = total > 0 ? Math.round((presentes / total) * 100) : 0;
  
  return { total, presentes, faltas, justificadas, porcentaje };
});

// Formulario de edición
const formEditar = useForm({
  estado: '',
  observacion: '',
});

// Métodos
const buscarAsistencias = async () => {
  cargando.value = true;
  
  try {
    const params: any = {
      id_gestion: filtros.value.id_gestion,
    };
    
    if (filtros.value.codigo_docente) {
      params.codigo_docente = filtros.value.codigo_docente;
    }
    
    if (filtros.value.fecha_inicio) {
      params.fecha_inicio = format(filtros.value.fecha_inicio, 'yyyy-MM-dd');
    }
    
    if (filtros.value.fecha_fin) {
      params.fecha_fin = format(filtros.value.fecha_fin, 'yyyy-MM-dd');
    }
    
    const response = await axios.get('/asistencias/reporte', { params });
    asistencias.value = response.data.asistencias;
  } catch (error) {
    console.error('Error al buscar asistencias:', error);
  } finally {
    cargando.value = false;
  }
};

const alCambiarGestion = () => {
  const gestionSeleccionada = props.gestiones.find(g => g.id === filtros.value.id_gestion);
  
  if (gestionSeleccionada) {
    // Establecer fechas según la gestión
    filtros.value.fecha_inicio = new Date(gestionSeleccionada.fecha_inicio);
    filtros.value.fecha_fin = new Date(gestionSeleccionada.fecha_fin);
  } else {
    filtros.value.fecha_inicio = null;
    filtros.value.fecha_fin = null;
  }
};

const aplicarFiltros = () => {
  // Se aplica al hacer click en buscar
};

const limpiarFiltros = () => {
  filtros.value = {
    id_gestion: props.gestionActual?.id,
    codigo_docente: null,
    fecha_inicio: null,
    fecha_fin: null,
  };
  asistencias.value = [];
};

const editarAsistencia = (asistencia: any) => {
  asistenciaEditando.value = asistencia;
  formEditar.reset();
  formEditar.estado = asistencia.estado;
  formEditar.observacion = asistencia.observacion || '';
  dialogEditarVisible.value = true;
};

const guardarEdicion = () => {
  if (!asistenciaEditando.value) return;

  formEditar.put(`/asistencias/${asistenciaEditando.value.id}`, {
    onSuccess: () => {
      dialogEditarVisible.value = false;
      buscarAsistencias(); // Recargar datos
    },
  });
};

const verJustificacion = (asistencia: any) => {
  justificacionVista.value = asistencia.justificacion;
  justificacionVista.value.asistencia = asistencia;
  dialogJustificacionVisible.value = true;
};

const descargarArchivo = (ruta: string) => {
  window.open(`/storage/${ruta}`, '_blank');
};

const irADiasNoLaborables = () => {
  router.visit('/dias-no-laborables');
};

const exportarReporte = () => {
  // Implementar exportación
  console.log('Exportar reporte');
};

const formatearFecha = (fecha: string, formato: string = 'DD/MM/YYYY') => {
  if (!fecha) return '-';
  return format(new Date(fecha), formato, { locale: es });
};

const getEstadoSeverity = (estado: string) => {
  switch (estado) {
    case 'Presente':
      return 'success';
    case 'Falta':
      return 'danger';
    case 'Justificada':
      return 'warn';
    case 'Licencia':
      return 'info';
    default:
      return 'secondary';
  }
};

const getEstadoIcon = (estado: string) => {
  switch (estado) {
    case 'Presente':
      return 'pi pi-check';
    case 'Falta':
      return 'pi pi-times';
    case 'Justificada':
      return 'pi pi-file-edit';
    case 'Licencia':
      return 'pi pi-file';
    default:
      return '';
  }
};

// Cargar datos iniciales si hay gestión seleccionada
onMounted(() => {
  if (filtros.value.id_gestion) {
    alCambiarGestion(); // Establecer fechas de la gestión actual
    buscarAsistencias();
  }
});
</script>
