<template>
  <AppLayout title="Gestión de Roles">
    <div class="space-y-4">
      <!-- Header con botón crear -->
      <Card>
        <template #title>
          <div class="flex justify-between items-center">
            <span>Roles del Sistema</span>
            <Button
              label="Nuevo Rol"
              icon="pi pi-plus"
              @click="openCreateDialog"
              severity="success"
              size="small"
            />
          </div>
        </template>
      </Card>

      <!-- Tabla de roles -->
      <Card>
        <template #content>
          <DataTable
            :value="roles"
            stripedRows
            size="small"
          >
            <Column field="nombre" header="Nombre" />
            <Column field="descripcion" header="Descripción" />
            <Column field="permisos_count" header="Permisos" style="width: 120px" />
            <Column header="Acciones" style="width: 200px">
              <template #body="{ data }">
                <div class="flex gap-2">
                  <Button
                    icon="pi pi-pencil"
                    @click="openEditDialog(data)"
                    severity="info"
                    size="small"
                    text
                    v-tooltip.top="'Editar'"
                  />
                  <Button
                    icon="pi pi-lock"
                    @click="openPermisosDialog(data)"
                    severity="warning"
                    size="small"
                    text
                    v-tooltip.top="'Gestionar Permisos'"
                  />
                  <Button
                    icon="pi pi-trash"
                    @click="confirmDelete(data)"
                    severity="danger"
                    size="small"
                    text
                    v-tooltip.top="'Eliminar'"
                  />
                </div>
              </template>
            </Column>
          </DataTable>
        </template>
      </Card>
    </div>

    <!-- Dialog Crear/Editar -->
    <Dialog
      v-model:visible="dialogVisible"
      :header="editMode ? 'Editar Rol' : 'Nuevo Rol'"
      :modal="true"
      class="w-[95vw] sm:w-[500px]"
    >
      <div class="space-y-4">
        <div class="flex flex-col gap-2">
          <label class="font-semibold text-sm">Nombre*</label>
          <input
            v-model="form.nombre"
            type="text"
            class="p-2 border rounded text-sm"
            placeholder="Ej: Administrador, Docente, etc."
          />
          <small v-if="form.errors.nombre" class="text-red-600">{{ form.errors.nombre }}</small>
        </div>

        <div class="flex flex-col gap-2">
          <label class="font-semibold text-sm">Descripción</label>
          <textarea
            v-model="form.descripcion"
            rows="3"
            class="p-2 border rounded text-sm"
            placeholder="Descripción del rol..."
          ></textarea>
        </div>
      </div>

      <template #footer>
        <div class="flex gap-2 justify-end">
          <Button
            label="Cancelar"
            @click="closeDialog"
            severity="secondary"
            outlined
            size="small"
          />
          <Button
            :label="editMode ? 'Actualizar' : 'Crear'"
            @click="submitForm"
            :loading="form.processing"
            size="small"
          />
        </div>
      </template>
    </Dialog>

    <!-- Dialog Permisos -->
    <Dialog
      v-model:visible="permisosDialogVisible"
      header="Gestionar Permisos"
      :modal="true"
      class="w-[95vw] sm:w-[700px]"
    >
      <div class="space-y-4">
        <p class="text-sm text-gray-600">
          Selecciona los permisos para el rol: <strong>{{ currentRol?.nombre }}</strong>
        </p>

        <div v-for="(perms, modulo) in permisos" :key="modulo" class="border rounded p-3">
          <h3 class="font-bold text-sm mb-3 text-blue-700">{{ modulo }}</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            <div v-for="permiso in perms" :key="permiso.id" class="flex items-center gap-2">
              <input
                type="checkbox"
                :id="`permiso-${permiso.id}`"
                v-model="permisosSeleccionados"
                :value="permiso.id"
                class="w-4 h-4"
              />
              <label :for="`permiso-${permiso.id}`" class="text-sm cursor-pointer">
                {{ permiso.nombre }}
              </label>
            </div>
          </div>
        </div>
      </div>

      <template #footer>
        <div class="flex gap-2 justify-end">
          <Button
            label="Cancelar"
            @click="permisosDialogVisible = false"
            severity="secondary"
            outlined
            size="small"
          />
          <Button
            label="Guardar Permisos"
            @click="guardarPermisos"
            :loading="permisosForm.processing"
            size="small"
          />
        </div>
      </template>
    </Dialog>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

interface Rol {
  id: number;
  nombre: string;
  descripcion: string | null;
  permisos_count: number;
}

interface Permiso {
  id: number;
  nombre: string;
  slug: string;
  modulo: string;
  descripcion: string | null;
}

const props = defineProps<{
  roles: Rol[];
  permisos: Record<string, Permiso[]>;
}>();

const dialogVisible = ref(false);
const permisosDialogVisible = ref(false);
const editMode = ref(false);
const currentRol = ref<Rol | null>(null);
const permisosSeleccionados = ref<number[]>([]);

const form = useForm({
  nombre: '',
  descripcion: '',
});

const permisosForm = useForm({
  permisos: [] as number[],
});

const openCreateDialog = () => {
  editMode.value = false;
  form.reset();
  form.clearErrors();
  dialogVisible.value = true;
};

const openEditDialog = (rol: Rol) => {
  editMode.value = true;
  currentRol.value = rol;
  form.nombre = rol.nombre;
  form.descripcion = rol.descripcion || '';
  form.clearErrors();
  dialogVisible.value = true;
};

const openPermisosDialog = async (rol: Rol) => {
  currentRol.value = rol;
  
  // Obtener permisos actuales del rol
  try {
    const response = await fetch(`/roles/${rol.id}/permisos`);
    const data = await response.json();
    permisosSeleccionados.value = data.permisos || [];
  } catch (error) {
    console.error('Error al cargar permisos:', error);
    permisosSeleccionados.value = [];
  }
  
  permisosDialogVisible.value = true;
};

const closeDialog = () => {
  dialogVisible.value = false;
  form.reset();
};

const submitForm = () => {
  if (editMode.value && currentRol.value) {
    form.put(`/roles/${currentRol.value.id}`, {
      onSuccess: () => {
        closeDialog();
      },
    });
  } else {
    form.post('/roles', {
      onSuccess: () => {
        closeDialog();
      },
    });
  }
};

const guardarPermisos = () => {
  if (!currentRol.value) return;

  permisosForm.permisos = permisosSeleccionados.value;
  permisosForm.put(`/roles/${currentRol.value.id}`, {
    onSuccess: () => {
      permisosDialogVisible.value = false;
    },
  });
};

const confirmDelete = (rol: Rol) => {
  if (confirm(`¿Estás seguro de eliminar el rol "${rol.nombre}"?`)) {
    router.delete(`/roles/${rol.id}`);
  }
};
</script>
