<template>
  <AppLayout title="Mis Asistencias">
    <div class="container mx-auto px-4 py-6 max-w-7xl">
      <!-- Header con información del docente -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">Mis Asistencias</h1>
          <p class="text-sm text-gray-600 mt-1">
            {{ docente.nombre }} {{ docente.apellidos }} • {{ gestionActual?.año }}-{{ gestionActual?.semestre }}
          </p>
        </div>
        
        <div class="flex gap-2">
          <Select
            v-model="gestionSeleccionada"
            :options="gestiones"
            optionLabel="label"
            optionValue="id"
            placeholder="Seleccionar gestión"
            class="w-48"
            @change="cambiarGestion"
          />
        </div>
      </div>

      <!-- Tarjetas de estadísticas -->
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <Card class="bg-gradient-to-br from-blue-500 to-blue-600 text-white">
          <template #content>
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm opacity-90">Total Clases</p>
                <p class="text-3xl font-bold mt-1">{{ estadisticas.total }}</p>
              </div>
              <i class="pi pi-calendar text-4xl opacity-30"></i>
            </div>
          </template>
        </Card>

        <Card class="bg-gradient-to-br from-green-500 to-green-600 text-white">
          <template #content>
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm opacity-90">Asistencias</p>
                <p class="text-3xl font-bold mt-1">{{ estadisticas.presentes }}</p>
                <p class="text-xs opacity-75 mt-1">{{ estadisticas.porcentaje_asistencia }}%</p>
              </div>
              <i class="pi pi-check-circle text-4xl opacity-30"></i>
            </div>
          </template>
        </Card>

        <Card class="bg-gradient-to-br from-orange-500 to-orange-600 text-white">
          <template #content>
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm opacity-90">Retrasos</p>
                <p class="text-3xl font-bold mt-1">{{ estadisticas.retrasos }}</p>
                <p class="text-xs opacity-75 mt-1">{{ estadisticas.porcentaje_retrasos }}%</p>
              </div>
              <i class="pi pi-clock text-4xl opacity-30"></i>
            </div>
          </template>
        </Card>

        <Card class="bg-gradient-to-br from-red-500 to-red-600 text-white">
          <template #content>
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm opacity-90">Faltas</p>
                <p class="text-3xl font-bold mt-1">{{ estadisticas.faltas }}</p>
                <p class="text-xs opacity-75 mt-1">{{ estadisticas.porcentaje_faltas }}%</p>
              </div>
              <i class="pi pi-times-circle text-4xl opacity-30"></i>
            </div>
          </template>
        </Card>

        <Card class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white">
          <template #content>
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm opacity-90">Justificadas</p>
                <p class="text-3xl font-bold mt-1">{{ estadisticas.justificadas }}</p>
              </div>
              <i class="pi pi-file-edit text-4xl opacity-30"></i>
            </div>
          </template>
        </Card>
      </div>

      <!-- Clases de Hoy -->
      <Card class="mb-6">
        <template #title>
          <div class="flex items-center gap-2">
            <i class="pi pi-clock text-blue-600"></i>
            <span>Clases de Hoy - {{ fechaHoy }}</span>
          </div>
        </template>
        <template #content>
          <div v-if="clasesHoy.length === 0" class="text-center py-8 text-gray-500">
            <i class="pi pi-inbox text-5xl mb-3 block"></i>
            <p>No tienes clases programadas para hoy</p>
          </div>

          <div v-else class="space-y-3">
            <div
              v-for="(clase, index) in clasesHoy"
              :key="index"
              class="border rounded-lg p-4 hover:shadow-md transition-shadow"
              :class="{
                'border-green-300 bg-green-50': clase.ya_registrada,
                'border-blue-300 bg-blue-50': clase.puede_registrar,
                'border-gray-300 bg-gray-50': !clase.puede_registrar && !clase.ya_registrada
              }"
            >
              <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex-1">
                  <div class="flex items-center gap-2 mb-2 flex-wrap">
                    <span class="text-lg font-semibold text-gray-800">
                      {{ clase.hora_inicio }} - {{ clase.hora_fin }}
                    </span>
                    <span v-if="clase.es_grupo" class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded-full font-medium">
                      {{ clase.cantidad_bloques }} bloques
                    </span>
                    <Tag
                      v-if="clase.ya_registrada && clase.asistencia.estado === 'Presente'"
                      value="Registrada"
                      severity="success"
                      :icon="'pi pi-check'"
                    />
                    <Tag
                      v-else-if="clase.ya_registrada && clase.asistencia.estado === 'Retraso'"
                      value="Retraso"
                      severity="warn"
                      :icon="'pi pi-clock'"
                    />
                    <Tag
                      v-else-if="clase.puede_registrar"
                      value="Disponible"
                      severity="info"
                      :icon="'pi pi-clock'"
                    />
                    <Tag
                      v-else-if="clase.puede_marcar_retraso"
                      value="Ventana de Retraso"
                      severity="warn"
                      :icon="'pi pi-exclamation-triangle'"
                    />
                    <Tag
                      v-else
                      value="Fuera de tiempo"
                      severity="secondary"
                    />
                  </div>
                  
                  <p class="text-sm text-gray-700 font-medium">
                    {{ clase.asignacion.grupo_materia.materia.nombre }}
                  </p>
                  <div class="flex flex-wrap gap-3 mt-2 text-xs text-gray-600">
                    <span class="font-semibold text-blue-600">
                      <i class="pi pi-users mr-1"></i>
                      Grupo {{ clase.asignacion.grupo_materia.grupo.numero }}
                    </span>
                    <span>
                      <i class="pi pi-building mr-1"></i>
                      Aula {{ clase.asignacion.aula.nombre }}
                    </span>
                    <span v-if="clase.es_grupo" class="text-purple-600 font-medium">
                      <i class="pi pi-link mr-1"></i>
                      {{ clase.cantidad_bloques }} bloques consecutivos
                    </span>
                    <span v-if="!clase.ya_registrada">
                      <i class="pi pi-stopwatch mr-1"></i>
                      Límite: {{ clase.hora_limite_registro }}
                    </span>
                  </div>

                  <div v-if="clase.asistencia" class="mt-2">
                    <div class="flex items-center gap-2 text-xs">
                      <i class="pi pi-calendar-check text-green-600"></i>
                      <span class="text-gray-600">
                        Registrada: {{ formatearFecha(clase.asistencia.fecha_asistencia) }}
                      </span>
                    </div>
                  </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-2">
                  <Button
                    v-if="clase.puede_registrar && !clase.ya_registrada"
                    label="Registrar Asistencia"
                    icon="pi pi-check"
                    @click="confirmarRegistro(clase, 'Presente')"
                    :loading="registrandoAsistencia"
                    severity="success"
                    class="w-full md:w-auto"
                  />
                  <Button
                    v-if="clase.puede_marcar_retraso && !clase.ya_registrada"
                    label="Marcar Retraso"
                    icon="pi pi-clock"
                    @click="confirmarRegistro(clase, 'Retraso')"
                    :loading="registrandoAsistencia"
                    severity="warn"
                    class="w-full md:w-auto"
                  />
                  <Button
                    v-if="(clase.puede_registrar || clase.puede_marcar_retraso) && !clase.ya_registrada"
                    label="Generar QR"
                    icon="pi pi-qrcode"
                    @click="abrirGeneradorQR(clase.asignacion.id)"
                    severity="info"
                    outlined
                    class="w-full md:w-auto"
                  />
                  <Button
                    v-else-if="clase.ya_registrada && clase.asistencia.estado === 'Presente'"
                    label="Asistencia Registrada"
                    icon="pi pi-check-circle"
                    disabled
                    severity="success"
                    outlined
                    class="w-full md:w-auto"
                  />
                  <Button
                    v-else-if="clase.ya_registrada && clase.asistencia.estado === 'Retraso'"
                    label="Retraso Registrado"
                    icon="pi pi-clock"
                    disabled
                    severity="warn"
                    outlined
                    class="w-full md:w-auto"
                  />
                  <Button
                    v-else-if="!clase.puede_registrar && !clase.puede_marcar_retraso && !clase.ya_registrada"
                    label="Tiempo Expirado"
                    icon="pi pi-clock"
                    disabled
                    severity="secondary"
                    outlined
                    class="w-full md:w-auto"
                  />
                </div>
              </div>
            </div>
          </div>
        </template>
      </Card>

      <!-- Historial de Asistencias -->
      <Card>
        <template #title>
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <i class="pi pi-history text-blue-600"></i>
              <span>Historial de Asistencias</span>
            </div>
            <Button
              label="Exportar"
              icon="pi pi-download"
              outlined
              size="small"
              @click="exportarHistorial"
            />
          </div>
        </template>
        <template #content>
          <DataTable
            :value="asistencias"
            :paginator="true"
            :rows="10"
            stripedRows
            showGridlines
            :loading="cargandoAsistencias"
            responsiveLayout="stack"
            breakpoint="768px"
          >
            <Column field="fecha" header="Fecha" :sortable="true">
              <template #body="slotProps">
                {{ formatearFecha(slotProps.data.fecha, 'DD/MM/YYYY') }}
              </template>
            </Column>
            
            <Column field="asignacion.horario.hora.hora_inicio" header="Hora" :sortable="true">
              <template #body="slotProps">
                {{ slotProps.data.asignacion.horario.hora.hora_inicio }} - 
                {{ slotProps.data.asignacion.horario.hora.hora_fin }}
              </template>
            </Column>
            
            <Column field="asignacion.grupo_materia.materia.nombre" header="Materia" :sortable="true" />
            
            <Column field="asignacion.grupo_materia.grupo.numero" header="Grupo" :sortable="true">
              <template #body="slotProps">
                Grupo {{ slotProps.data.asignacion.grupo_materia.grupo.numero }}
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
            
            <Column field="fecha_asistencia" header="Registrada" :sortable="true">
              <template #body="slotProps">
                <span v-if="slotProps.data.fecha_asistencia" class="text-xs text-gray-600">
                  {{ formatearFecha(slotProps.data.fecha_asistencia, 'DD/MM/YYYY HH:mm') }}
                </span>
                <span v-else class="text-xs text-gray-400">-</span>
              </template>
            </Column>
            
            <Column header="Acciones" :style="{ width: '100px' }">
              <template #body="slotProps">
                <Button
                  v-if="slotProps.data.estado === 'Falta' && !slotProps.data.justificacion"
                  icon="pi pi-file-edit"
                  text
                  rounded
                  severity="warning"
                  @click="abrirDialogJustificacion(slotProps.data)"
                  v-tooltip.top="'Justificar falta'"
                />
                <Button
                  v-else-if="slotProps.data.justificacion"
                  icon="pi pi-eye"
                  text
                  rounded
                  severity="info"
                  @click="verJustificacion(slotProps.data)"
                  v-tooltip.top="'Ver justificación'"
                />
              </template>
            </Column>

            <template #empty>
              <div class="text-center py-6 text-gray-500">
                No hay registros de asistencia en este período
              </div>
            </template>
          </DataTable>
        </template>
      </Card>

      <!-- Dialog para confirmar registro -->
      <Dialog
        v-model:visible="dialogConfirmarVisible"
        :header="estadoSeleccionado === 'Presente' ? 'Confirmar Registro de Asistencia' : 'Confirmar Registro de Retraso'"
        :modal="true"
        class="w-[95vw] sm:w-[450px]"
      >
        <div v-if="claseSeleccionada" class="space-y-3">
          <p class="text-gray-700">
            {{ estadoSeleccionado === 'Presente' 
                ? (claseSeleccionada.es_grupo 
                    ? '¿Confirmas tu asistencia a los siguientes bloques de clase?' 
                    : '¿Confirmas tu asistencia a la siguiente clase?')
                : (claseSeleccionada.es_grupo 
                    ? '¿Confirmas que llegaste con retraso a los siguientes bloques de clase?' 
                    : '¿Confirmas que llegaste con retraso a la siguiente clase?')
            }}
          </p>
          
          <div 
            class="border rounded-lg p-4"
            :class="estadoSeleccionado === 'Presente' ? 'bg-blue-50 border-blue-200' : 'bg-orange-50 border-orange-200'"
          >
            <p class="font-semibold text-gray-800 text-lg">
              {{ claseSeleccionada.asignacion.grupo_materia.materia.nombre }}
            </p>
            <p class="text-sm text-blue-600 font-semibold mt-1">
              <i class="pi pi-users mr-1"></i>
              Grupo {{ claseSeleccionada.asignacion.grupo_materia.grupo.numero }}
            </p>
            <p class="text-sm text-gray-600">
              <i class="pi pi-building mr-1"></i>
              Aula {{ claseSeleccionada.asignacion.aula.nombre }}
            </p>
            <p class="text-sm text-gray-600 font-medium mt-2">
              <i class="pi pi-clock mr-1"></i>
              {{ claseSeleccionada.hora_inicio }} - {{ claseSeleccionada.hora_fin }}
            </p>
            <div v-if="claseSeleccionada.es_grupo" class="mt-3 pt-3 border-t border-gray-300">
              <p class="text-xs text-purple-600 font-medium">
                <i class="pi pi-link mr-1"></i>
                Clase agrupada: {{ claseSeleccionada.cantidad_bloques }} bloques consecutivos
              </p>
              <p class="text-xs text-gray-500 mt-1">
                La asistencia se registrará para todos los bloques
              </p>
            </div>
            <p v-if="estadoSeleccionado === 'Retraso'" class="text-xs text-orange-600 mt-2 pt-2 border-t">
              <i class="pi pi-exclamation-triangle mr-1"></i>
              Se registrará como retraso en el sistema
              <span v-if="claseSeleccionada.es_grupo"> para todos los bloques</span>.
            </p>
          </div>
        </div>

        <template #footer>
          <div class="flex gap-2">
            <Button
              label="Cancelar"
              @click="dialogConfirmarVisible = false"
              severity="secondary"
              outlined
            />
            <Button
              :label="estadoSeleccionado === 'Presente' ? 'Confirmar Asistencia' : 'Confirmar Retraso'"
              :icon="estadoSeleccionado === 'Presente' ? 'pi pi-check' : 'pi pi-clock'"
              @click="registrarAsistencia"
              :loading="registrandoAsistencia"
              :severity="estadoSeleccionado === 'Presente' ? 'success' : 'warn'"
            />
          </div>
        </template>
      </Dialog>

      <!-- Dialog para justificar falta -->
      <Dialog
        v-model:visible="dialogJustificacionVisible"
        header="Justificar Falta"
        :modal="true"
        class="w-[95vw] sm:w-[600px]"
      >
        <div class="space-y-4">
          <div v-if="asistenciaSeleccionada" class="bg-gray-50 border rounded-lg p-3">
            <p class="text-sm text-gray-600">
              <strong>Fecha:</strong> {{ formatearFecha(asistenciaSeleccionada.fecha, 'DD/MM/YYYY') }}
            </p>
            <p class="text-sm text-gray-600">
              <strong>Materia:</strong> {{ asistenciaSeleccionada.asignacion.grupo_materia.materia.nombre }}
            </p>
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">
              Descripción de la Justificación *
            </label>
            <Textarea
              v-model="justificacionForm.descripcion"
              rows="4"
              placeholder="Explica el motivo de tu falta..."
              class="w-full"
              :class="{ 'p-invalid': justificacionForm.errors.descripcion }"
            />
            <small v-if="justificacionForm.errors.descripcion" class="text-red-600">
              {{ justificacionForm.errors.descripcion }}
            </small>
          </div>

          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">
              Documento de Respaldo (Opcional)
            </label>
            <input
              ref="archivoInput"
              type="file"
              @change="handleArchivoChange"
              accept=".pdf,.jpg,.jpeg,.png"
              class="hidden"
            />
            <div
              @click="archivoInput?.click()"
              class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-400 transition-colors"
            >
              <div v-if="!archivoSeleccionado">
                <i class="pi pi-cloud-upload text-3xl text-gray-400 mb-2 block"></i>
                <p class="text-sm text-gray-600">Click para seleccionar archivo</p>
                <p class="text-xs text-gray-500 mt-1">PDF, JPG, PNG (Max 5MB)</p>
              </div>
              <div v-else class="flex items-center justify-center gap-2">
                <i class="pi pi-file text-2xl text-blue-600"></i>
                <span class="text-sm font-medium">{{ archivoSeleccionado.name }}</span>
                <Button
                  icon="pi pi-times"
                  text
                  rounded
                  severity="danger"
                  size="small"
                  @click.stop="archivoSeleccionado = null"
                />
              </div>
            </div>
            <small v-if="justificacionForm.errors.archivo" class="text-red-600">
              {{ justificacionForm.errors.archivo }}
            </small>
          </div>
        </div>

        <template #footer>
          <div class="flex gap-2">
            <Button
              label="Cancelar"
              @click="cerrarDialogJustificacion"
              severity="secondary"
              outlined
            />
            <Button
              label="Enviar Justificación"
              icon="pi pi-send"
              @click="enviarJustificacion"
              :loading="justificacionForm.processing"
              severity="warning"
            />
          </div>
        </template>
      </Dialog>

      <!-- Dialog para ver justificación -->
      <Dialog
        v-model:visible="dialogVerJustificacionVisible"
        header="Detalle de Justificación"
        :modal="true"
        class="w-[95vw] sm:w-[600px]"
      >
        <div v-if="justificacionVista" class="space-y-4">
          <div class="bg-gray-50 border rounded-lg p-3">
            <p class="text-sm text-gray-600">
              <strong>Fecha de Falta:</strong> {{ formatearFecha(justificacionVista.asistencia.fecha, 'DD/MM/YYYY') }}
            </p>
            <p class="text-sm text-gray-600">
              <strong>Fecha de Justificación:</strong> {{ formatearFecha(justificacionVista.fecha_justificacion, 'DD/MM/YYYY HH:mm') }}
            </p>
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
              :label="'Descargar documento'"
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
            @click="dialogVerJustificacionVisible = false"
          />
        </template>
      </Dialog>

      <!-- Modales de QR -->
      <QRGenerador
        v-model:visible="qrGeneradorVisible"
        :idAsignacion="asignacionQRSeleccionada ?? undefined"
        @qr-generado="onQRGenerado"
      />

      <QRScanner
        v-model:visible="qrScannerVisible"
        @asistencia-registrada="onAsistenciaRegistrada"
      />

      <!-- Botón flotante para escanear QR -->
      <Button
        icon="pi pi-camera"
        class="fixed bottom-6 right-6 w-16 h-16 rounded-full shadow-lg"
        severity="info"
        @click="abrirEscanerQR"
        v-tooltip.left="'Escanear QR'"
      />
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
import QRGenerador from '@/components/QRGenerador.vue';
import QRScanner from '@/components/QRScanner.vue';
import { format } from 'date-fns';
import { es } from 'date-fns/locale';

const props = defineProps<{
  docente: any;
  clasesHoy: any[];
  estadisticas: any;
  asistencias: any[];
  gestionActual: any;
  gestiones: any[];
}>();

// Estado
const gestionSeleccionada = ref(props.gestionActual?.id);
const dialogConfirmarVisible = ref(false);
const dialogJustificacionVisible = ref(false);
const dialogVerJustificacionVisible = ref(false);
const claseSeleccionada = ref<any>(null);
const asistenciaSeleccionada = ref<any>(null);
const justificacionVista = ref<any>(null);
const registrandoAsistencia = ref(false);
const cargandoAsistencias = ref(false);
const archivoSeleccionado = ref<File | null>(null);
const archivoInput = ref<HTMLInputElement | null>(null);

// QR
const qrGeneradorVisible = ref(false);
const qrScannerVisible = ref(false);
const asignacionQRSeleccionada = ref<number | null>(null);

// Fecha de hoy formateada
const fechaHoy = computed(() => {
  return format(new Date(), "EEEE, dd 'de' MMMM 'de' yyyy", { locale: es });
});

// Formulario de justificación
const justificacionForm = useForm({
  id_asistencia: null as number | null,
  descripcion: '',
  archivo: null as File | null,
});

// Estado seleccionado para registro
const estadoSeleccionado = ref<'Presente' | 'Retraso'>('Presente');

// Métodos
const confirmarRegistro = (clase: any, estado: 'Presente' | 'Retraso' = 'Presente') => {
  claseSeleccionada.value = clase;
  estadoSeleccionado.value = estado;
  dialogConfirmarVisible.value = true;
};

const registrarAsistencia = () => {
  if (!claseSeleccionada.value) return;

  registrandoAsistencia.value = true;

  // Si es un grupo, enviar todos los IDs de las asignaciones
  const payload = claseSeleccionada.value.es_grupo
    ? {
        ids_asignaciones: claseSeleccionada.value.asignaciones.map((a: any) => a.id),
        estado: estadoSeleccionado.value,
      }
    : {
        id_asignacion: claseSeleccionada.value.asignacion.id,
        estado: estadoSeleccionado.value,
      };

  router.post(
    '/asistencias/registrar',
    payload,
    {
      onSuccess: () => {
        dialogConfirmarVisible.value = false;
        claseSeleccionada.value = null;
        estadoSeleccionado.value = 'Presente';
      },
      onFinish: () => {
        registrandoAsistencia.value = false;
      },
    }
  );
};

const abrirDialogJustificacion = (asistencia: any) => {
  asistenciaSeleccionada.value = asistencia;
  justificacionForm.reset();
  justificacionForm.id_asistencia = asistencia.id;
  archivoSeleccionado.value = null;
  dialogJustificacionVisible.value = true;
};

const cerrarDialogJustificacion = () => {
  dialogJustificacionVisible.value = false;
  asistenciaSeleccionada.value = null;
  justificacionForm.reset();
  archivoSeleccionado.value = null;
};

const handleArchivoChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files.length > 0) {
    archivoSeleccionado.value = target.files[0];
    justificacionForm.archivo = target.files[0];
  }
};

const enviarJustificacion = () => {
  justificacionForm
    .transform((data) => {
      const formData = new FormData();
      formData.append('id_asistencia', String(data.id_asistencia));
      formData.append('descripcion', data.descripcion);
      if (data.archivo) {
        formData.append('archivo', data.archivo);
      }
      return formData;
    })
    .post('/asistencias/justificar', {
      forceFormData: true,
      onSuccess: () => {
        cerrarDialogJustificacion();
      },
    });
};

const verJustificacion = (asistencia: any) => {
  justificacionVista.value = asistencia.justificacion;
  dialogVerJustificacionVisible.value = true;
};

const descargarArchivo = (ruta: string) => {
  window.open(`/storage/${ruta}`, '_blank');
};

const cambiarGestion = () => {
  router.get('/asistencias', { id_gestion: gestionSeleccionada.value });
};

const exportarHistorial = () => {
  if (!gestionSeleccionada.value) {
    alert('Por favor, selecciona una gestión antes de exportar.');
    return;
  }

  // Construir la URL con parámetros
  const params = new URLSearchParams({
    id_gestion: String(gestionSeleccionada.value),
  });

  // Agregar fechas del mes actual por defecto
  const fechaInicio = format(new Date(new Date().getFullYear(), new Date().getMonth(), 1), 'yyyy-MM-dd');
  const fechaFin = format(new Date(new Date().getFullYear(), new Date().getMonth() + 1, 0), 'yyyy-MM-dd');
  
  params.append('fecha_inicio', fechaInicio);
  params.append('fecha_fin', fechaFin);

  // Descargar el archivo
  window.location.href = `/asistencias/exportar-historial?${params.toString()}`;
};

const formatearFecha = (fecha: string, formato: string = 'DD/MM/YYYY') => {
  if (!fecha) return '-';
  return format(new Date(fecha), formato, { locale: es });
};

const getEstadoSeverity = (estado: string) => {
  switch (estado) {
    case 'Presente':
      return 'success';
    case 'Retraso':
      return 'warn';
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
    case 'Retraso':
      return 'pi pi-clock';
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

// Funciones QR
const abrirGeneradorQR = (idAsignacion: number) => {
  asignacionQRSeleccionada.value = idAsignacion;
  qrGeneradorVisible.value = true;
};

const abrirEscanerQR = () => {
  qrScannerVisible.value = true;
};

const onQRGenerado = (data: any) => {
  console.log('QR generado:', data);
};

const onAsistenciaRegistrada = (data: any) => {
  console.log('Asistencia registrada vía QR:', data);
  // Recargar la página para actualizar las asistencias
  router.reload({ only: ['clasesHoy', 'asistencias', 'estadisticas'] });
};
</script>
