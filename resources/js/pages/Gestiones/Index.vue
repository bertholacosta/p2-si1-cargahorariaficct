<template>
  <AppLayout title="Gestión de Gestiones">
    <div class="flex flex-col h-full space-y-4 lg:space-y-6">
    <!-- Header con botón de crear -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 shrink-0">
      <div>
        <h2 class="text-xl lg:text-2xl font-bold text-gray-800 dark:text-white">Gestión de Gestiones</h2>
        <p class="text-sm lg:text-base text-gray-600 dark:text-gray-400 mt-1">Administra los periodos académicos</p>
      </div>
      <Button
        label="Nueva Gestión"
        icon="pi pi-plus"
        @click="openCreateDialog"
        severity="success"
        class="w-full sm:w-auto"
      />
    </div>

    <!-- Tabla de gestiones con scroll -->
    <div class="flex-1 overflow-hidden">
      <Card class="h-full flex flex-col">
        <template #content>
          <div class="flex-1 overflow-auto">
            <DataTable
              :value="gestiones"
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
                field="año" 
                header="Año" 
                sortable 
                :style="{ minWidth: '7rem' }"
                headerClass="text-xs sm:text-sm"
                bodyClass="text-xs sm:text-sm"
              >
                <template #body="slotProps">
                  <Tag 
                    :value="slotProps.data.año" 
                    severity="info"
                    class="text-xs font-bold"
                  />
                </template>
              </Column>
              <Column 
                field="semestre" 
                header="Semestre" 
                sortable 
                :style="{ minWidth: '9rem' }"
                headerClass="text-xs sm:text-sm"
                bodyClass="text-xs sm:text-sm"
              >
                <template #body="slotProps">
                  <Tag 
                    :value="'Semestre ' + slotProps.data.semestre" 
                    :severity="slotProps.data.semestre === 1 ? 'success' : 'warn'"
                    class="text-xs font-semibold"
                  />
                </template>
              </Column>
              <Column 
                field="fecha_inicio" 
                header="Fecha Inicio" 
                sortable 
                :style="{ minWidth: '10rem' }"
                headerClass="text-xs sm:text-sm"
                bodyClass="text-xs sm:text-sm"
              >
                <template #body="slotProps">
                  <span class="text-xs sm:text-sm">
                    {{ formatDate(slotProps.data.fecha_inicio) }}
                  </span>
                </template>
              </Column>
              <Column 
                field="fecha_fin" 
                header="Fecha Fin" 
                sortable 
                :style="{ minWidth: '10rem' }"
                headerClass="text-xs sm:text-sm"
                bodyClass="text-xs sm:text-sm"
              >
                <template #body="slotProps">
                  <span class="text-xs sm:text-sm">
                    {{ formatDate(slotProps.data.fecha_fin) }}
                  </span>
                </template>
              </Column>
              <Column 
                header="Periodo Completo" 
                :style="{ minWidth: '15rem' }"
                headerClass="text-xs sm:text-sm"
                bodyClass="text-xs sm:text-sm"
                class="hidden lg:table-cell"
              >
                <template #body="slotProps">
                  <span class="text-xs sm:text-sm font-medium">
                    {{ slotProps.data.año }} - Semestre {{ slotProps.data.semestre }}
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

    <!-- Dialog para Crear/Editar Gestión -->
    <Dialog
      v-model:visible="dialogVisible"
      :header="isEditing ? 'Editar Gestión' : 'Crear Gestión'"
      :modal="true"
      class="w-[95vw] sm:w-[500px]"
      :dismissableMask="true"
    >
      <form @submit.prevent="submitForm" class="space-y-3 sm:space-y-4 mt-2 sm:mt-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
          <div class="flex flex-col gap-2">
            <label for="año" class="font-semibold text-sm">Año</label>
            <InputNumber
              id="año"
              v-model="form.año"
              :class="{ 'p-invalid': form.errors.año }"
              placeholder="Ej: 2024"
              class="w-full text-sm"
              :useGrouping="false"
              :min="2000"
              :max="2100"
            />
            <small v-if="form.errors.año" class="text-red-500 text-xs">{{ form.errors.año }}</small>
          </div>

          <div class="flex flex-col gap-2">
            <label for="semestre" class="font-semibold text-sm">Semestre</label>
            <Select
              id="semestre"
              v-model="form.semestre"
              :options="[1, 2]"
              placeholder="Selecciona semestre"
              :class="{ 'p-invalid': form.errors.semestre }"
              class="w-full text-sm"
            >
              <template #value="slotProps">
                <span v-if="slotProps.value">Semestre {{ slotProps.value }}</span>
                <span v-else>{{ slotProps.placeholder }}</span>
              </template>
              <template #option="slotProps">
                <span>Semestre {{ slotProps.option }}</span>
              </template>
            </Select>
            <small v-if="form.errors.semestre" class="text-red-500 text-xs">{{ form.errors.semestre }}</small>
          </div>
        </div>

        <div class="flex flex-col gap-2">
          <label for="fecha_inicio" class="font-semibold text-sm">Fecha de Inicio</label>
          <DatePicker
            id="fecha_inicio"
            v-model="form.fecha_inicio"
            :class="{ 'p-invalid': form.errors.fecha_inicio }"
            placeholder="Selecciona fecha de inicio"
            class="w-full text-sm"
            dateFormat="dd/mm/yy"
            showIcon
          />
          <small v-if="form.errors.fecha_inicio" class="text-red-500 text-xs">{{ form.errors.fecha_inicio }}</small>
        </div>

        <div class="flex flex-col gap-2">
          <label for="fecha_fin" class="font-semibold text-sm">Fecha de Fin</label>
          <DatePicker
            id="fecha_fin"
            v-model="form.fecha_fin"
            :class="{ 'p-invalid': form.errors.fecha_fin }"
            placeholder="Selecciona fecha de fin"
            class="w-full text-sm"
            dateFormat="dd/mm/yy"
            showIcon
            :minDate="form.fecha_inicio"
          />
          <small class="text-gray-500 text-xs">Debe ser posterior a la fecha de inicio</small>
          <small v-if="form.errors.fecha_fin" class="text-red-500 text-xs">{{ form.errors.fecha_fin }}</small>
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
          ¿Estás seguro de que deseas eliminar la gestión 
          <strong>{{ gestionToDelete?.año }} - Semestre {{ gestionToDelete?.semestre }}</strong>?
        </span>
      </div>
      <div class="mt-3 text-xs sm:text-sm text-gray-600">
        <p>Periodo: {{ gestionToDelete ? formatDate(gestionToDelete.fecha_inicio) : '' }} - {{ gestionToDelete ? formatDate(gestionToDelete.fecha_fin) : '' }}</p>
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
            @click="deleteGestion"
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
import InputNumber from 'primevue/inputnumber';
import Select from 'primevue/select';
import DatePicker from 'primevue/datepicker';
import Tag from 'primevue/tag';

interface Gestion {
  id: number;
  fecha_inicio: string;
  fecha_fin: string;
  semestre: number;
  año: number;
}

const props = defineProps<{
  gestiones: Gestion[];
}>();

const dialogVisible = ref(false);
const deleteDialogVisible = ref(false);
const isEditing = ref(false);
const gestionToDelete = ref<Gestion | null>(null);
const currentId = ref<number | null>(null);

const form = useForm({
  fecha_inicio: null as Date | null,
  fecha_fin: null as Date | null,
  semestre: null as number | null,
  año: null as number | null,
});

const deleteForm = useForm({});

const formatDate = (dateString: string) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

const openCreateDialog = () => {
  isEditing.value = false;
  currentId.value = null;
  form.reset();
  form.clearErrors();
  dialogVisible.value = true;
};

const openEditDialog = (gestion: Gestion) => {
  isEditing.value = true;
  currentId.value = gestion.id;
  form.fecha_inicio = new Date(gestion.fecha_inicio);
  form.fecha_fin = new Date(gestion.fecha_fin);
  form.semestre = gestion.semestre;
  form.año = gestion.año;
  form.clearErrors();
  dialogVisible.value = true;
};

const openDeleteDialog = (gestion: Gestion) => {
  gestionToDelete.value = gestion;
  deleteDialogVisible.value = true;
};

const closeDialog = () => {
  dialogVisible.value = false;
  form.reset();
  form.clearErrors();
};

const submitForm = () => {
  if (isEditing.value && currentId.value) {
    form.put(`/gestiones/${currentId.value}`, {
      onSuccess: () => {
        closeDialog();
      },
    });
  } else {
    form.post('/gestiones', {
      onSuccess: () => {
        closeDialog();
      },
    });
  }
};

const deleteGestion = () => {
  if (gestionToDelete.value) {
    deleteForm.delete(`/gestiones/${gestionToDelete.value.id}`, {
      onSuccess: () => {
        deleteDialogVisible.value = false;
        gestionToDelete.value = null;
      },
    });
  }
};
</script>
