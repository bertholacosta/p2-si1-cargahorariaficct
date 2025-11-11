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
                :style="{ minWidth: '10rem' }"
                headerClass="text-xs sm:text-sm"
                bodyClass="text-xs sm:text-sm"
              >
                <template #body="slotProps">
                  <div class="flex gap-1 sm:gap-2">
                    <Button
                      icon="pi pi-book"
                      @click="goToMaterias(slotProps.data.codigo)"
                      severity="info"
                      outlined
                      size="small"
                      class="!p-1 sm:!p-2"
                      v-tooltip.top="'Materias'"
                    />
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

    <!-- Dialog para Importar Docentes -->
    <Dialog
      v-model:visible="importDialogVisible"
      header="Importar Docentes desde Excel/CSV"
      :modal="true"
      class="w-[95vw] sm:w-[600px]"
      :dismissableMask="true"
    >
      <div class="space-y-4">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <h4 class="font-semibold text-sm text-blue-900 mb-2">📋 Instrucciones:</h4>
          <ul class="text-xs text-blue-800 space-y-1">
            <li>1. Descarga la plantilla de Excel haciendo clic en el botón de abajo</li>
            <li>2. Llena la plantilla con los datos de los docentes</li>
            <li>3. Guarda el archivo y súbelo usando el selector de archivos</li>
            <li>4. El sistema creará automáticamente usuarios y docentes</li>
          </ul>
        </div>

        <div class="flex flex-col gap-3">
          <Button
            label="Descargar Plantilla de Excel"
            icon="pi pi-download"
            @click="descargarPlantilla"
            severity="info"
            outlined
            class="w-full"
          />

          <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
            <input
              ref="fileInput"
              type="file"
              accept=".xlsx,.xls,.csv"
              @change="handleFileSelect"
              class="hidden"
            />
            
            <div v-if="!selectedFile" @click="triggerFileInput" class="cursor-pointer">
              <i class="pi pi-cloud-upload text-4xl text-gray-400 mb-3"></i>
              <p class="text-sm text-gray-600 mb-1">Haz clic para seleccionar un archivo</p>
              <p class="text-xs text-gray-500">Excel (.xlsx, .xls) o CSV</p>
            </div>

            <div v-else class="space-y-2">
              <div class="flex items-center justify-center gap-2">
                <i class="pi pi-file-excel text-2xl text-green-600"></i>
                <span class="text-sm font-medium">{{ selectedFile.name }}</span>
              </div>
              <Button
                label="Cambiar archivo"
                icon="pi pi-refresh"
                @click="triggerFileInput"
                text
                size="small"
              />
            </div>
          </div>

          <div v-if="importForm.errors.archivo" class="p-3 bg-red-50 border border-red-200 rounded text-xs">
            <i class="pi pi-exclamation-triangle text-red-600 mr-2"></i>
            <span class="text-red-800">{{ importForm.errors.archivo }}</span>
          </div>
        </div>
      </div>

      <template #footer>
        <div class="flex flex-col-reverse sm:flex-row gap-2">
          <Button
            label="Cancelar"
            @click="closeImportDialog"
            severity="secondary"
            outlined
            class="w-full sm:w-auto text-sm"
          />
          <Button
            label="Importar Docentes"
            icon="pi pi-upload"
            @click="importarDocentes"
            :loading="importForm.processing"
            :disabled="!selectedFile"
            class="w-full sm:w-auto text-sm"
          />
        </div>
      </template>
    </Dialog>

    <!-- Dialog para Resultados de Importación -->
    <Dialog
      v-model:visible="resultadosDialogVisible"
      header="Resultados de la Importación"
      :modal="true"
      class="w-[95vw] sm:w-[800px] max-h-[80vh]"
    >
      <div class="space-y-4">
        <div class="grid grid-cols-3 gap-3">
          <Card class="bg-green-50">
            <template #content>
              <div class="text-center">
                <i class="pi pi-check-circle text-2xl text-green-600 mb-2"></i>
                <p class="text-2xl font-bold text-green-700">{{ resultadosCreados }}</p>
                <p class="text-xs text-green-600">Creados</p>
              </div>
            </template>
          </Card>
          <Card class="bg-yellow-50">
            <template #content>
              <div class="text-center">
                <i class="pi pi-exclamation-triangle text-2xl text-yellow-600 mb-2"></i>
                <p class="text-2xl font-bold text-yellow-700">{{ resultadosOmitidos }}</p>
                <p class="text-xs text-yellow-600">Omitidos</p>
              </div>
            </template>
          </Card>
          <Card class="bg-red-50">
            <template #content>
              <div class="text-center">
                <i class="pi pi-times-circle text-2xl text-red-600 mb-2"></i>
                <p class="text-2xl font-bold text-red-700">{{ resultadosErrores }}</p>
                <p class="text-xs text-red-600">Errores</p>
              </div>
            </template>
          </Card>
        </div>

        <div class="max-h-[400px] overflow-auto">
          <DataTable
            :value="resultadosImportacion"
            stripedRows
            showGridlines
            size="small"
            class="text-xs"
          >
            <Column field="fila" header="Fila" :style="{ width: '60px' }"></Column>
            <Column field="codigo" header="Código" :style="{ width: '100px' }"></Column>
            <Column field="nombre" header="Nombre"></Column>
            <Column field="username" header="Usuario" class="hidden sm:table-cell"></Column>
            <Column field="password_temporal" header="Contraseña" class="hidden md:table-cell">
              <template #body="slotProps">
                <code v-if="slotProps.data.password_temporal" class="text-xs bg-gray-100 px-2 py-1 rounded">
                  {{ slotProps.data.password_temporal }}
                </code>
              </template>
            </Column>
            <Column field="estado" header="Estado" :style="{ width: '100px' }">
              <template #body="slotProps">
                <Tag 
                  :value="slotProps.data.estado" 
                  :severity="getEstadoSeverity(slotProps.data.estado)"
                  class="text-xs"
                />
              </template>
            </Column>
            <Column field="mensaje" header="Mensaje">
              <template #body="slotProps">
                <span class="text-xs">{{ slotProps.data.mensaje }}</span>
              </template>
            </Column>
          </DataTable>
        </div>

        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
          <p class="text-xs text-yellow-800">
            <i class="pi pi-info-circle mr-2"></i>
            <strong>Importante:</strong> Guarda las contraseñas temporales generadas. Los docentes deberán cambiarlas en su primer inicio de sesión.
          </p>
        </div>
      </div>

      <template #footer>
        <Button
          label="Cerrar"
          @click="resultadosDialogVisible = false"
          class="w-full sm:w-auto text-sm"
        />
      </template>
    </Dialog>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
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
const importDialogVisible = ref(false);
const resultadosDialogVisible = ref(false);
const isEditing = ref(false);
const docenteToDelete = ref<Docente | null>(null);
const currentCodigo = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);
const selectedFile = ref<File | null>(null);
const resultadosImportacion = ref<any[]>([]);

const form = useForm({
  codigo: '',
  nombre: '',
  apellidos: '',
  ci: '',
  id_usuario: null as number | null,
});

const deleteForm = useForm({});

const importForm = useForm({
  archivo: null as File | null,
});

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

const goToMaterias = (codigo: string) => {
  router.visit(`/docentes/${codigo}/materias-habilitadas`);
};

// Funciones para importación
const openImportDialog = () => {
  selectedFile.value = null;
  importForm.reset();
  importForm.clearErrors();
  importDialogVisible.value = true;
};

const closeImportDialog = () => {
  importDialogVisible.value = false;
  selectedFile.value = null;
  importForm.reset();
  importForm.clearErrors();
};

const triggerFileInput = () => {
  fileInput.value?.click();
};

const handleFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files.length > 0) {
    selectedFile.value = target.files[0];
    importForm.archivo = target.files[0];
    importForm.clearErrors();
  }
};

const descargarPlantilla = () => {
  window.location.href = '/docentes/plantilla/descargar';
};

const importarDocentes = () => {
  if (!selectedFile.value) {
    importForm.setError('archivo', 'Debes seleccionar un archivo');
    return;
  }

  importForm.archivo = selectedFile.value;

  importForm.post('/docentes/importar', {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      closeImportDialog();
      // Obtener resultados de la importación
      fetch('/docentes/importar/resultados')
        .then(res => res.json())
        .then(data => {
          resultadosImportacion.value = data;
          if (data.length > 0) {
            resultadosDialogVisible.value = true;
          }
        });
    },
  });
};

// Computed para estadísticas de resultados
const resultadosCreados = computed(() => {
  return resultadosImportacion.value.filter(r => r.estado === 'creado').length;
});

const resultadosOmitidos = computed(() => {
  return resultadosImportacion.value.filter(r => r.estado === 'omitido').length;
});

const resultadosErrores = computed(() => {
  return resultadosImportacion.value.filter(r => r.estado === 'error').length;
});

const getEstadoSeverity = (estado: string) => {
  switch (estado) {
    case 'creado':
      return 'success';
    case 'omitido':
      return 'warn';
    case 'error':
      return 'danger';
    default:
      return 'info';
  }
};
</script>
