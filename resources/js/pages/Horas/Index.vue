<template>
  <AppLayout title="Gestión de Bloques Horarios">
    <div class="flex flex-col h-full space-y-4 lg:space-y-6">
    <!-- Header con botón de crear -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 shrink-0">
      <div>
        <h2 class="text-xl lg:text-2xl font-bold text-gray-800 dark:text-white">Gestión de Bloques Horarios</h2>
        <p class="text-sm lg:text-base text-gray-600 dark:text-gray-400 mt-1">Administra los bloques de tiempo</p>
      </div>
      <Button
        label="Nuevo Bloque"
        icon="pi pi-plus"
        @click="openCreateDialog"
        severity="success"
        class="w-full sm:w-auto"
      />
    </div>

    <!-- Tabla de horas con scroll -->
    <div class="flex-1 overflow-hidden">
      <Card class="h-full flex flex-col">
        <template #content>
          <div class="flex-1 overflow-auto">
            <DataTable
              :value="horas"
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
                field="hora_inicio" 
                header="Hora Inicio" 
                sortable 
                :style="{ minWidth: '10rem' }"
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
                :style="{ minWidth: '10rem' }"
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
                header="Periodo" 
                :style="{ minWidth: '12rem' }"
                headerClass="text-xs sm:text-sm"
                bodyClass="text-xs sm:text-sm"
                class="hidden md:table-cell"
              >
                <template #body="slotProps">
                  <span class="text-xs sm:text-sm font-medium">
                    {{ formatTime(slotProps.data.hora_inicio) }} - {{ formatTime(slotProps.data.hora_fin) }}
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

    <!-- Dialog para Crear/Editar Hora -->
    <Dialog
      v-model:visible="dialogVisible"
      :header="isEditing ? 'Editar Bloque Horario' : 'Crear Bloque Horario'"
      :modal="true"
      class="w-[95vw] sm:w-[500px]"
      :dismissableMask="true"
    >
      <form @submit.prevent="submitForm" class="space-y-3 sm:space-y-4 mt-2 sm:mt-4">
        <div class="flex flex-col gap-2">
          <label for="hora_inicio" class="font-semibold text-sm">Hora de Inicio</label>
          <InputText
            id="hora_inicio"
            v-model="form.hora_inicio"
            type="time"
            :class="{ 'p-invalid': form.errors.hora_inicio }"
            class="w-full text-sm"
          />
          <small class="text-gray-500 text-xs">Formato: HH:MM (24 horas)</small>
          <small v-if="form.errors.hora_inicio" class="text-red-500 text-xs">{{ form.errors.hora_inicio }}</small>
        </div>

        <div class="flex flex-col gap-2">
          <label for="hora_fin" class="font-semibold text-sm">Hora de Fin</label>
          <InputText
            id="hora_fin"
            v-model="form.hora_fin"
            type="time"
            :class="{ 'p-invalid': form.errors.hora_fin }"
            class="w-full text-sm"
          />
          <small class="text-gray-500 text-xs">Debe ser posterior a la hora de inicio</small>
          <small v-if="form.errors.hora_fin" class="text-red-500 text-xs">{{ form.errors.hora_fin }}</small>
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
          ¿Estás seguro de que deseas eliminar el bloque horario 
          <strong>{{ horaToDelete ? formatTime(horaToDelete.hora_inicio) : '' }} - {{ horaToDelete ? formatTime(horaToDelete.hora_fin) : '' }}</strong>?
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
            @click="deleteHora"
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
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';

interface Hora {
  id: number;
  hora_inicio: string;
  hora_fin: string;
  horarios_count?: number;
}

const props = defineProps<{
  horas: Hora[];
}>();

const dialogVisible = ref(false);
const deleteDialogVisible = ref(false);
const isEditing = ref(false);
const horaToDelete = ref<Hora | null>(null);
const currentId = ref<number | null>(null);

const form = useForm({
  hora_inicio: '',
  hora_fin: '',
});

const deleteForm = useForm({});

const formatTime = (time: string) => {
  if (!time) return '';
  // time viene como "HH:MM:SS", retornamos "HH:MM"
  return time.substring(0, 5);
};

const openCreateDialog = () => {
  isEditing.value = false;
  currentId.value = null;
  form.reset();
  form.clearErrors();
  dialogVisible.value = true;
};

const openEditDialog = (hora: Hora) => {
  isEditing.value = true;
  currentId.value = hora.id;
  form.hora_inicio = formatTime(hora.hora_inicio);
  form.hora_fin = formatTime(hora.hora_fin);
  form.clearErrors();
  dialogVisible.value = true;
};

const openDeleteDialog = (hora: Hora) => {
  horaToDelete.value = hora;
  deleteDialogVisible.value = true;
};

const closeDialog = () => {
  dialogVisible.value = false;
  form.reset();
  form.clearErrors();
};

const submitForm = () => {
  if (isEditing.value && currentId.value) {
    form.put(`/horas/${currentId.value}`, {
      onSuccess: () => {
        closeDialog();
      },
    });
  } else {
    form.post('/horas', {
      onSuccess: () => {
        closeDialog();
      },
    });
  }
};

const deleteHora = () => {
  if (horaToDelete.value) {
    deleteForm.delete(`/horas/${horaToDelete.value.id}`, {
      onSuccess: () => {
        deleteDialogVisible.value = false;
        horaToDelete.value = null;
      },
    });
  }
};
</script>
