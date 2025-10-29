<template>
  <AppLayout title="Gestión de Materias">
    <div class="flex flex-col h-full space-y-4 lg:space-y-6">
    <!-- Header con botón de crear -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 shrink-0">
      <div>
        <h2 class="text-xl lg:text-2xl font-bold text-gray-800 dark:text-white">Gestión de Materias</h2>
        <p class="text-sm lg:text-base text-gray-600 dark:text-gray-400 mt-1">Administra las materias del sistema</p>
      </div>
      <Button
        label="Nueva Materia"
        icon="pi pi-plus"
        @click="openCreateDialog"
        severity="success"
        class="w-full sm:w-auto"
      />
    </div>

    <!-- Tabla de materias con scroll -->
    <div class="flex-1 overflow-hidden">
      <Card class="h-full flex flex-col">
        <template #content>
          <div class="flex-1 overflow-auto">
            <DataTable
              :value="materias"
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
                field="sigla" 
                header="Sigla" 
                sortable 
                :style="{ minWidth: '8rem' }"
                headerClass="text-xs sm:text-sm"
                bodyClass="text-xs sm:text-sm"
              >
                <template #body="slotProps">
                  <Tag 
                    :value="slotProps.data.sigla" 
                    severity="info"
                    class="text-xs font-mono"
                  />
                </template>
              </Column>
              <Column 
                field="nombre" 
                header="Nombre" 
                sortable 
                :style="{ minWidth: '15rem' }"
                headerClass="text-xs sm:text-sm"
                bodyClass="text-xs sm:text-sm"
              ></Column>
              <Column 
                field="carga_horaria" 
                header="Carga Horaria" 
                sortable 
                :style="{ minWidth: '8rem' }"
                headerClass="text-xs sm:text-sm"
                bodyClass="text-xs sm:text-sm"
                class="hidden sm:table-cell"
              >
                <template #body="slotProps">
                  <span class="text-xs sm:text-sm">
                    {{ slotProps.data.carga_horaria ? `${slotProps.data.carga_horaria} hrs` : '-' }}
                  </span>
                </template>
              </Column>
              <Column 
                field="creditos" 
                header="Créditos" 
                sortable 
                :style="{ minWidth: '6rem' }"
                headerClass="text-xs sm:text-sm"
                bodyClass="text-xs sm:text-sm"
                class="hidden md:table-cell"
              >
                <template #body="slotProps">
                  <Tag 
                    v-if="slotProps.data.creditos"
                    :value="slotProps.data.creditos.toString()" 
                    severity="success"
                    class="text-xs"
                  />
                  <span v-else class="text-xs text-gray-400">-</span>
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
                    />
                    <Button
                      icon="pi pi-trash"
                      @click="openDeleteDialog(slotProps.data)"
                      severity="danger"
                      outlined
                      size="small"
                      class="!p-1 sm:!p-2"
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

    <!-- Dialog para Crear/Editar Materia -->
    <Dialog
      v-model:visible="dialogVisible"
      :header="isEditing ? 'Editar Materia' : 'Crear Materia'"
      :modal="true"
      class="w-[95vw] sm:w-[85vw] md:w-[600px] lg:w-[500px]"
      :dismissableMask="true"
    >
      <form @submit.prevent="submitForm" class="space-y-3 sm:space-y-4 mt-2 sm:mt-4">
        <div class="flex flex-col gap-2">
          <label for="sigla" class="font-semibold text-sm">Sigla</label>
          <InputText
            id="sigla"
            v-model="form.sigla"
            :class="{ 'p-invalid': form.errors.sigla }"
            placeholder="Ej: MAT101"
            class="w-full text-sm uppercase"
            maxlength="10"
          />
          <small v-if="form.errors.sigla" class="text-red-500 text-xs">{{ form.errors.sigla }}</small>
        </div>

        <div class="flex flex-col gap-2">
          <label for="nombre" class="font-semibold text-sm">Nombre</label>
          <InputText
            id="nombre"
            v-model="form.nombre"
            :class="{ 'p-invalid': form.errors.nombre }"
            placeholder="Ej: Cálculo I"
            class="w-full text-sm"
          />
          <small v-if="form.errors.nombre" class="text-red-500 text-xs">{{ form.errors.nombre }}</small>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
          <div class="flex flex-col gap-2">
            <label for="carga_horaria" class="font-semibold text-sm">Carga Horaria</label>
            <InputNumber
              id="carga_horaria"
              v-model="form.carga_horaria"
              :class="{ 'p-invalid': form.errors.carga_horaria }"
              placeholder="Horas"
              class="w-full text-sm"
              :min="0"
              suffix=" hrs"
              :useGrouping="false"
            />
            <small v-if="form.errors.carga_horaria" class="text-red-500 text-xs">{{ form.errors.carga_horaria }}</small>
          </div>

          <div class="flex flex-col gap-2">
            <label for="creditos" class="font-semibold text-sm">Créditos</label>
            <InputNumber
              id="creditos"
              v-model="form.creditos"
              :class="{ 'p-invalid': form.errors.creditos }"
              placeholder="Créditos"
              class="w-full text-sm"
              :min="0"
              :useGrouping="false"
            />
            <small v-if="form.errors.creditos" class="text-red-500 text-xs">{{ form.errors.creditos }}</small>
          </div>
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
          ¿Estás seguro de que deseas eliminar la materia <strong>{{ materiaToDelete?.sigla }} - {{ materiaToDelete?.nombre }}</strong>?
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
            @click="deleteMateria"
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
import InputNumber from 'primevue/inputnumber';
import Tag from 'primevue/tag';

interface Materia {
  id: number;
  sigla: string;
  nombre: string;
  carga_horaria: number | null;
  creditos: number | null;
}

const props = defineProps<{
  materias: Materia[];
}>();

const dialogVisible = ref(false);
const deleteDialogVisible = ref(false);
const isEditing = ref(false);
const materiaToDelete = ref<Materia | null>(null);
const currentId = ref<number | null>(null);

const form = useForm({
  sigla: '',
  nombre: '',
  carga_horaria: null as number | null,
  creditos: null as number | null,
});

const deleteForm = useForm({});

const openCreateDialog = () => {
  isEditing.value = false;
  currentId.value = null;
  form.reset();
  form.clearErrors();
  dialogVisible.value = true;
};

const openEditDialog = (materia: Materia) => {
  isEditing.value = true;
  currentId.value = materia.id;
  form.sigla = materia.sigla;
  form.nombre = materia.nombre;
  form.carga_horaria = materia.carga_horaria;
  form.creditos = materia.creditos;
  form.clearErrors();
  dialogVisible.value = true;
};

const openDeleteDialog = (materia: Materia) => {
  materiaToDelete.value = materia;
  deleteDialogVisible.value = true;
};

const closeDialog = () => {
  dialogVisible.value = false;
  form.reset();
  form.clearErrors();
};

const submitForm = () => {
  // Convertir sigla a mayúsculas
  form.sigla = form.sigla.toUpperCase();
  
  if (isEditing.value && currentId.value) {
    form.put(`/materias/${currentId.value}`, {
      onSuccess: () => {
        closeDialog();
      },
    });
  } else {
    form.post('/materias', {
      onSuccess: () => {
        closeDialog();
      },
    });
  }
};

const deleteMateria = () => {
  if (materiaToDelete.value) {
    deleteForm.delete(`/materias/${materiaToDelete.value.id}`, {
      onSuccess: () => {
        deleteDialogVisible.value = false;
        materiaToDelete.value = null;
      },
    });
  }
};
</script>
