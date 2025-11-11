<template>
  <AppLayout title="Gestión de Asignaciones">
    <div class="flex flex-col h-full space-y-4">
      <!-- Header con controles -->
      <div class="flex flex-col gap-4 shrink-0">
        <!-- Selector de gestión y botones -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 w-full sm:w-auto shrink-0">
            <label class="font-semibold text-sm whitespace-nowrap">Gestión:</label>
            <Select
              v-model="gestionActual"
              :options="gestiones"
              optionValue="id"
              placeholder="Selecciona gestión"
              class="w-full sm:w-64 text-sm shrink-0"
              :editable="false"
            >
              <template #value="slotProps">
                <template v-if="slotProps.value">
                  <div class="flex items-center py-1">
                    <i class="pi pi-calendar mr-2 text-xs text-gray-500"></i>
                    <span class="font-medium">
                      {{ getGestionNombre(slotProps.value) || `[ID: ${slotProps.value}]` }}
                    </span>
                  </div>
                </template>
                <template v-else>
                  <span class="text-gray-400">{{ slotProps.placeholder }}</span>
                </template>
              </template>
              <template #option="slotProps">
                <div class="flex items-center gap-2 py-1">
                  <i class="pi pi-calendar text-xs text-gray-500"></i>
                  <span class="font-medium">{{ slotProps.option.año }}</span>
                  <span class="text-gray-600">- Semestre {{ slotProps.option.semestre }}</span>
                </div>
              </template>
            </Select>
          </div>
          
          <div class="flex gap-2 w-full sm:w-auto">
            <Button
              label="Importar Excel"
              icon="pi pi-file-excel"
              @click="irAImportar"
              severity="success"
              outlined
              class="flex-1 sm:flex-initial"
              size="small"
            />
            <Button
              label="Asignación Rápida"
              icon="pi pi-bolt"
              @click="openRapidDialog"
              severity="info"
              class="flex-1 sm:flex-initial"
              size="small"
            />
            <Button
              label="Nueva Asignación"
              icon="pi pi-plus"
              @click="openCreateDialog"
              severity="success"
              class="flex-1 sm:flex-initial"
              size="small"
            />
          </div>
        </div>

        <!-- Filtros de visualización -->
        <Card>
          <template #content>
            <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-end">
              <div class="flex flex-col gap-2">
                <label class="font-semibold text-xs">Ver horario de:</label>
                <div class="flex gap-2">
                  <Button
                    label="Docente"
                    icon="pi pi-user"
                    :severity="vistaActual === 'docente' ? 'info' : 'secondary'"
                    @click="cambiarVista('docente')"
                    size="small"
                    outlined
                  />
                  <Button
                    label="Aula"
                    icon="pi pi-building"
                    :severity="vistaActual === 'aula' ? 'info' : 'secondary'"
                    @click="cambiarVista('aula')"
                    size="small"
                    outlined
                  />
                </div>
              </div>

              <div v-if="vistaActual === 'docente'" class="flex flex-col gap-2 flex-1">
                <label class="font-semibold text-xs">Selecciona el docente:</label>
                <div class="flex gap-2">
                  <Select
                    v-model="docenteSeleccionado"
                    :options="docentes"
                    optionValue="codigo"
                    optionLabel="nombre_completo"
                    placeholder="Selecciona un docente"
                    class="flex-1 text-sm"
                    filter
                    showClear
                  />
                  <Button
                    v-if="docenteSeleccionado"
                    icon="pi pi-file-pdf"
                    severity="danger"
                    outlined
                    size="small"
                    @click="exportarPDF"
                    v-tooltip.top="'Exportar a PDF'"
                  />
                  <Button
                    v-if="docenteSeleccionado"
                    icon="pi pi-file-excel"
                    severity="success"
                    outlined
                    size="small"
                    @click="exportarCSV"
                    v-tooltip.top="'Exportar a CSV'"
                  />
                </div>
              </div>

              <div v-if="vistaActual === 'aula'" class="flex flex-col gap-2 flex-1">
                <label class="font-semibold text-xs">Selecciona el aula:</label>
                <div class="flex gap-2">
                  <Select
                    v-model="aulaSeleccionada"
                    :options="aulas"
                    optionValue="id"
                    optionLabel="nombre"
                    placeholder="Selecciona un aula"
                    class="flex-1 text-sm"
                    filter
                    showClear
                  />
                  <Button
                    v-if="aulaSeleccionada"
                    icon="pi pi-file-pdf"
                    severity="danger"
                    outlined
                    size="small"
                    @click="exportarPDF"
                    v-tooltip.top="'Exportar a PDF'"
                  />
                  <Button
                    v-if="aulaSeleccionada"
                    icon="pi pi-file-excel"
                    severity="success"
                    outlined
                    size="small"
                    @click="exportarCSV"
                    v-tooltip.top="'Exportar a CSV'"
                  />
                </div>
              </div>
            </div>
          </template>
        </Card>
      </div>

      <!-- Tabla de horario semanal -->
      <div class="flex-1 overflow-auto">
        <Card class="h-full">
          <template #content>
            <div class="overflow-x-auto">
              <table class="w-full border-collapse border border-gray-300 text-xs">
                <thead>
                  <tr class="bg-gray-100">
                    <th class="border border-gray-300 p-2 sticky left-0 bg-gray-100 min-w-[100px] text-gray-800 font-bold">
                      Hora
                    </th>
                    <th 
                      v-for="dia in dias" 
                      :key="dia.id" 
                      class="border border-gray-300 p-2 min-w-[150px] text-gray-800 font-bold"
                    >
                      {{ dia.nombre }}
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="hora in horas" :key="hora.id">
                    <td class="border border-gray-300 p-2 font-semibold sticky left-0 bg-white text-gray-800">
                      {{ formatTime(hora.hora_inicio) }}<br>
                      <span class="text-[10px] text-gray-600">{{ formatTime(hora.hora_fin) }}</span>
                    </td>
                    <td 
                      v-for="dia in dias" 
                      :key="`${dia.id}-${hora.id}`"
                      class="border border-gray-300 p-1 relative hover:bg-gray-50 cursor-pointer"
                      :class="getCeldaClass(dia.id, hora.id)"
                      @click="clickCelda(dia.id, hora.id)"
                    >
                      <div v-if="getAsignacion(dia.id, hora.id)" class="space-y-1">
                        <div 
                          v-for="(asig, index) in getAsignacion(dia.id, hora.id)" 
                          :key="asig.id"
                          class="rounded p-2 cursor-pointer transition-all hover:shadow-md border"
                          :style="getAsignacionStyle(index)"
                          @click.stop="verDetalleAsignacion(asig)"
                        >
                          <div class="font-bold text-[11px]" :style="{ color: getTextColor(index) }">
                            {{ asig.materia_nombre }}
                          </div>
                          <div class="text-[10px] font-medium" :style="{ color: getTextColor(index) }">
                            Grupo: {{ asig.grupo_nombre }}
                          </div>
                          <div class="text-[10px]" :style="{ color: getTextColor(index, 0.7) }">
                            {{ asig.docente_nombre }}
                          </div>
                          <div class="text-[10px] font-semibold" :style="{ color: getTextColor(index, 0.8) }">
                            📍 {{ asig.aula_nombre }}
                          </div>
                        </div>
                      </div>
                      <div v-else class="text-center text-gray-300 text-xs py-2">
                        +
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </template>
        </Card>
      </div>
    </div>

    <!-- Dialog para asignación rápida -->
    <Dialog
      v-model:visible="rapidDialogVisible"
      header="Asignación Rápida"
      :modal="true"
      class="w-[95vw] sm:w-[700px]"
      :dismissableMask="true"
    >
      <div class="space-y-4">
        <p class="text-sm text-gray-600">
          Configura los datos que se repetirán y luego asigna rápidamente a diferentes horarios/aulas.
        </p>

        <!-- Datos que se mantienen -->
        <Card>
          <template #title>
            <span class="text-sm">Datos Fijos</span>
          </template>
          <template #content>
            <div class="space-y-3">
              <div class="flex flex-col gap-2">
                <label class="font-semibold text-sm">Docente</label>
                <Select
                  v-model="rapidForm.id_docente"
                  :options="docentes"
                  optionValue="codigo"
                  optionLabel="nombre_completo"
                  placeholder="Selecciona docente"
                  class="w-full text-sm"
                  filter
                />
              </div>

              <div class="flex flex-col gap-2">
                <label class="font-semibold text-sm">Materia - Grupo</label>
                <Select
                  v-model="rapidForm.id_grupo_materia"
                  :options="gruposMaterias"
                  optionValue="id"
                  optionLabel="nombre_completo"
                  placeholder="Selecciona materia y grupo"
                  class="w-full text-sm"
                  filter
                />
                <small class="text-gray-500 text-xs">
                  Carga horaria requerida: {{ getCargaHorariaRapida() }} bloques
                </small>
              </div>
            </div>
          </template>
        </Card>

        <!-- Bloques a asignar -->
        <Card>
          <template #title>
            <div class="flex justify-between items-center">
              <span class="text-sm">Bloques a Asignar</span>
              <Button
                label="Agregar Bloque"
                icon="pi pi-plus"
                @click="agregarBloqueRapido"
                size="small"
                text
              />
            </div>
          </template>
          <template #content>
            <div class="space-y-2">
              <div 
                v-for="(bloque, index) in bloquesRapidos" 
                :key="index"
                class="flex gap-2 items-start border-b pb-2"
              >
                <div class="flex-1 space-y-2">
                  <div class="grid grid-cols-2 gap-2">
                    <Select
                      v-model="bloque.dia"
                      :options="dias"
                      optionValue="id"
                      optionLabel="nombre"
                      placeholder="Día"
                      class="text-xs"
                    />
                    <Select
                      v-model="bloque.aula"
                      :options="aulas"
                      optionValue="id"
                      optionLabel="nombre"
                      placeholder="Aula"
                      class="text-xs"
                      filter
                    />
                  </div>
                  <div class="grid grid-cols-2 gap-2">
                    <div class="flex flex-col gap-1">
                      <label class="text-[10px] text-gray-600">Hora Inicio</label>
                      <Select
                        v-model="bloque.hora_inicio"
                        :options="horas"
                        optionValue="id"
                        placeholder="Inicio"
                        class="text-xs"
                      >
                        <template #value="slotProps">
                          <span v-if="slotProps.value" class="text-xs">{{ getHoraPeriodo(slotProps.value) }}</span>
                          <span v-else class="text-xs">{{ slotProps.placeholder }}</span>
                        </template>
                        <template #option="slotProps">
                          <span class="text-xs">{{ formatTime(slotProps.option.hora_inicio) }} - {{ formatTime(slotProps.option.hora_fin) }}</span>
                        </template>
                      </Select>
                    </div>
                    <div class="flex flex-col gap-1">
                      <label class="text-[10px] text-gray-600">Hora Fin</label>
                      <Select
                        v-model="bloque.hora_fin"
                        :options="horas"
                        optionValue="id"
                        placeholder="Fin"
                        class="text-xs"
                      >
                        <template #value="slotProps">
                          <span v-if="slotProps.value" class="text-xs">{{ getHoraPeriodo(slotProps.value) }}</span>
                          <span v-else class="text-xs">{{ slotProps.placeholder }}</span>
                        </template>
                        <template #option="slotProps">
                          <span class="text-xs">{{ formatTime(slotProps.option.hora_inicio) }} - {{ formatTime(slotProps.option.hora_fin) }}</span>
                        </template>
                      </Select>
                    </div>
                  </div>
                  <small v-if="bloque.hora_inicio && bloque.hora_fin" class="text-[10px] text-blue-600">
                    Se asignarán {{ calcularBloquesConsecutivos(bloque.hora_inicio, bloque.hora_fin) }} bloques de 45 min
                  </small>
                </div>
                <Button
                  icon="pi pi-trash"
                  @click="eliminarBloqueRapido(index)"
                  severity="danger"
                  text
                  size="small"
                />
              </div>
              <div v-if="bloquesRapidos.length === 0" class="text-center text-gray-400 py-4 text-sm">
                No hay bloques. Haz clic en "Agregar Bloque" para comenzar.
              </div>
            </div>
          </template>
        </Card>

        <div v-if="rapidForm.errors" class="p-3 bg-red-50 border border-red-200 rounded text-xs">
          <i class="pi pi-exclamation-triangle text-red-600 mr-2"></i>
          <span class="text-red-800">{{ rapidForm.errors }}</span>
        </div>
      </div>

      <template #footer>
        <div class="flex flex-col-reverse sm:flex-row gap-2">
          <Button
            label="Cancelar"
            @click="closeRapidDialog"
            severity="secondary"
            outlined
            class="w-full sm:w-auto text-sm"
          />
          <Button
            label="Asignar Todos"
            @click="guardarAsignacionesRapidas"
            :loading="rapidForm.processing"
            class="w-full sm:w-auto text-sm"
            :disabled="bloquesRapidos.length === 0"
          />
        </div>
      </template>
    </Dialog>

    <!-- Dialog para crear asignación -->
    <Dialog
      v-model:visible="dialogVisible"
      header="Nueva Asignación"
      :modal="true"
      class="w-[95vw] sm:w-[600px]"
      :dismissableMask="true"
    >
      <form @submit.prevent="submitForm" class="space-y-3 mt-4">
        <div class="flex flex-col gap-2">
          <label for="id_docente" class="font-semibold text-sm">Docente</label>
          <Select
            id="id_docente"
            v-model="form.id_docente"
            :options="docentes"
            optionValue="codigo"
            optionLabel="nombre_completo"
            placeholder="Selecciona docente"
            :class="{ 'p-invalid': form.errors.id_docente }"
            class="w-full text-sm"
            filter
          />
          <small v-if="form.errors.id_docente" class="text-red-500 text-xs">{{ form.errors.id_docente }}</small>
        </div>

        <div class="flex flex-col gap-2">
          <label for="id_grupo_materia" class="font-semibold text-sm">Materia - Grupo</label>
          <Select
            id="id_grupo_materia"
            v-model="form.id_grupo_materia"
            :options="gruposMaterias"
            optionValue="id"
            optionLabel="nombre_completo"
            placeholder="Selecciona materia y grupo"
            :class="{ 'p-invalid': form.errors.id_grupo_materia }"
            class="w-full text-sm"
            filter
          />
          <small class="text-gray-500 text-xs">
            Carga horaria: {{ getCargaHoraria() }} bloques de 45 min
          </small>
          <small v-if="form.errors.id_grupo_materia" class="text-red-500 text-xs">{{ form.errors.id_grupo_materia }}</small>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div class="flex flex-col gap-2">
            <label for="dia" class="font-semibold text-sm">Día</label>
            <Select
              id="dia"
              v-model="diaSeleccionado"
              :options="dias"
              optionValue="id"
              optionLabel="nombre"
              placeholder="Selecciona día"
              class="w-full text-sm"
            />
          </div>

          <div class="flex flex-col gap-2">
            <label for="hora" class="font-semibold text-sm">Bloque Horario</label>
            <Select
              id="hora"
              v-model="horaSeleccionada"
              :options="horas"
              optionValue="id"
              placeholder="Selecciona hora"
              class="w-full text-sm"
            >
              <template #value="slotProps">
                <span v-if="slotProps.value">{{ getHoraPeriodo(slotProps.value) }}</span>
                <span v-else>{{ slotProps.placeholder }}</span>
              </template>
              <template #option="slotProps">
                <span>{{ formatTime(slotProps.option.hora_inicio) }} - {{ formatTime(slotProps.option.hora_fin) }}</span>
              </template>
            </Select>
          </div>
        </div>

        <div class="flex flex-col gap-2">
          <label for="id_aula" class="font-semibold text-sm">Aula</label>
          <Select
            id="id_aula"
            v-model="form.id_aula"
            :options="aulas"
            optionValue="id"
            optionLabel="nombre"
            placeholder="Selecciona aula"
            :class="{ 'p-invalid': form.errors.id_aula }"
            class="w-full text-sm"
            filter
          />
          <small v-if="form.errors.id_aula" class="text-red-500 text-xs">{{ form.errors.id_aula }}</small>
        </div>

        <div v-if="form.errors.conflicto" class="p-3 bg-red-50 border border-red-200 rounded">
          <i class="pi pi-exclamation-triangle text-red-600 mr-2"></i>
          <span class="text-red-800 text-xs">{{ form.errors.conflicto }}</span>
        </div>

        <div class="flex flex-col-reverse sm:flex-row gap-2 pt-4">
          <Button
            type="button"
            label="Cancelar"
            @click="closeDialog"
            severity="secondary"
            outlined
            class="w-full sm:w-auto text-sm"
          />
          <Button
            type="submit"
            label="Crear"
            :loading="form.processing"
            class="w-full sm:w-auto text-sm"
          />
        </div>
      </form>
    </Dialog>

    <!-- Dialog detalle de asignación -->
    <Dialog
      v-model:visible="detalleVisible"
      header="Detalle de Asignación"
      :modal="true"
      class="w-[95vw] sm:w-[500px]"
    >
      <div v-if="asignacionDetalle" class="space-y-3">
        <div class="grid grid-cols-2 gap-3 text-sm">
          <div>
            <strong>Materia:</strong><br>
            {{ asignacionDetalle.materia_nombre }}
          </div>
          <div>
            <strong>Grupo:</strong><br>
            {{ asignacionDetalle.grupo_nombre }}
          </div>
          <div>
            <strong>Docente:</strong><br>
            {{ asignacionDetalle.docente_nombre }}
          </div>
          <div>
            <strong>Aula:</strong><br>
            {{ asignacionDetalle.aula_nombre }}
          </div>
          <div>
            <strong>Día:</strong><br>
            {{ asignacionDetalle.dia_nombre }}
          </div>
          <div>
            <strong>Horario:</strong><br>
            {{ formatTime(asignacionDetalle.hora_inicio) }} - {{ formatTime(asignacionDetalle.hora_fin) }}
          </div>
        </div>
      </div>
      <template #footer>
        <div class="flex gap-2">
          <Button
            label="Cerrar"
            @click="detalleVisible = false"
            severity="secondary"
            outlined
            class="flex-1 text-sm"
          />
          <Button
            label="Duplicar"
            icon="pi pi-copy"
            @click="duplicarAsignacion"
            severity="info"
            outlined
            class="flex-1 text-sm"
          />
          <Button
            label="Eliminar"
            @click="eliminarAsignacion"
            severity="danger"
            :loading="deleteForm.processing"
            class="flex-1 text-sm"
          />
        </div>
      </template>
    </Dialog>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch, reactive } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Select from 'primevue/select';
import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';

interface Gestion {
  id: number;
  año: number;
  semestre: number;
}

interface Docente {
  codigo: string;
  nombre_completo: string;
}

interface Aula {
  id: number;
  nombre: string;
  numero_modulo: number;
  numero: number;
}

interface Dia {
  id: number;
  nombre: string;
}

interface Hora {
  id: number;
  hora_inicio: string;
  hora_fin: string;
}

interface Horario {
  id: number;
  id_dia: number;
  id_hora: number;
  dia_nombre: string;
  hora_inicio: string;
  hora_fin: string;
}

interface GrupoMateria {
  id: number;
  nombre_completo: string;
  materia_nombre: string;
  grupo_nombre: string;
  carga_horaria: number;
}

interface Asignacion {
  id: number;
  id_docente: string;
  id_grupo_materia: number;
  id_horario: number;
  id_aula: number;
  id_gestion: number;
  docente_nombre: string;
  materia_nombre: string;
  grupo_nombre: string;
  dia_id: number;
  dia_nombre: string;
  hora_id: number;
  hora_inicio: string;
  hora_fin: string;
  aula_nombre: string;
  carga_horaria: number;
}

const props = defineProps<{
  gestiones: Gestion[];
  gestionSeleccionada: number | null;
  docentes: Docente[];
  aulas: Aula[];
  dias: Dia[];
  horas: Hora[];
  horarios: Horario[];
  gruposMaterias: GrupoMateria[];
  asignaciones: Asignacion[];
}>();

const gestionActual = ref<number | null>(props.gestionSeleccionada || (props.gestiones.length > 0 ? props.gestiones[0].id : null));
const vistaActual = ref<'docente' | 'aula'>('docente');
const docenteSeleccionado = ref<string | null>(null);
const aulaSeleccionada = ref<number | null>(null);
const dialogVisible = ref(false);
const rapidDialogVisible = ref(false);
const detalleVisible = ref(false);
const asignacionDetalle = ref<Asignacion | null>(null);
const diaSeleccionado = ref<number | null>(null);
const horaSeleccionada = ref<number | null>(null);

// Colores para las asignaciones
const colores = [
  { bg: '#E3F2FD', border: '#2196F3', text: '#0D47A1' },      // Azul
  { bg: '#F3E5F5', border: '#9C27B0', text: '#4A148C' },      // Púrpura
  { bg: '#E8F5E9', border: '#4CAF50', text: '#1B5E20' },      // Verde
  { bg: '#FFF3E0', border: '#FF9800', text: '#E65100' },      // Naranja
  { bg: '#FCE4EC', border: '#E91E63', text: '#880E4F' },      // Rosa
  { bg: '#E0F2F1', border: '#009688', text: '#004D40' },      // Teal
  { bg: '#FFF9C4', border: '#FBC02D', text: '#F57F17' },      // Amarillo
  { bg: '#EFEBE9', border: '#795548', text: '#3E2723' },      // Café
];

// Bloques para asignación rápida
interface BloqueRapido {
  dia: number | null;
  hora_inicio: number | null;
  hora_fin: number | null;
  aula: number | null;
}

const bloquesRapidos = ref<BloqueRapido[]>([]);

const rapidForm = reactive({
  id_docente: null as string | null,
  id_grupo_materia: null as number | null,
  processing: false,
  errors: null as string | null,
});

const form = useForm({
  id_docente: null as string | null,
  id_grupo_materia: null as number | null,
  id_horario: null as number | null,
  id_aula: null as number | null,
  id_gestion: gestionActual.value,
});

const deleteForm = useForm({});

// Computed para obtener horario filtrado
const horarioId = computed(() => {
  if (!diaSeleccionado.value || !horaSeleccionada.value) return null;
  const horario = props.horarios.find(
    h => h.id_dia === diaSeleccionado.value && h.id_hora === horaSeleccionada.value
  );
  return horario?.id || null;
});

// Watch para actualizar id_horario
watch([diaSeleccionado, horaSeleccionada], () => {
  form.id_horario = horarioId.value;
});

// Sincronizar gestión cuando cambian los props (al volver de otra página)
watch(() => props.gestionSeleccionada, (newVal) => {
  console.log('Props gestionSeleccionada cambió a:', newVal);
  if (newVal && newVal !== gestionActual.value) {
    console.log('Actualizando gestionActual de', gestionActual.value, 'a', newVal);
    gestionActual.value = newVal;
  }
}, { immediate: true });

// Recargar asignaciones cuando el usuario cambia la gestión
watch(gestionActual, (newVal, oldVal) => {
  console.log('gestionActual cambió de', oldVal, 'a', newVal);
  // Solo recargar si el cambio fue iniciado por el usuario (no por los props)
  if (newVal && oldVal && newVal !== oldVal && newVal !== props.gestionSeleccionada) {
    console.log('Recargando asignaciones para gestión:', newVal);
    router.visit(`/asignaciones?gestion_id=${newVal}`, {
      preserveScroll: true,
      // No usar 'only' para asegurar que todos los datos se actualicen correctamente
    });
  }
});

const asignacionesFiltradas = computed(() => {
  if (vistaActual.value === 'docente' && docenteSeleccionado.value) {
    return props.asignaciones.filter(a => a.id_docente === docenteSeleccionado.value);
  }
  if (vistaActual.value === 'aula' && aulaSeleccionada.value) {
    return props.asignaciones.filter(a => a.id_aula === aulaSeleccionada.value);
  }
  // Si no hay selección, no mostrar nada
  return [];
});

const cambiarVista = (vista: 'docente' | 'aula') => {
  vistaActual.value = vista;
  // Limpiar selecciones al cambiar de vista
  docenteSeleccionado.value = null;
  aulaSeleccionada.value = null;
};

const formatTime = (time: string) => {
  if (!time) return '';
  return time.substring(0, 5);
};

const getGestionNombre = (id: number | null) => {
  if (!id) {
    console.log('getGestionNombre: ID es null o undefined');
    return '';
  }
  console.log('getGestionNombre: Buscando gestión con id:', id, 'tipo:', typeof id);
  console.log('getGestionNombre: Gestiones disponibles:', props.gestiones);
  
  // Convertir a número para comparación segura
  const numId = Number(id);
  const gestion = props.gestiones.find(g => Number(g.id) === numId);
  
  if (!gestion) {
    console.warn('No se encontró gestión con id:', id);
    return '';
  }
  
  const nombre = `${gestion.año} - Semestre ${gestion.semestre}`;
  console.log('getGestionNombre: Retornando:', nombre);
  return nombre;
};

const getHoraPeriodo = (id: number) => {
  const hora = props.horas.find(h => h.id === id);
  if (!hora) return '';
  return `${formatTime(hora.hora_inicio)} - ${formatTime(hora.hora_fin)}`;
};

const getCargaHoraria = () => {
  if (!form.id_grupo_materia) return 0;
  const gm = props.gruposMaterias.find(g => g.id === form.id_grupo_materia);
  return gm?.carga_horaria || 0;
};

const getAsignacion = (diaId: number, horaId: number) => {
  return asignacionesFiltradas.value.filter(
    a => a.dia_id === diaId && a.hora_id === horaId
  );
};

const getCeldaClass = (diaId: number, horaId: number) => {
  const asigs = getAsignacion(diaId, horaId);
  return asigs.length > 0 ? '' : 'bg-gray-50';
};

const getAsignacionStyle = (index: number) => {
  const color = colores[index % colores.length];
  return {
    backgroundColor: color.bg,
    borderColor: color.border,
    borderWidth: '1px',
  };
};

const getTextColor = (index: number, opacity: number = 1) => {
  const color = colores[index % colores.length];
  const rgb = color.text;
  if (opacity < 1) {
    // Convertir hex a rgba
    const r = parseInt(rgb.slice(1, 3), 16);
    const g = parseInt(rgb.slice(3, 5), 16);
    const b = parseInt(rgb.slice(5, 7), 16);
    return `rgba(${r}, ${g}, ${b}, ${opacity})`;
  }
  return rgb;
};

const clickCelda = (diaId: number, horaId: number) => {
  // Si la celda está vacía, abrir formulario de creación con datos pre-llenados
  const asigs = getAsignacion(diaId, horaId);
  if (asigs.length === 0) {
    diaSeleccionado.value = diaId;
    horaSeleccionada.value = horaId;
    openCreateDialog();
  }
};

const openCreateDialog = () => {
  if (!diaSeleccionado.value || !horaSeleccionada.value) {
    form.reset();
  }
  form.id_gestion = gestionActual.value;
  form.clearErrors();
  dialogVisible.value = true;
};

const irAImportar = () => {
  router.visit('/asignaciones/importar');
};

const openRapidDialog = () => {
  rapidForm.id_docente = null;
  rapidForm.id_grupo_materia = null;
  rapidForm.errors = null;
  bloquesRapidos.value = [];
  rapidDialogVisible.value = true;
};

const closeRapidDialog = () => {
  rapidDialogVisible.value = false;
  rapidForm.id_docente = null;
  rapidForm.id_grupo_materia = null;
  rapidForm.errors = null;
  bloquesRapidos.value = [];
};

const agregarBloqueRapido = () => {
  bloquesRapidos.value.push({
    dia: null,
    hora_inicio: null,
    hora_fin: null,
    aula: null,
  });
};

const eliminarBloqueRapido = (index: number) => {
  bloquesRapidos.value.splice(index, 1);
};

const getCargaHorariaRapida = () => {
  if (!rapidForm.id_grupo_materia) return 0;
  const gm = props.gruposMaterias.find(g => g.id === rapidForm.id_grupo_materia);
  return gm?.carga_horaria || 0;
};

const calcularBloquesConsecutivos = (horaInicioId: number | null, horaFinId: number | null) => {
  if (!horaInicioId || !horaFinId) return 0;
  
  const horaInicio = props.horas.find(h => h.id === horaInicioId);
  const horaFin = props.horas.find(h => h.id === horaFinId);
  
  if (!horaInicio || !horaFin) return 0;
  
  // Contar cuántos bloques hay entre inicio y fin (inclusivo)
  const bloquesEnRango = props.horas.filter(h => 
    h.hora_inicio >= horaInicio.hora_inicio && h.hora_fin <= horaFin.hora_fin
  );
  
  return bloquesEnRango.length;
};

const guardarAsignacionesRapidas = () => {
  rapidForm.errors = null;
  
  // Validaciones
  if (!rapidForm.id_docente || !rapidForm.id_grupo_materia) {
    rapidForm.errors = 'Debes seleccionar docente y materia-grupo';
    return;
  }

  if (bloquesRapidos.value.length === 0) {
    rapidForm.errors = 'Debes agregar al menos un bloque';
    return;
  }

  // Validar que todos los bloques estén completos
  const bloqueIncompleto = bloquesRapidos.value.find(b => !b.dia || !b.hora_inicio || !b.hora_fin || !b.aula);
  if (bloqueIncompleto) {
    rapidForm.errors = 'Todos los bloques deben tener día, hora de inicio, hora de fin y aula';
    return;
  }

  rapidForm.processing = true;

  // Preparar datos para enviar al backend
  const bloquesParaEnviar = bloquesRapidos.value.map(bloque => ({
    id_dia: bloque.dia,
    id_hora_inicio: bloque.hora_inicio,
    id_hora_fin: bloque.hora_fin,
    id_aula: bloque.aula,
  }));

  router.post('/asignaciones/multiple', {
    id_docente: rapidForm.id_docente,
    id_grupo_materia: rapidForm.id_grupo_materia,
    id_gestion: gestionActual.value,
    bloques: bloquesParaEnviar,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      rapidForm.processing = false;
      closeRapidDialog();
    },
    onError: (errors: any) => {
      rapidForm.errors = errors.conflicto || 'Error al crear asignaciones';
      rapidForm.processing = false;
    },
  });
};

const duplicarAsignacion = () => {
  if (asignacionDetalle.value) {
    form.id_docente = asignacionDetalle.value.id_docente;
    form.id_grupo_materia = asignacionDetalle.value.id_grupo_materia;
    form.id_aula = null; // Dejar aula vacía para que el usuario la cambie
    diaSeleccionado.value = null;
    horaSeleccionada.value = null;
    form.id_horario = null;
    form.id_gestion = gestionActual.value;
    detalleVisible.value = false;
    dialogVisible.value = true;
  }
};

const closeDialog = () => {
  dialogVisible.value = false;
  form.reset();
  form.clearErrors();
};

const submitForm = () => {
  form.post('/asignaciones', {
    onSuccess: () => {
      closeDialog();
    },
  });
};

const verDetalleAsignacion = (asignacion: Asignacion) => {
  asignacionDetalle.value = asignacion;
  detalleVisible.value = true;
};

const eliminarAsignacion = () => {
  if (asignacionDetalle.value) {
    deleteForm.delete(`/asignaciones/${asignacionDetalle.value.id}`, {
      onSuccess: () => {
        detalleVisible.value = false;
        asignacionDetalle.value = null;
      },
    });
  }
};

// Funciones de exportación
const getTituloExportacion = () => {
  const gestion = props.gestiones.find(g => g.id === gestionActual.value);
  const gestionNombre = gestion ? `${gestion.año} - Semestre ${gestion.semestre}` : '';
  
  if (vistaActual.value === 'docente' && docenteSeleccionado.value) {
    const docente = props.docentes.find(d => d.codigo === docenteSeleccionado.value);
    return `Horario de ${docente?.nombre_completo || 'Docente'} - ${gestionNombre}`;
  } else if (vistaActual.value === 'aula' && aulaSeleccionada.value) {
    const aula = props.aulas.find(a => a.id === aulaSeleccionada.value);
    return `Horario de ${aula?.nombre || 'Aula'} - ${gestionNombre}`;
  }
  return `Horario - ${gestionNombre}`;
};

const exportarPDF = () => {
  const doc = new jsPDF('l', 'mm', 'a4'); // landscape
  const titulo = getTituloExportacion();
  
  // Título
  doc.setFontSize(16);
  doc.text(titulo, 15, 15);
  
  // Preparar datos para la tabla
  const headers = ['Hora', ...props.dias.map(d => d.nombre)];
  const body: string[][] = [];
  
  props.horas.forEach(hora => {
    const row: string[] = [`${formatTime(hora.hora_inicio)} - ${formatTime(hora.hora_fin)}`];
    
    props.dias.forEach(dia => {
      const asignaciones = asignacionesFiltradas.value.filter(
        a => a.dia_id === dia.id && a.hora_id === hora.id
      );
      
      if (asignaciones.length > 0) {
        const textos = asignaciones.map(a => 
          `${a.materia_nombre}\nGrupo: ${a.grupo_nombre}\n${a.docente_nombre}\n${a.aula_nombre}`
        );
        row.push(textos.join('\n---\n'));
      } else {
        row.push('');
      }
    });
    
    body.push(row);
  });
  
  // Generar tabla
  autoTable(doc, {
    head: [headers],
    body: body,
    startY: 25,
    styles: { 
      fontSize: 8,
      cellPadding: 3,
      overflow: 'linebreak',
    },
    headStyles: {
      fillColor: [66, 139, 202],
      textColor: 255,
      fontStyle: 'bold',
    },
    columnStyles: {
      0: { cellWidth: 30 }, // Columna de horas más ancha
    },
  });
  
  // Guardar
  const filename = `horario_${vistaActual.value}_${Date.now()}.pdf`;
  doc.save(filename);
};

const exportarCSV = () => {
  const titulo = getTituloExportacion();
  let csv = titulo + '\n\n';
  
  // Headers
  csv += 'Hora,' + props.dias.map(d => d.nombre).join(',') + '\n';
  
  // Datos
  props.horas.forEach(hora => {
    const row: string[] = [`${formatTime(hora.hora_inicio)}-${formatTime(hora.hora_fin)}`];
    
    props.dias.forEach(dia => {
      const asignaciones = asignacionesFiltradas.value.filter(
        a => a.dia_id === dia.id && a.hora_id === hora.id
      );
      
      if (asignaciones.length > 0) {
        const textos = asignaciones.map(a => 
          `${a.materia_nombre} | Grupo: ${a.grupo_nombre} | ${a.docente_nombre} | ${a.aula_nombre}`
        );
        row.push(`"${textos.join(' / ')}"`);
      } else {
        row.push('');
      }
    });
    
    csv += row.join(',') + '\n';
  });
  
  // Descargar
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  const url = URL.createObjectURL(blob);
  link.setAttribute('href', url);
  link.setAttribute('download', `horario_${vistaActual.value}_${Date.now()}.csv`);
  link.style.visibility = 'hidden';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};
</script>
