<template>
  <AppLayout title="Gestión de Grupos">
    <div class="flex flex-col h-full space-y-4 lg:space-y-6">
    <!-- Header con botón de crear -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 shrink-0">
      <div>
        <h2 class="text-xl lg:text-2xl font-bold text-gray-800 dark:text-white">Gestión de Grupos</h2>
        <p class="text-sm lg:text-base text-gray-600 dark:text-gray-400 mt-1">Administra los grupos del sistema</p>
      </div>
      <Button
        label="Nuevo Grupo"
        icon="pi pi-plus"
        @click="openCreateDialog"
        severity="success"
        class="w-full sm:w-auto"
      />
    </div>

    <!-- Tabla de grupos con scroll -->
    <div class="flex-1 overflow-hidden">
      <Card class="h-full flex flex-col">
        <template #content>
          <div class="flex-1 overflow-auto">
            <DataTable
              :value="grupos"
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
              ></Column>
              <Column 
                field="nombre" 
                header="Nombre" 
                sortable 
                :style="{ minWidth: '10rem' }"
                headerClass="text-xs sm:text-sm"
                bodyClass="text-xs sm:text-sm"
              >
                <template #body="slotProps">
                  <Tag 
                    :value="slotProps.data.nombre" 
                    severity="info"
                    class="text-xs font-semibold"
                  />
                </template>
              </Column>
              <Column 
                field="materias_count" 
                header="Materias Asignadas" 
                sortable 
                :style="{ minWidth: '10rem' }"
                headerClass="text-xs sm:text-sm"
                bodyClass="text-xs sm:text-sm"
                class="hidden sm:table-cell"
              >
                <template #body="slotProps">
                  <Tag 
                    :value="slotProps.data.materias_count.toString()" 
                    :severity="slotProps.data.materias_count > 0 ? 'success' : 'secondary'"
                    class="text-xs"
                  />
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

    <!-- Dialog para Crear/Editar Grupo -->
    <Dialog
      v-model:visible="dialogVisible"
      :header="isEditing ? 'Editar Grupo' : 'Crear Grupo'"
      :modal="true"
      class="w-[95vw] sm:w-[450px]"
      :dismissableMask="true"
    >
      <form @submit.prevent="submitForm" class="space-y-3 sm:space-y-4 mt-2 sm:mt-4">
        <div class="flex flex-col gap-2">
          <label for="nombre" class="font-semibold text-sm">Nombre del Grupo</label>
          <InputText
            id="nombre"
            v-model="form.nombre"
            :class="{ 'p-invalid': form.errors.nombre }"
            placeholder="Ej: Z1, Z2, SA"
            class="w-full text-sm uppercase"
            maxlength="10"
          />
          <small class="text-gray-500 text-xs">Ejemplo: Z1, Z2, SA, etc.</small>
          <small v-if="form.errors.nombre" class="text-red-500 text-xs">{{ form.errors.nombre }}</small>
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
          ¿Estás seguro de que deseas eliminar el grupo <strong>{{ grupoToDelete?.nombre }}</strong>?
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
            @click="deleteGrupo"
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

interface Grupo {
  id: number;
  nombre: string;
  materias_count: number;
}

const props = defineProps<{
  grupos: Grupo[];
}>();

const dialogVisible = ref(false);
const deleteDialogVisible = ref(false);
const isEditing = ref(false);
const grupoToDelete = ref<Grupo | null>(null);
const currentId = ref<number | null>(null);

const form = useForm({
  nombre: '',
});

const deleteForm = useForm({});

const openCreateDialog = () => {
  isEditing.value = false;
  currentId.value = null;
  form.reset();
  form.clearErrors();
  dialogVisible.value = true;
};

const openEditDialog = (grupo: Grupo) => {
  isEditing.value = true;
  currentId.value = grupo.id;
  form.nombre = grupo.nombre;
  form.clearErrors();
  dialogVisible.value = true;
};

const openDeleteDialog = (grupo: Grupo) => {
  grupoToDelete.value = grupo;
  deleteDialogVisible.value = true;
};

const closeDialog = () => {
  dialogVisible.value = false;
  form.reset();
  form.clearErrors();
};

const submitForm = () => {
  // Convertir nombre a mayúsculas
  form.nombre = form.nombre.toUpperCase();
  
  if (isEditing.value && currentId.value) {
    form.put(`/grupos/${currentId.value}`, {
      onSuccess: () => {
        closeDialog();
      },
    });
  } else {
    form.post('/grupos', {
      onSuccess: () => {
        closeDialog();
      },
    });
  }
};

const deleteGrupo = () => {
  if (grupoToDelete.value) {
    deleteForm.delete(`/grupos/${grupoToDelete.value.id}`, {
      onSuccess: () => {
        deleteDialogVisible.value = false;
        grupoToDelete.value = null;
      },
    });
  }
};
</script>
