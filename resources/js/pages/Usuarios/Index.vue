<template>
  <AppLayout title="Gestión de Usuarios">
    <div class="flex flex-col h-full space-y-4 lg:space-y-6">
    <!-- Header con botón de crear -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 shrink-0">
      <div>
        <h2 class="text-xl lg:text-2xl font-bold text-gray-800 dark:text-white">Gestión de Usuarios</h2>
        <p class="text-sm lg:text-base text-gray-600 dark:text-gray-400 mt-1">Administra los usuarios del sistema</p>
      </div>
      <div v-if="puedeCrear('usuarios')" class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
        <Button
          label="Importar Docentes"
          icon="pi pi-upload"
          @click="openImportDialog"
          severity="info"
          outlined
          class="w-full sm:w-auto text-sm"
        />
        <Button
          label="Nuevo Usuario"
          icon="pi pi-plus"
          @click="openCreateDialog"
          severity="success"
          class="w-full sm:w-auto text-sm"
        />
      </div>
    </div>

    <!-- Tabla de usuarios con scroll -->
    <div class="flex-1 overflow-hidden">
      <Card class="h-full flex flex-col">
        <template #content>
          <div class="flex-1 overflow-auto">
            <DataTable
            :value="usuarios"
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
              :style="{ minWidth: '3rem' }"
              headerClass="text-xs sm:text-sm"
              bodyClass="text-xs sm:text-sm"
            ></Column>
            <Column 
              field="username" 
              header="Usuario" 
              sortable 
              :style="{ minWidth: '8rem' }"
              headerClass="text-xs sm:text-sm"
              bodyClass="text-xs sm:text-sm"
            ></Column>
            <Column 
              field="email" 
              header="Email" 
              sortable 
              :style="{ minWidth: '12rem' }"
              headerClass="text-xs sm:text-sm"
              bodyClass="text-xs sm:text-sm"
              class="hidden sm:table-cell"
            ></Column>
            <Column 
              field="rol.nombre" 
              header="Rol" 
              sortable 
              :style="{ minWidth: '8rem' }"
              headerClass="text-xs sm:text-sm"
              bodyClass="text-xs sm:text-sm"
            >
              <template #body="slotProps">
                <Tag 
                  :value="slotProps.data.rol?.nombre" 
                  :severity="getRolSeverity(slotProps.data.rol?.nombre)" 
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
                    v-if="puedeEditar('usuarios')"
                    icon="pi pi-pencil"
                    @click="openEditDialog(slotProps.data)"
                    severity="info"
                    outlined
                    size="small"
                    class="!p-1 sm:!p-2"
                  />
                  <Button
                    v-if="puedeEliminar('usuarios')"
                    icon="pi pi-trash"
                    @click="confirmDelete(slotProps.data)"
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

    <!-- Dialog para Crear/Editar Usuario -->
    <Dialog
      v-model:visible="dialogVisible"
      :header="isEditing ? 'Editar Usuario' : 'Crear Usuario'"
      :modal="true"
      class="w-[95vw] sm:w-[85vw] md:w-[600px] lg:w-[500px]"
      :dismissableMask="true"
    >
      <form @submit.prevent="submitForm" class="space-y-3 sm:space-y-4 mt-2 sm:mt-4">
        <div class="flex flex-col gap-2">
          <label for="username" class="font-semibold text-sm">Username</label>
          <InputText
            id="username"
            v-model="form.username"
            :class="{ 'p-invalid': form.errors.username }"
            placeholder="Ingresa el username"
            class="w-full text-sm"
          />
          <small v-if="form.errors.username" class="text-red-500 text-xs">{{ form.errors.username }}</small>
        </div>

        <div class="flex flex-col gap-2">
          <label for="email" class="font-semibold text-sm">Email</label>
          <InputText
            id="email"
            v-model="form.email"
            type="email"
            :class="{ 'p-invalid': form.errors.email }"
            placeholder="usuario@ejemplo.com"
            class="w-full text-sm"
          />
          <small v-if="form.errors.email" class="text-red-500 text-xs">{{ form.errors.email }}</small>
        </div>

        <div class="flex flex-col gap-2">
          <label for="password" class="font-semibold text-sm">
            Contraseña {{ isEditing ? '(vacío = no cambiar)' : '' }}
          </label>
          <Password
            id="password"
            v-model="form.password"
            :class="{ 'p-invalid': form.errors.password }"
            :feedback="false"
            toggleMask
            placeholder="Ingresa la contraseña"
            class="w-full"
            inputClass="w-full text-sm"
          />
          <small v-if="form.errors.password" class="text-red-500 text-xs">{{ form.errors.password }}</small>
        </div>

        <div class="flex flex-col gap-2">
          <label for="rol" class="font-semibold text-sm">Rol</label>
          <Select
            id="rol"
            v-model="form.id_rol"
            :options="roles"
            optionLabel="nombre"
            optionValue="id"
            placeholder="Selecciona un rol"
            :class="{ 'p-invalid': form.errors.id_rol }"
            class="w-full text-sm"
          />
          <small v-if="form.errors.id_rol" class="text-red-500 text-xs">{{ form.errors.id_rol }}</small>
        </div>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 mt-4 sm:mt-6">
          <Button
            label="Cancelar"
            @click="dialogVisible = false"
            severity="secondary"
            outlined
            type="button"
            class="w-full sm:w-auto text-sm"
          />
          <Button
            :label="isEditing ? 'Actualizar' : 'Crear'"
            type="submit"
            :loading="form.processing"
            severity="success"
            class="w-full sm:w-auto text-sm"
          />
        </div>
      </form>
    </Dialog>

    <!-- Dialog de Confirmación para Eliminar -->
    <Dialog
      v-model:visible="deleteDialogVisible"
      header="Confirmar Eliminación"
      :modal="true"
      class="w-[95vw] sm:w-[85vw] md:w-[500px] lg:w-[400px]"
      :dismissableMask="true"
    >
      <div class="flex items-start sm:items-center gap-3 p-2">
        <i class="pi pi-exclamation-triangle text-red-500 text-2xl sm:text-3xl flex-shrink-0"></i>
        <span class="text-sm sm:text-base">
          ¿Estás seguro de que deseas eliminar al usuario <strong>{{ usuarioToDelete?.username }}</strong>?
        </span>
      </div>
      <template #footer>
        <div class="flex flex-col-reverse sm:flex-row gap-2 w-full sm:w-auto">
          <Button
            label="Cancelar"
            @click="deleteDialogVisible = false"
            severity="secondary"
            outlined
            class="w-full sm:w-auto text-sm"
          />
          <Button
            label="Eliminar"
            @click="deleteUsuario"
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
            <li>5. Contraseña generada: Primera letra del nombre + CI (Ej: Juan con CI 5485555 → J5485555)</li>
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
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import type { User, Rol } from '@/types';

const props = defineProps<{
  usuarios: User[];
  roles: Rol[];
}>();

// Composable de permisos
const { puedeCrear, puedeEditar, puedeEliminar } = usePermissions();

const dialogVisible = ref(false);
const deleteDialogVisible = ref(false);
const isEditing = ref(false);
const usuarioToDelete = ref<User | null>(null);

const form = useForm({
  username: '',
  email: '',
  password: '',
  id_rol: null as number | null,
});

const deleteForm = useForm({});

const getRolSeverity = (rol: string | undefined) => {
  switch (rol) {
    case 'Administrador':
      return 'danger';
    case 'Docente':
      return 'info';
    case 'Estudiante':
      return 'success';
    default:
      return 'secondary';
  }
};

const openCreateDialog = () => {
  isEditing.value = false;
  form.reset();
  form.clearErrors();
  dialogVisible.value = true;
};

const openEditDialog = (usuario: User) => {
  isEditing.value = true;
  form.reset();
  form.clearErrors();
  form.username = usuario.username;
  form.email = usuario.email;
  form.password = '';
  form.id_rol = usuario.id_rol || null;
  form.transform((data) => ({
    ...data,
    _method: 'PUT',
  }));
  dialogVisible.value = true;
  usuarioToDelete.value = usuario;
};

const submitForm = () => {
  if (isEditing.value && usuarioToDelete.value) {
    form.post(`/usuarios/${usuarioToDelete.value.id}`, {
      onSuccess: () => {
        dialogVisible.value = false;
        form.reset();
      },
    });
  } else {
    form.post('/usuarios', {
      onSuccess: () => {
        dialogVisible.value = false;
        form.reset();
      },
    });
  }
};

const confirmDelete = (usuario: User) => {
  usuarioToDelete.value = usuario;
  deleteDialogVisible.value = true;
};

const deleteUsuario = () => {
  if (usuarioToDelete.value) {
    deleteForm.delete(`/usuarios/${usuarioToDelete.value.id}`, {
      onSuccess: () => {
        deleteDialogVisible.value = false;
        usuarioToDelete.value = null;
      },
    });
  }
};

// Referencias para importación de docentes
const importDialogVisible = ref(false);
const resultadosDialogVisible = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);
const selectedFile = ref<File | null>(null);
const resultadosImportacion = ref<any[]>([]);

const importForm = useForm({
  archivo: null as File | null,
});

// Computed properties para resultados
const resultadosCreados = computed(() => 
  resultadosImportacion.value.filter(r => r.estado === 'Creado').length
);

const resultadosOmitidos = computed(() => 
  resultadosImportacion.value.filter(r => r.estado === 'Omitido').length
);

const resultadosErrores = computed(() => 
  resultadosImportacion.value.filter(r => r.estado === 'Error').length
);

// Métodos para importación
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
};

const triggerFileInput = () => {
  fileInput.value?.click();
};

const handleFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files.length > 0) {
    selectedFile.value = target.files[0];
    importForm.archivo = target.files[0];
  }
};

const descargarPlantilla = () => {
  window.location.href = '/usuarios/plantilla/descargar';
};

const importarDocentes = () => {
  if (!selectedFile.value) return;

  importForm
    .transform((data) => {
      const formData = new FormData();
      if (data.archivo) {
        formData.append('archivo', data.archivo);
      }
      return formData;
    })
    .post('/usuarios/importar', {
      forceFormData: true,
      onSuccess: () => {
        importDialogVisible.value = false;
        
        // Obtener resultados de la sesión
        router.get('/usuarios/importar/resultados', {}, {
          preserveState: true,
          preserveScroll: true,
          only: [],
          onSuccess: (page: any) => {
            if (page.props && page.props.resultados) {
              resultadosImportacion.value = page.props.resultados;
              resultadosDialogVisible.value = true;
            }
          }
        });
      },
      onError: () => {
        // Los errores se muestran automáticamente por Inertia
      }
    });
};

const getEstadoSeverity = (estado: string) => {
  switch (estado) {
    case 'Creado':
      return 'success';
    case 'Omitido':
      return 'warn';
    case 'Error':
      return 'danger';
    default:
      return 'secondary';
  }
};
</script>
