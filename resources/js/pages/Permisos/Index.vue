<template>
  <AppLayout title="Gestión de Permisos">
    <div class="space-y-4">
      <!-- Header con botón crear -->
      <Card>
        <template #title>
          <div class="flex justify-between items-center">
            <span>Permisos del Sistema</span>
            <Button
              label="Nuevo Permiso"
              icon="pi pi-plus"
              @click="openCreateDialog"
              severity="success"
              size="small"
            />
          </div>
        </template>
      </Card>

      <!-- Tabla de permisos por módulo -->
      <div v-for="(perms, modulo) in permisosGrouped" :key="modulo">
        <Card>
          <template #title>
            <h3 class="text-lg font-bold text-blue-700">{{ modulo }}</h3>
          </template>
          <template #content>
            <DataTable
              :value="perms"
              stripedRows
              size="small"
            >
              <Column field="nombre" header="Nombre" />
              <Column field="slug" header="Slug" />
              <Column field="descripcion" header="Descripción" />
              <Column header="Acciones" style="width: 150px">
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
    </div>

    <!-- Dialog Crear/Editar -->
    <Dialog
      v-model:visible="dialogVisible"
      :header="editMode ? 'Editar Permiso' : 'Nuevo Permiso'"
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
            placeholder="Ej: Ver usuarios, Crear materias, etc."
          />
          <small v-if="form.errors.nombre" class="text-red-600">{{ form.errors.nombre }}</small>
        </div>

        <div class="flex flex-col gap-2">
          <label class="font-semibold text-sm">Slug*</label>
          <input
            v-model="form.slug"
            type="text"
            class="p-2 border rounded text-sm"
            placeholder="Ej: usuarios.ver, materias.crear"
          />
          <small v-if="form.errors.slug" class="text-red-600">{{ form.errors.slug }}</small>
          <small class="text-gray-600">Formato: modulo.accion (sin espacios, minúsculas)</small>
        </div>

        <div class="flex flex-col gap-2">
          <label class="font-semibold text-sm">Módulo*</label>
          <input
            v-model="form.modulo"
            type="text"
            class="p-2 border rounded text-sm"
            placeholder="Ej: Usuarios, Materias, Horarios"
          />
          <small v-if="form.errors.modulo" class="text-red-600">{{ form.errors.modulo }}</small>
        </div>

        <div class="flex flex-col gap-2">
          <label class="font-semibold text-sm">Descripción</label>
          <textarea
            v-model="form.descripcion"
            rows="3"
            class="p-2 border rounded text-sm"
            placeholder="Descripción del permiso..."
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
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

interface Permiso {
  id: number;
  nombre: string;
  slug: string;
  modulo: string;
  descripcion: string | null;
}

const props = defineProps<{
  permisos: Permiso[];
}>();

const dialogVisible = ref(false);
const editMode = ref(false);
const currentPermiso = ref<Permiso | null>(null);

const form = useForm({
  nombre: '',
  slug: '',
  modulo: '',
  descripcion: '',
});

// Agrupar permisos por módulo
const permisosGrouped = computed(() => {
  const grouped: Record<string, Permiso[]> = {};
  props.permisos.forEach(permiso => {
    if (!grouped[permiso.modulo]) {
      grouped[permiso.modulo] = [];
    }
    grouped[permiso.modulo].push(permiso);
  });
  return grouped;
});

const openCreateDialog = () => {
  editMode.value = false;
  form.reset();
  form.clearErrors();
  dialogVisible.value = true;
};

const openEditDialog = (permiso: Permiso) => {
  editMode.value = true;
  currentPermiso.value = permiso;
  form.nombre = permiso.nombre;
  form.slug = permiso.slug;
  form.modulo = permiso.modulo;
  form.descripcion = permiso.descripcion || '';
  form.clearErrors();
  dialogVisible.value = true;
};

const closeDialog = () => {
  dialogVisible.value = false;
  form.reset();
};

const submitForm = () => {
  if (editMode.value && currentPermiso.value) {
    form.put(`/permisos/${currentPermiso.value.id}`, {
      onSuccess: () => {
        closeDialog();
      },
    });
  } else {
    form.post('/permisos', {
      onSuccess: () => {
        closeDialog();
      },
    });
  }
};

const confirmDelete = (permiso: Permiso) => {
  if (confirm(`¿Estás seguro de eliminar el permiso "${permiso.nombre}"?`)) {
    router.delete(`/permisos/${permiso.id}`);
  }
};
</script>
