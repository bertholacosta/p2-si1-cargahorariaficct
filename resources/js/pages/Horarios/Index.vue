<template>
  <AppLayout title="Gestión de Horarios">
    <div class="flex flex-col h-full space-y-4 lg:space-y-6">
    <!-- Header con botón de crear -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 shrink-0">
      <div>
        <h2 class="text-xl lg:text-2xl font-bold text-gray-800 dark:text-white">Gestión de Horarios</h2>
        <p class="text-sm lg:text-base text-gray-600 dark:text-gray-400 mt-1">Administra las combinaciones día-hora</p>
      </div>
      <Button
        label="Nuevo Horario"
        icon="pi pi-plus"
        @click="openCreateDialog"
        severity="success"
        class="w-full sm:w-auto"
      />
    </div>

    <!-- Tabla de horarios con scroll -->
    <div class="flex-1 overflow-hidden">
      <Card class="h-full flex flex-col">
        <template #content>
          <div class="flex-1 overflow-auto">
            <DataTable
              :value="horarios"
              :paginator="true"
              :rows="10"
              :rowsPerPageOptions="[5, 10, 20, 50]"
              stripedRows
              showGridlines
              responsiveLayout="scroll"
              :breakpoint="'960px'"
              class="text-xs sm:text-sm"
            >
              <Column 
                field="id" 
                header="ID" 
                sortable 
                :style="{ minWidth: '4rem' }"
                headerClass="text-xs sm:text-sm"
                bodyClass="text-xs sm:text-sm"
                class="hidden sm:table-cell"
              ></Column>
              <Column 
                field="dia_nombre" 
                header="Día" 
                sortable 
                :style="{ minWidth: '10rem' }"
                headerClass="text-xs sm:text-sm"
                bodyClass="text-xs sm:text-sm"
              >
                <template #body="slotProps">
                  <Tag 
                    :value="slotProps.data.dia_nombre" 
                    severity="info"
                    class="text-xs font-semibold"
                  />
                </template>
              </Column>
              <Column 
                field="hora_inicio" 
                header="Hora Inicio" 
                sortable 
                :style="{ minWidth: '9rem' }"
                headerClass="text-xs sm:text-sm"
                bodyClass="text-xs sm:text-sm"
              >
                <template #body="slotProps">
                  <Tag 
                    :value="formatTime(slotProps.data.hora_inicio)" 
                    severity="success"
                    class="text-xs font-semibold"
                  />
                </template>
              </Column>
              <Column 
                field="hora_fin" 
                header="Hora Fin" 
                sortable 
                :style="{ minWidth: '9rem' }"
                headerClass="text-xs sm:text-sm"
                bodyClass="text-xs sm:text-sm"
              >
                <template #body="slotProps">
                  <Tag 
                    :value="formatTime(slotProps.data.hora_fin)" 
                    severity="warn"
                    class="text-xs font-semibold"
                  />
                </template>
              </Column>
              <Column 
                header="Horario Completo" 
                :style="{ minWidth: '18rem' }"
                headerClass="text-xs sm:text-sm"
                bodyClass="text-xs sm:text-sm"
                class="hidden lg:table-cell"
              >
                <template #body="slotProps">
                  <span class="text-xs sm:text-sm font-medium">
                    {{ slotProps.data.dia_nombre }} {{ formatTime(slotProps.data.hora_inicio) }} - {{ formatTime(slotProps.data.hora_fin) }}
                  </span>
                </template>
              </Column>
              <Column 
                header="Acciones" 
                :style="{ minWidth: '8rem' }"
                headerClass="text-xs sm:text-sm"
                bodyClass="text-xs sm:text-sm"
              >
                <template #body="slotProps">
                  <div class="flex gap-1 sm:gap-2">
                    <Button
                      icon="pi pi-pencil"
                      @click="openEditDialog(slotProps.data)"
                      severity="warning"
                      outlined
                      size="small"
                      class="!p-1 sm:!p-2"
                      v-tooltip.top="'Editar'"
                    />
                    <Button
                      icon="pi pi-trash"
                      @click="openDeleteDialog(slotProps.data)"
                      severity="danger"
                      outlined
                      size="small"
                      class="!p-1 sm:!p-2"
                      v-tooltip.top="'Eliminar'"
                    />
                  </div>
                </template>
              </Column>
            </DataTable>
          </div>
        </template>
      </Card>
    </div>
    </div>

    <!-- Dialog para Crear/Editar Horario -->
    <Dialog
      v-model:visible="dialogVisible"
      :header="isEditing ? 'Editar Horario' : 'Crear Horario'"
      :modal="true"
      class="w-[95vw] sm:w-[500px]"
      :dismissableMask="true"
    >
      <form @submit.prevent="submitForm" class="space-y-3 sm:space-y-4 mt-2 sm:mt-4">
        <div class="flex flex-col gap-2">
          <label for="id_dia" class="font-semibold text-sm">Día</label>
          <Select
            id="id_dia"
            v-model="form.id_dia"
            :options="dias"
            optionLabel="nombre"
            optionValue="id"
            placeholder="Selecciona un día"
            :class="{ 'p-invalid': form.errors.id_dia }"
            class="w-full text-sm"
          >
            <template #value="slotProps">
              <span v-if="slotProps.value">{{ getDiaName(slotProps.value) }}</span>
              <span v-else>{{ slotProps.placeholder }}</span>
            </template>
            <template #option="slotProps">
              <div class="flex items-center gap-2">
                <Tag :value="slotProps.option.nombre" severity="info" class="text-xs" />
              </div>
            </template>
          </Select>
          <small v-if="form.errors.id_dia" class="text-red-500 text-xs">{{ form.errors.id_dia }}</small>
        </div>

        <div class="flex flex-col gap-2">
          <label for="id_hora" class="font-semibold text-sm">Bloque Horario</label>
          <Select
            id="id_hora"
            v-model="form.id_hora"
            :options="horas"
            optionValue="id"
            placeholder="Selecciona un bloque horario"
            :class="{ 'p-invalid': form.errors.id_hora }"
            class="w-full text-sm"
          >
            <template #value="slotProps">
              <span v-if="slotProps.value">{{ getHoraPeriodo(slotProps.value) }}</span>
              <span v-else>{{ slotProps.placeholder }}</span>
            </template>
            <template #option="slotProps">
              <div class="flex items-center gap-2">
                <Tag :value="formatTime(slotProps.option.hora_inicio)" severity="success" class="text-xs" />
                <span class="text-xs">-</span>
                <Tag :value="formatTime(slotProps.option.hora_fin)" severity="warn" class="text-xs" />
              </div>
            </template>
          </Select>
          <small class="text-gray-500 text-xs">La combinación día-hora debe ser única</small>
          <small v-if="form.errors.id_hora" class="text-red-500 text-xs">{{ form.errors.id_hora }}</small>
        </div>

        <div class="flex flex-col-reverse sm:flex-row gap-2 sm:gap-3 pt-2 sm:pt-4">
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
            :label="isEditing ? 'Actualizar' : 'Crear'"
            :loading="form.processing"
            class="w-full sm:w-auto text-sm"
          />
        </div>
      </form>
    </Dialog>

    <!-- Dialog para Confirmar Eliminación -->
    <Dialog
      v-model:visible="deleteDialogVisible"
      header="Confirmar Eliminación"
      :modal="true"
      class="w-[95vw] sm:w-[450px]"
    >
      <div class="flex items-center gap-3 sm:gap-4">
        <i class="pi pi-exclamation-triangle text-3xl sm:text-4xl text-red-500"></i>
        <span class="text-sm sm:text-base">
          ¿Estás seguro de que deseas eliminar el horario 
          <strong>{{ horarioToDelete?.dia_nombre }} {{ horarioToDelete ? formatTime(horarioToDelete.hora_inicio) : '' }} - {{ horarioToDelete ? formatTime(horarioToDelete.hora_fin) : '' }}</strong>?
        </span>
      </div>
      <template #footer>
        <div class="flex flex-col-reverse sm:flex-row gap-2 sm:gap-3">
          <Button
            label="Cancelar"
            @click="deleteDialogVisible = false"
            severity="secondary"
            outlined
            class="w-full sm:w-auto text-sm"
          />
          <Button
            label="Eliminar"
            @click="deleteHorario"
            severity="danger"
            :loading="deleteForm.processing"
            class="w-full sm:w-auto text-sm"
          />
        </div>
      </template>
    </Dialog>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import Select from 'primevue/select';
import Tag from 'primevue/tag';

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

const props = defineProps<{
  horarios: Horario[];
  dias: Dia[];
  horas: Hora[];
}>();

const dialogVisible = ref(false);
const deleteDialogVisible = ref(false);
const isEditing = ref(false);
const horarioToDelete = ref<Horario | null>(null);
const currentId = ref<number | null>(null);

const form = useForm({
  id_dia: null as number | null,
  id_hora: null as number | null,
});

const deleteForm = useForm({});

const formatTime = (time: string) => {
  if (!time) return '';
  return time.substring(0, 5);
};

const getDiaName = (id: number) => {
  const dia = props.dias.find(d => d.id === id);
  return dia?.nombre || '';
};

const getHoraPeriodo = (id: number) => {
  const hora = props.horas.find(h => h.id === id);
  if (!hora) return '';
  return `${formatTime(hora.hora_inicio)} - ${formatTime(hora.hora_fin)}`;
};

const openCreateDialog = () => {
  isEditing.value = false;
  currentId.value = null;
  form.reset();
  form.clearErrors();
  dialogVisible.value = true;
};

const openEditDialog = (horario: Horario) => {
  isEditing.value = true;
  currentId.value = horario.id;
  form.id_dia = horario.id_dia;
  form.id_hora = horario.id_hora;
  form.clearErrors();
  dialogVisible.value = true;
};

const openDeleteDialog = (horario: Horario) => {
  horarioToDelete.value = horario;
  deleteDialogVisible.value = true;
};

const closeDialog = () => {
  dialogVisible.value = false;
  form.reset();
  form.clearErrors();
};

const submitForm = () => {
  if (isEditing.value && currentId.value) {
    form.put(`/horarios/${currentId.value}`, {
      onSuccess: () => {
        closeDialog();
      },
    });
  } else {
    form.post('/horarios', {
      onSuccess: () => {
        closeDialog();
      },
    });
  }
};

const deleteHorario = () => {
  if (horarioToDelete.value) {
    deleteForm.delete(`/horarios/${horarioToDelete.value.id}`, {
      onSuccess: () => {
        deleteDialogVisible.value = false;
        horarioToDelete.value = null;
      },
    });
  }
};
</script>
