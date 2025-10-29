<template>
  <AppLayout title="Gestión de Aulas">
    <div class="flex flex-col h-full space-y-4 lg:space-y-6">
    <!-- Header con botón de crear -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 shrink-0">
      <div>
        <h2 class="text-xl lg:text-2xl font-bold text-gray-800 dark:text-white">Gestión de Aulas</h2>
        <p class="text-sm lg:text-base text-gray-600 dark:text-gray-400 mt-1">Administra las aulas por módulo</p>
      </div>
      <Button
        label="Nueva Aula"
        icon="pi pi-plus"
        @click="openCreateDialog"
        severity="success"
        class="w-full sm:w-auto"
      />
    </div>

    <!-- Tabla de aulas con scroll -->
    <div class="flex-1 overflow-hidden">
      <Card class="h-full flex flex-col">
        <template #content>
          <div class="flex-1 overflow-auto">
            <DataTable
              :value="aulas"
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
                field="numero_modulo" 
                header="Módulo" 
                sortable 
                :style="{ minWidth: '8rem' }"
                headerClass="text-xs sm:text-sm"
                bodyClass="text-xs sm:text-sm"
              >
                <template #body="slotProps">
                  <Tag 
                    :value="'M' + slotProps.data.numero_modulo" 
                    severity="info"
                    class="text-xs font-bold"
                  />
                </template>
              </Column>
              <Column 
                field="numero" 
                header="Número Aula" 
                sortable 
                :style="{ minWidth: '10rem' }"
                headerClass="text-xs sm:text-sm"
                bodyClass="text-xs sm:text-sm"
              >
                <template #body="slotProps">
                  <Tag 
                    :value="'Aula ' + slotProps.data.numero" 
                    severity="success"
                    class="text-xs font-semibold"
                  />
                </template>
              </Column>
              <Column 
                header="Nombre Completo" 
                :style="{ minWidth: '15rem' }"
                headerClass="text-xs sm:text-sm"
                bodyClass="text-xs sm:text-sm"
                class="hidden md:table-cell"
              >
                <template #body="slotProps">
                  <span class="text-xs sm:text-sm font-medium">
                    Módulo {{ slotProps.data.numero_modulo }} - Aula {{ slotProps.data.numero }}
                  </span>
                </template>
              </Column>
              <Column 
                field="modulo.facultad" 
                header="Facultad" 
                sortable 
                :style="{ minWidth: '12rem' }"
                headerClass="text-xs sm:text-sm"
                bodyClass="text-xs sm:text-sm"
                class="hidden lg:table-cell"
              >
                <template #body="slotProps">
                  <span class="text-xs sm:text-sm">
                    {{ slotProps.data.modulo?.facultad || 'No especificada' }}
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

    <!-- Dialog para Crear/Editar Aula -->
    <Dialog
      v-model:visible="dialogVisible"
      :header="isEditing ? 'Editar Aula' : 'Crear Aula'"
      :modal="true"
      class="w-[95vw] sm:w-[500px]"
      :dismissableMask="true"
    >
      <form @submit.prevent="submitForm" class="space-y-3 sm:space-y-4 mt-2 sm:mt-4">
        <div class="flex flex-col gap-2">
          <label for="numero_modulo" class="font-semibold text-sm">Módulo</label>
          <Select
            id="numero_modulo"
            v-model="form.numero_modulo"
            :options="modulos"
            optionLabel="label"
            optionValue="numero"
            placeholder="Selecciona un módulo"
            :class="{ 'p-invalid': form.errors.numero_modulo }"
            class="w-full text-sm"
          >
            <template #value="slotProps">
              <span v-if="slotProps.value">Módulo {{ slotProps.value }}</span>
              <span v-else>{{ slotProps.placeholder }}</span>
            </template>
            <template #option="slotProps">
              <div class="flex items-center gap-2">
                <Tag :value="'M' + slotProps.option.numero" severity="info" class="text-xs" />
                <span class="text-sm">{{ slotProps.option.facultad || 'Sin nombre' }}</span>
              </div>
            </template>
          </Select>
          <small v-if="form.errors.numero_modulo" class="text-red-500 text-xs">{{ form.errors.numero_modulo }}</small>
        </div>

        <div class="flex flex-col gap-2">
          <label for="numero" class="font-semibold text-sm">Número del Aula</label>
          <InputNumber
            id="numero"
            v-model="form.numero"
            :class="{ 'p-invalid': form.errors.numero }"
            placeholder="Ej: 101, 201, 301..."
            class="w-full text-sm"
            :useGrouping="false"
          />
          <small class="text-gray-500 text-xs">El aula debe ser única dentro del módulo</small>
          <small v-if="form.errors.numero" class="text-red-500 text-xs">{{ form.errors.numero }}</small>
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
          ¿Estás seguro de que deseas eliminar el aula 
          <strong>Módulo {{ aulaToDelete?.numero_modulo }} - Aula {{ aulaToDelete?.numero }}</strong>?
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
            @click="deleteAula"
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
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import InputNumber from 'primevue/inputnumber';
import Select from 'primevue/select';
import Tag from 'primevue/tag';

interface Modulo {
  numero: number;
  facultad: string | null;
  label?: string;
}

interface Aula {
  id: number;
  numero: number;
  numero_modulo: number;
  modulo?: Modulo;
}

const props = defineProps<{
  aulas: Aula[];
  modulos: Modulo[];
}>();

const dialogVisible = ref(false);
const deleteDialogVisible = ref(false);
const isEditing = ref(false);
const aulaToDelete = ref<Aula | null>(null);
const currentId = ref<number | null>(null);

// Agregar label a los módulos para el select
const modulosWithLabel = computed(() => {
  return props.modulos.map(m => ({
    ...m,
    label: `Módulo ${m.numero}${m.facultad ? ' - ' + m.facultad : ''}`
  }));
});

const form = useForm({
  numero: null as number | null,
  numero_modulo: null as number | null,
});

const deleteForm = useForm({});

const openCreateDialog = () => {
  isEditing.value = false;
  currentId.value = null;
  form.reset();
  form.clearErrors();
  dialogVisible.value = true;
};

const openEditDialog = (aula: Aula) => {
  isEditing.value = true;
  currentId.value = aula.id;
  form.numero = aula.numero;
  form.numero_modulo = aula.numero_modulo;
  form.clearErrors();
  dialogVisible.value = true;
};

const openDeleteDialog = (aula: Aula) => {
  aulaToDelete.value = aula;
  deleteDialogVisible.value = true;
};

const closeDialog = () => {
  dialogVisible.value = false;
  form.reset();
  form.clearErrors();
};

const submitForm = () => {
  if (isEditing.value && currentId.value) {
    form.put(`/aulas/${currentId.value}`, {
      onSuccess: () => {
        closeDialog();
      },
    });
  } else {
    form.post('/aulas', {
      onSuccess: () => {
        closeDialog();
      },
    });
  }
};

const deleteAula = () => {
  if (aulaToDelete.value) {
    deleteForm.delete(`/aulas/${aulaToDelete.value.id}`, {
      onSuccess: () => {
        deleteDialogVisible.value = false;
        aulaToDelete.value = null;
      },
    });
  }
};
</script>
