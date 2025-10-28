<template>
  <AppLayout title="Gestión de Docentes">
    <div class="flex flex-col h-full space-y-4 lg:space-y-6">
    <!-- Header con botón de crear -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 shrink-0">
      <div>
        <h2 class="text-xl lg:text-2xl font-bold text-gray-800 dark:text-white">Gestión de Docentes</h2>
        <p class="text-sm lg:text-base text-gray-600 dark:text-gray-400 mt-1">Administra los docentes del sistema</p>
      </div>
      <Button
        label="Nuevo Docente"
        icon="pi pi-plus"
        @click="openCreateDialog"
        severity="success"
        class="w-full sm:w-auto"
      />
    </div>

    <!-- Tabla de docentes con scroll -->
    <div class="flex-1 overflow-hidden">
      <Card class="h-full flex flex-col">
        <template #content>
          <div class="flex-1 overflow-auto">
            <DataTable
              :value="docentes"
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
                field="codigo" 
                header="Código" 
                sortable 
                :style="{ minWidth: '8rem' }"
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
              ></Column>
              <Column 
                field="apellidos" 
                header="Apellidos" 
                sortable 
                :style="{ minWidth: '10rem' }"
                headerClass="text-xs sm:text-sm"
                bodyClass="text-xs sm:text-sm"
                class="hidden sm:table-cell"
              ></Column>
              <Column 
                field="ci" 
                header="CI" 
                sortable 
                :style="{ minWidth: '8rem' }"
                headerClass="text-xs sm:text-sm"
                bodyClass="text-xs sm:text-sm"
              ></Column>
              <Column 
                field="usuario.username" 
                header="Usuario" 
                sortable 
                :style="{ minWidth: '10rem' }"
                headerClass="text-xs sm:text-sm"
                bodyClass="text-xs sm:text-sm"
                class="hidden md:table-cell"
              >
                <template #body="slotProps">
                  <Tag 
                    v-if="slotProps.data.usuario"
                    :value="slotProps.data.usuario.username" 
                    severity="info"
                    class="text-xs"
                  />
                  <span v-else class="text-xs text-gray-400">Sin usuario</span>
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

    <!-- Dialog para Crear/Editar Docente -->
    <Dialog
      v-model:visible="dialogVisible"
      :header="isEditing ? 'Editar Docente' : 'Crear Docente'"
      :modal="true"
      class="w-[95vw] sm:w-[85vw] md:w-[600px] lg:w-[500px]"
      :dismissableMask="true"
    >
      <form @submit.prevent="submitForm" class="space-y-3 sm:space-y-4 mt-2 sm:mt-4">
        <div class="flex flex-col gap-2">
          <label for="codigo" class="font-semibold text-sm">Código</label>
          <InputText
            id="codigo"
            v-model="form.codigo"
            :class="{ 'p-invalid': form.errors.codigo }"
            placeholder="Ingresa el código del docente"
            class="w-full text-sm"
            :disabled="isEditing"
          />
          <small v-if="form.errors.codigo" class="text-red-500 text-xs">{{ form.errors.codigo }}</small>
        </div>

        <div class="flex flex-col gap-2">
          <label for="nombre" class="font-semibold text-sm">Nombre</label>
          <InputText
            id="nombre"
            v-model="form.nombre"
            :class="{ 'p-invalid': form.errors.nombre }"
            placeholder="Ingresa el nombre"
            class="w-full text-sm"
          />
          <small v-if="form.errors.nombre" class="text-red-500 text-xs">{{ form.errors.nombre }}</small>
        </div>

        <div class="flex flex-col gap-2">
          <label for="apellidos" class="font-semibold text-sm">Apellidos</label>
          <InputText
            id="apellidos"
            v-model="form.apellidos"
            :class="{ 'p-invalid': form.errors.apellidos }"
            placeholder="Ingresa los apellidos"
            class="w-full text-sm"
          />
          <small v-if="form.errors.apellidos" class="text-red-500 text-xs">{{ form.errors.apellidos }}</small>
        </div>

        <div class="flex flex-col gap-2">
          <label for="ci" class="font-semibold text-sm">CI</label>
          <InputText
            id="ci"
            v-model="form.ci"
            :class="{ 'p-invalid': form.errors.ci }"
            placeholder="Ingresa el CI"
            class="w-full text-sm"
          />
          <small v-if="form.errors.ci" class="text-red-500 text-xs">{{ form.errors.ci }}</small>
        </div>

        <div class="flex flex-col gap-2">
          <label for="id_usuario" class="font-semibold text-sm">Usuario (Opcional)</label>
          <Select
            id="id_usuario"
            v-model="form.id_usuario"
            :options="availableUsuarios"
            optionLabel="username"
            optionValue="id"
            placeholder="Selecciona un usuario"
            :class="{ 'p-invalid': form.errors.id_usuario }"
            class="w-full text-sm"
            showClear
          />
          <small v-if="form.errors.id_usuario" class="text-red-500 text-xs">{{ form.errors.id_usuario }}</small>
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
          ¿Estás seguro de que deseas eliminar al docente <strong>{{ docenteToDelete?.nombre }} {{ docenteToDelete?.apellidos }}</strong>?
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
            @click="deleteDocente"
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
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import type { User } from '@/types';

interface Docente {
  codigo: string;
  id_usuario: number | null;
  nombre: string;
  apellidos: string;
  ci: string;
  usuario?: User;
}

const props = defineProps<{
  docentes: Docente[];
  usuarios: User[];
}>();

const dialogVisible = ref(false);
const deleteDialogVisible = ref(false);
const isEditing = ref(false);
const docenteToDelete = ref<Docente | null>(null);
const currentCodigo = ref<string | null>(null);

const form = useForm({
  codigo: '',
  nombre: '',
  apellidos: '',
  ci: '',
  id_usuario: null as number | null,
});

const deleteForm = useForm({});

const availableUsuarios = computed(() => {
  // Usuarios disponibles: los que no tienen docente asignado + el usuario actual del docente que se está editando
  if (isEditing.value && currentCodigo.value) {
    const currentDocente = props.docentes.find(d => d.codigo === currentCodigo.value);
    const currentUsuarioId = currentDocente?.id_usuario;
    
    return props.usuarios.filter(u => 
      !props.docentes.some(d => d.id_usuario === u.id && d.codigo !== currentCodigo.value) ||
      u.id === currentUsuarioId
    );
  }
  
  return props.usuarios.filter(u => 
    !props.docentes.some(d => d.id_usuario === u.id)
  );
});

const openCreateDialog = () => {
  isEditing.value = false;
  currentCodigo.value = null;
  form.reset();
  form.clearErrors();
  dialogVisible.value = true;
};

const openEditDialog = (docente: Docente) => {
  isEditing.value = true;
  currentCodigo.value = docente.codigo;
  form.codigo = docente.codigo;
  form.nombre = docente.nombre;
  form.apellidos = docente.apellidos;
  form.ci = docente.ci;
  form.id_usuario = docente.id_usuario;
  form.clearErrors();
  dialogVisible.value = true;
};

const openDeleteDialog = (docente: Docente) => {
  docenteToDelete.value = docente;
  deleteDialogVisible.value = true;
};

const closeDialog = () => {
  dialogVisible.value = false;
  form.reset();
  form.clearErrors();
};

const submitForm = () => {
  if (isEditing.value && currentCodigo.value) {
    form.put(`/docentes/${currentCodigo.value}`, {
      onSuccess: () => {
        closeDialog();
      },
    });
  } else {
    form.post('/docentes', {
      onSuccess: () => {
        closeDialog();
      },
    });
  }
};

const deleteDocente = () => {
  if (docenteToDelete.value) {
    deleteForm.delete(`/docentes/${docenteToDelete.value.codigo}`, {
      onSuccess: () => {
        deleteDialogVisible.value = false;
        docenteToDelete.value = null;
      },
    });
  }
};
</script>
