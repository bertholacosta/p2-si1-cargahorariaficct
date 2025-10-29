<template>
  <AppLayout title="Gestión de Módulos">
    <div class="flex flex-col h-full space-y-4 lg:space-y-6">
    <!-- Header con botón de crear -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 shrink-0">
      <div>
        <h2 class="text-xl lg:text-2xl font-bold text-gray-800 dark:text-white">Gestión de Módulos</h2>
        <p class="text-sm lg:text-base text-gray-600 dark:text-gray-400 mt-1">Administra los módulos de la facultad</p>
      </div>
      <Button
        label="Nuevo Módulo"
        icon="pi pi-plus"
        @click="openCreateDialog"
        severity="success"
        class="w-full sm:w-auto"
      />
    </div>

    <!-- Tabla de módulos con scroll -->
    <div class="flex-1 overflow-hidden">
      <Card class="h-full flex flex-col">
        <template #content>
          <div class="flex-1 overflow-auto">
            <DataTable
              :value="modulos"
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
                field="numero" 
                header="Número" 
                sortable 
                :style="{ minWidth: '8rem' }"
                headerClass="text-xs sm:text-sm"
                bodyClass="text-xs sm:text-sm"
              >
                <template #body="slotProps">
                  <Tag 
                    :value="'M' + slotProps.data.numero" 
                    severity="info"
                    class="text-xs font-bold"
                  />
                </template>
              </Column>
              <Column 
                field="facultad" 
                header="Facultad" 
                sortable 
                :style="{ minWidth: '15rem' }"
                headerClass="text-xs sm:text-sm"
                bodyClass="text-xs sm:text-sm"
              >
                <template #body="slotProps">
                  <span class="text-xs sm:text-sm">
                    {{ slotProps.data.facultad || 'No especificada' }}
                  </span>
                </template>
              </Column>
              <Column 
                field="aulas_count" 
                header="Aulas" 
                sortable 
                :style="{ minWidth: '8rem' }"
                headerClass="text-xs sm:text-sm"
                bodyClass="text-xs sm:text-sm"
                class="hidden sm:table-cell"
              >
                <template #body="slotProps">
                  <Tag 
                    :value="slotProps.data.aulas_count.toString()" 
                    :severity="slotProps.data.aulas_count > 0 ? 'success' : 'secondary'"
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

    <!-- Dialog para Crear/Editar Módulo -->
    <Dialog
      v-model:visible="dialogVisible"
      :header="isEditing ? 'Editar Módulo' : 'Crear Módulo'"
      :modal="true"
      class="w-[95vw] sm:w-[500px]"
      :dismissableMask="true"
    >
      <form @submit.prevent="submitForm" class="space-y-3 sm:space-y-4 mt-2 sm:mt-4">
        <div class="flex flex-col gap-2">
          <label for="numero" class="font-semibold text-sm">Número del Módulo</label>
          <InputNumber
            id="numero"
            v-model="form.numero"
            :class="{ 'p-invalid': form.errors.numero }"
            placeholder="Ej: 1, 2, 3..."
            class="w-full text-sm"
            :useGrouping="false"
            :disabled="isEditing"
          />
          <small v-if="form.errors.numero" class="text-red-500 text-xs">{{ form.errors.numero }}</small>
        </div>

        <div class="flex flex-col gap-2">
          <label for="facultad" class="font-semibold text-sm">Facultad (Opcional)</label>
          <InputText
            id="facultad"
            v-model="form.facultad"
            :class="{ 'p-invalid': form.errors.facultad }"
            placeholder="Ej: FICCT"
            class="w-full text-sm"
          />
          <small v-if="form.errors.facultad" class="text-red-500 text-xs">{{ form.errors.facultad }}</small>
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
          ¿Estás seguro de que deseas eliminar el módulo <strong>{{ moduloToDelete?.numero }}</strong>?
          <span v-if="moduloToDelete?.aulas_count > 0" class="block mt-2 text-red-600">
            Este módulo tiene {{ moduloToDelete.aulas_count }} aula(s) asignada(s) que también se eliminarán.
          </span>
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
            @click="deleteModulo"
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

interface Modulo {
  numero: number;
  facultad: string | null;
  aulas_count: number;
}

const props = defineProps<{
  modulos: Modulo[];
}>();

const dialogVisible = ref(false);
const deleteDialogVisible = ref(false);
const isEditing = ref(false);
const moduloToDelete = ref<Modulo | null>(null);
const currentNumero = ref<number | null>(null);

const form = useForm({
  numero: null as number | null,
  facultad: '',
});

const deleteForm = useForm({});

const openCreateDialog = () => {
  isEditing.value = false;
  currentNumero.value = null;
  form.reset();
  form.clearErrors();
  dialogVisible.value = true;
};

const openEditDialog = (modulo: Modulo) => {
  isEditing.value = true;
  currentNumero.value = modulo.numero;
  form.numero = modulo.numero;
  form.facultad = modulo.facultad || '';
  form.clearErrors();
  dialogVisible.value = true;
};

const openDeleteDialog = (modulo: Modulo) => {
  moduloToDelete.value = modulo;
  deleteDialogVisible.value = true;
};

const closeDialog = () => {
  dialogVisible.value = false;
  form.reset();
  form.clearErrors();
};

const submitForm = () => {
  if (isEditing.value && currentNumero.value) {
    form.put(`/modulos/${currentNumero.value}`, {
      onSuccess: () => {
        closeDialog();
      },
    });
  } else {
    form.post('/modulos', {
      onSuccess: () => {
        closeDialog();
      },
    });
  }
};

const deleteModulo = () => {
  if (moduloToDelete.value) {
    deleteForm.delete(`/modulos/${moduloToDelete.value.numero}`, {
      onSuccess: () => {
        deleteDialogVisible.value = false;
        moduloToDelete.value = null;
      },
    });
  }
};
</script>
