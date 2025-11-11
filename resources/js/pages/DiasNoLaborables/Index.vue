<template>
  <AppLayout title="Días No Laborables">
    <div class="container mx-auto px-4 py-6 max-w-7xl">
      <!-- Header -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">Gestión de Días No Laborables</h1>
          <p class="text-sm text-gray-600 mt-1">Feriados, vacaciones y suspensiones de clases</p>
        </div>
        
        <Button
          label="Nuevo Día No Laborable"
          icon="pi pi-plus"
          @click="abrirDialogNuevo"
          severity="success"
        />
      </div>

      <!-- Filtros -->
      <Card class="mb-6">
        <template #content>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Gestión</label>
              <Select
                v-model="gestionSeleccionada"
                :options="gestionesOptions"
                optionLabel="label"
                optionValue="value"
                placeholder="Todas las gestiones"
                class="w-full"
                showClear
                @change="cargarDias"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
              <Select
                v-model="tipoFiltro"
                :options="tiposOptions"
                optionLabel="label"
                optionValue="value"
                placeholder="Todos los tipos"
                class="w-full"
                showClear
                @change="aplicarFiltros"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
              <Select
                v-model="estadoFiltro"
                :options="estadosOptions"
                optionLabel="label"
                optionValue="value"
                placeholder="Todos"
                class="w-full"
                showClear
                @change="aplicarFiltros"
              />
            </div>
          </div>
        </template>
      </Card>

      <!-- Tarjetas de Estadísticas -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <Card class="bg-gradient-to-br from-blue-500 to-blue-600 text-white">
          <template #content>
            <div class="text-center">
              <i class="pi pi-calendar-times text-3xl mb-2 block opacity-80"></i>
              <p class="text-2xl font-bold">{{ estadisticas.total }}</p>
              <p class="text-sm opacity-90">Total</p>
            </div>
          </template>
        </Card>

        <Card class="bg-gradient-to-br from-red-500 to-red-600 text-white">
          <template #content>
            <div class="text-center">
              <i class="pi pi-flag text-3xl mb-2 block opacity-80"></i>
              <p class="text-2xl font-bold">{{ estadisticas.feriados }}</p>
              <p class="text-sm opacity-90">Feriados</p>
            </div>
          </template>
        </Card>

        <Card class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white">
          <template #content>
            <div class="text-center">
              <i class="pi pi-sun text-3xl mb-2 block opacity-80"></i>
              <p class="text-2xl font-bold">{{ estadisticas.vacaciones }}</p>
              <p class="text-sm opacity-90">Vacaciones</p>
            </div>
          </template>
        </Card>

        <Card class="bg-gradient-to-br from-purple-500 to-purple-600 text-white">
          <template #content>
            <div class="text-center">
              <i class="pi pi-ban text-3xl mb-2 block opacity-80"></i>
              <p class="text-2xl font-bold">{{ estadisticas.suspensiones }}</p>
              <p class="text-sm opacity-90">Suspensiones</p>
            </div>
          </template>
        </Card>
      </div>

      <!-- Tabla de Días No Laborables -->
      <Card>
        <template #title>
          <div class="flex items-center gap-2">
            <i class="pi pi-list text-blue-600"></i>
            <span>Días Registrados</span>
          </div>
        </template>
        <template #content>
          <DataTable
            :value="diasFiltrados"
            :paginator="true"
            :rows="15"
            stripedRows
            showGridlines
            responsiveLayout="stack"
            breakpoint="768px"
            :globalFilterFields="['descripcion']"
          >
            <template #header>
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">
                  {{ diasFiltrados.length }} días registrados
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

            <Column field="fecha" header="Fecha" :sortable="true">
              <template #body="slotProps">
                <div class="flex items-center gap-2">
                  <i class="pi pi-calendar text-blue-600"></i>
                  {{ formatearFecha(slotProps.data.fecha, 'DD/MM/YYYY - EEEE') }}
                </div>
              </template>
            </Column>
            
            <Column field="descripcion" header="Descripción" :sortable="true">
              <template #body="slotProps">
                <div>
                  <p class="font-medium text-gray-800">{{ slotProps.data.descripcion }}</p>
                </div>
              </template>
            </Column>
            
            <Column field="tipo" header="Tipo" :sortable="true">
              <template #body="slotProps">
                <Tag
                  :value="slotProps.data.tipo"
                  :severity="getTipoSeverity(slotProps.data.tipo)"
                  :icon="getTipoIcon(slotProps.data.tipo)"
                />
              </template>
            </Column>
            
            <Column field="gestion" header="Gestión" :sortable="true">
              <template #body="slotProps">
                <span v-if="slotProps.data.gestion" class="text-sm text-gray-700">
                  {{ slotProps.data.gestion.año }}-{{ slotProps.data.gestion.semestre }}
                </span>
                <Tag v-else value="Global" severity="secondary" size="small" />
              </template>
            </Column>
            
            <Column field="activo" header="Estado" :sortable="true">
              <template #body="slotProps">
                <Tag
                  :value="slotProps.data.activo ? 'Activo' : 'Inactivo'"
                  :severity="slotProps.data.activo ? 'success' : 'secondary'"
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
                    @click="editarDia(slotProps.data)"
                    v-tooltip.top="'Editar'"
                  />
                  <Button
                    icon="pi pi-trash"
                    text
                    rounded
                    severity="danger"
                    @click="confirmarEliminar(slotProps.data)"
                    v-tooltip.top="'Eliminar'"
                  />
                </div>
              </template>
            </Column>

            <template #empty>
              <div class="text-center py-8 text-gray-500">
                <i class="pi pi-inbox text-5xl mb-3 block"></i>
                <p>No hay días no laborables registrados</p>
              </div>
            </template>
          </DataTable>
        </template>
      </Card>

      <!-- Dialog para crear/editar -->
      <Dialog
        v-model:visible="dialogVisible"
        :header="esEdicion ? 'Editar Día No Laborable' : 'Nuevo Día No Laborable'"
        :modal="true"
        class="w-[95vw] sm:w-[550px]"
      >
        <div class="space-y-4">
          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">
              Fecha *
            </label>
            <DatePicker
              v-model="form.fecha"
              placeholder="Seleccionar fecha"
              dateFormat="dd/mm/yy"
              class="w-full"
              :class="{ 'p-invalid': form.errors.fecha }"
              showIcon
            />
            <small v-if="form.errors.fecha" class="text-red-600">
              {{ form.errors.fecha }}
            </small>
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">
              Descripción *
            </label>
            <InputText
              v-model="form.descripcion"
              placeholder="Ej: Día del Trabajo, Vacaciones de Invierno"
              class="w-full"
              :class="{ 'p-invalid': form.errors.descripcion }"
            />
            <small v-if="form.errors.descripcion" class="text-red-600">
              {{ form.errors.descripcion }}
            </small>
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">
              Tipo *
            </label>
            <Select
              v-model="form.tipo"
              :options="tiposOptions.filter(t => t.value !== null)"
              optionLabel="label"
              optionValue="value"
              placeholder="Seleccionar tipo"
              class="w-full"
              :class="{ 'p-invalid': form.errors.tipo }"
            />
            <small v-if="form.errors.tipo" class="text-red-600">
              {{ form.errors.tipo }}
            </small>
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">
              Gestión
            </label>
            <Select
              v-model="form.id_gestion"
              :options="gestionesOptions"
              optionLabel="label"
              optionValue="value"
              placeholder="Global (todas las gestiones)"
              class="w-full"
              showClear
            />
            <small class="text-gray-500">
              Si no seleccionas gestión, aplicará a todas
            </small>
          </div>

          <div class="flex items-center gap-2">
            <Checkbox v-model="form.activo" inputId="activo" :binary="true" />
            <label for="activo" class="text-sm font-medium text-gray-700">
              Activo
            </label>
          </div>
        </div>

        <template #footer>
          <div class="flex gap-2">
            <Button
              label="Cancelar"
              @click="cerrarDialog"
              severity="secondary"
              outlined
            />
            <Button
              :label="esEdicion ? 'Guardar Cambios' : 'Crear'"
              icon="pi pi-check"
              @click="guardar"
              :loading="form.processing"
              severity="success"
            />
          </div>
        </template>
      </Dialog>

      <!-- Dialog de confirmación de eliminación -->
      <Dialog
        v-model:visible="dialogEliminarVisible"
        header="Confirmar Eliminación"
        :modal="true"
        class="w-[95vw] sm:w-[450px]"
      >
        <div v-if="diaEliminar" class="space-y-3">
          <p class="text-gray-700">¿Estás seguro de eliminar este día no laborable?</p>
          
          <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <p class="font-medium text-gray-800">{{ diaEliminar.descripcion }}</p>
            <p class="text-sm text-gray-600 mt-1">
              {{ formatearFecha(diaEliminar.fecha, 'DD/MM/YYYY') }}
            </p>
          </div>

          <p class="text-sm text-red-600">
            <i class="pi pi-exclamation-triangle mr-2"></i>
            Esta acción no se puede deshacer
          </p>
        </div>

        <template #footer>
          <div class="flex gap-2">
            <Button
              label="Cancelar"
              @click="dialogEliminarVisible = false"
              severity="secondary"
              outlined
            />
            <Button
              label="Eliminar"
              icon="pi pi-trash"
              @click="eliminar"
              :loading="formEliminar.processing"
              severity="danger"
            />
          </div>
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
import InputText from 'primevue/inputtext';
import DatePicker from 'primevue/datepicker';
import Checkbox from 'primevue/checkbox';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import { format } from 'date-fns';
import { es } from 'date-fns/locale';

const props = defineProps<{
  diasNoLaborables: any[];
  gestiones: any[];
}>();

// Estado
const dialogVisible = ref(false);
const dialogEliminarVisible = ref(false);
const esEdicion = ref(false);
const diaEliminar = ref<any>(null);
const gestionSeleccionada = ref<number | null>(null);
const tipoFiltro = ref<string | null>(null);
const estadoFiltro = ref<boolean | null>(null);
const filtroGlobal = ref('');

// Opciones
const gestionesOptions = computed(() => [
  { label: 'Global (Todas)', value: null },
  ...props.gestiones.map(g => ({
    label: `${g.año}-${g.semestre}`,
    value: g.id,
  })),
]);

const tiposOptions = [
  { label: 'Todos', value: null },
  { label: 'Feriado', value: 'Feriado' },
  { label: 'Vacación', value: 'Vacación' },
  { label: 'Suspensión', value: 'Suspensión' },
  { label: 'Otro', value: 'Otro' },
];

const estadosOptions = [
  { label: 'Todos', value: null },
  { label: 'Activos', value: true },
  { label: 'Inactivos', value: false },
];

// Formularios
const form = useForm({
  fecha: null as Date | null,
  descripcion: '',
  tipo: 'Feriado',
  id_gestion: null as number | null,
  activo: true,
});

const formEliminar = useForm({});

// Días filtrados
const diasFiltrados = computed(() => {
  let dias = props.diasNoLaborables;
  
  if (gestionSeleccionada.value !== null) {
    dias = dias.filter(d => 
      d.id_gestion === gestionSeleccionada.value || d.id_gestion === null
    );
  }
  
  if (tipoFiltro.value) {
    dias = dias.filter(d => d.tipo === tipoFiltro.value);
  }
  
  if (estadoFiltro.value !== null) {
    dias = dias.filter(d => d.activo === estadoFiltro.value);
  }
  
  if (filtroGlobal.value) {
    const busqueda = filtroGlobal.value.toLowerCase();
    dias = dias.filter(d => 
      d.descripcion.toLowerCase().includes(busqueda)
    );
  }
  
  return dias;
});

// Estadísticas
const estadisticas = computed(() => {
  const dias = diasFiltrados.value;
  return {
    total: dias.length,
    feriados: dias.filter(d => d.tipo === 'Feriado').length,
    vacaciones: dias.filter(d => d.tipo === 'Vacación').length,
    suspensiones: dias.filter(d => d.tipo === 'Suspensión').length,
  };
});

// Métodos
const abrirDialogNuevo = () => {
  esEdicion.value = false;
  form.reset();
  form.activo = true;
  form.tipo = 'Feriado';
  dialogVisible.value = true;
};

const editarDia = (dia: any) => {
  esEdicion.value = true;
  form.reset();
  form.fecha = new Date(dia.fecha);
  form.descripcion = dia.descripcion;
  form.tipo = dia.tipo;
  form.id_gestion = dia.id_gestion;
  form.activo = dia.activo;
  
  // Guardar ID para la edición
  (form as any).id = dia.id;
  
  dialogVisible.value = true;
};

const cerrarDialog = () => {
  dialogVisible.value = false;
  form.reset();
};

const guardar = () => {
  // Convertir fecha a formato YYYY-MM-DD
  const data = {
    ...form.data(),
    fecha: form.fecha ? format(form.fecha, 'yyyy-MM-dd') : null,
  };
  
  if (esEdicion.value) {
    form
      .transform(() => data)
      .put(`/dias-no-laborables/${(form as any).id}`, {
        onSuccess: () => {
          cerrarDialog();
        },
      });
  } else {
    form
      .transform(() => data)
      .post('/dias-no-laborables', {
        onSuccess: () => {
          cerrarDialog();
        },
      });
  }
};

const confirmarEliminar = (dia: any) => {
  diaEliminar.value = dia;
  dialogEliminarVisible.value = true;
};

const eliminar = () => {
  if (!diaEliminar.value) return;

  formEliminar.delete(`/dias-no-laborables/${diaEliminar.value.id}`, {
    onSuccess: () => {
      dialogEliminarVisible.value = false;
      diaEliminar.value = null;
    },
  });
};

const cargarDias = () => {
  router.get('/dias-no-laborables', {
    id_gestion: gestionSeleccionada.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const aplicarFiltros = () => {
  // Los filtros se aplican automáticamente vía computed
};

const formatearFecha = (fecha: string, formato: string = 'DD/MM/YYYY') => {
  if (!fecha) return '-';
  return format(new Date(fecha), formato, { locale: es });
};

const getTipoSeverity = (tipo: string) => {
  switch (tipo) {
    case 'Feriado':
      return 'danger';
    case 'Vacación':
      return 'warn';
    case 'Suspensión':
      return 'secondary';
    default:
      return 'info';
  }
};

const getTipoIcon = (tipo: string) => {
  switch (tipo) {
    case 'Feriado':
      return 'pi pi-flag';
    case 'Vacación':
      return 'pi pi-sun';
    case 'Suspensión':
      return 'pi pi-ban';
    default:
      return 'pi pi-calendar-times';
  }
};
</script>
