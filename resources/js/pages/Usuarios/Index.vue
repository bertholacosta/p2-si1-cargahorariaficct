<template>
  <AppLayout title="Gestión de Usuarios">
    <div class="space-y-6">
    <!-- Header con botón de crear -->
    <div class="flex justify-between items-center">
      <div>
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Gestión de Usuarios</h2>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Administra los usuarios del sistema</p>
      </div>
      <Button
        label="Nuevo Usuario"
        icon="pi pi-plus"
        @click="openCreateDialog"
        severity="success"
      />
    </div>

    <!-- Tabla de usuarios -->
    <Card>
      <template #content>
        <DataTable
          :value="usuarios"
          :paginator="true"
          :rows="10"
          :rowsPerPageOptions="[5, 10, 20, 50]"
          stripedRows
          showGridlines
          tableStyle="min-width: 50rem"
        >
          <Column field="id" header="ID" sortable style="width: 5%"></Column>
          <Column field="username" header="Username" sortable style="width: 20%"></Column>
          <Column field="email" header="Email" sortable style="width: 25%"></Column>
          <Column field="rol.nombre" header="Rol" sortable style="width: 20%">
            <template #body="slotProps">
              <Tag :value="slotProps.data.rol?.nombre" :severity="getRolSeverity(slotProps.data.rol?.nombre)" />
            </template>
          </Column>
          <Column header="Acciones" style="width: 15%">
            <template #body="slotProps">
              <div class="flex gap-2">
                <Button
                  icon="pi pi-pencil"
                  @click="openEditDialog(slotProps.data)"
                  severity="info"
                  outlined
                  size="small"
                />
                <Button
                  icon="pi pi-trash"
                  @click="confirmDelete(slotProps.data)"
                  severity="danger"
                  outlined
                  size="small"
                />
              </div>
            </template>
          </Column>
        </DataTable>
      </template>
    </Card>

    <!-- Dialog para Crear/Editar Usuario -->
    <Dialog
      v-model:visible="dialogVisible"
      :header="isEditing ? 'Editar Usuario' : 'Crear Usuario'"
      :modal="true"
      :style="{ width: '500px' }"
    >
      <form @submit.prevent="submitForm" class="space-y-4 mt-4">
        <div class="flex flex-col gap-2">
          <label for="username" class="font-semibold">Username</label>
          <InputText
            id="username"
            v-model="form.username"
            :class="{ 'p-invalid': form.errors.username }"
            placeholder="Ingresa el username"
          />
          <small v-if="form.errors.username" class="text-red-500">{{ form.errors.username }}</small>
        </div>

        <div class="flex flex-col gap-2">
          <label for="email" class="font-semibold">Email</label>
          <InputText
            id="email"
            v-model="form.email"
            type="email"
            :class="{ 'p-invalid': form.errors.email }"
            placeholder="usuario@ejemplo.com"
          />
          <small v-if="form.errors.email" class="text-red-500">{{ form.errors.email }}</small>
        </div>

        <div class="flex flex-col gap-2">
          <label for="password" class="font-semibold">
            Contraseña {{ isEditing ? '(dejar vacío para no cambiar)' : '' }}
          </label>
          <Password
            id="password"
            v-model="form.password"
            :class="{ 'p-invalid': form.errors.password }"
            :feedback="false"
            toggleMask
            placeholder="Ingresa la contraseña"
          />
          <small v-if="form.errors.password" class="text-red-500">{{ form.errors.password }}</small>
        </div>

        <div class="flex flex-col gap-2">
          <label for="rol" class="font-semibold">Rol</label>
          <Select
            id="rol"
            v-model="form.id_rol"
            :options="roles"
            optionLabel="nombre"
            optionValue="id"
            placeholder="Selecciona un rol"
            :class="{ 'p-invalid': form.errors.id_rol }"
          />
          <small v-if="form.errors.id_rol" class="text-red-500">{{ form.errors.id_rol }}</small>
        </div>

        <div class="flex justify-end gap-2 mt-6">
          <Button
            label="Cancelar"
            @click="dialogVisible = false"
            severity="secondary"
            outlined
            type="button"
          />
          <Button
            :label="isEditing ? 'Actualizar' : 'Crear'"
            type="submit"
            :loading="form.processing"
            severity="success"
          />
        </div>
      </form>
    </Dialog>

    <!-- Dialog de Confirmación para Eliminar -->
    <Dialog
      v-model:visible="deleteDialogVisible"
      header="Confirmar Eliminación"
      :modal="true"
      :style="{ width: '400px' }"
    >
      <div class="flex items-center gap-3">
        <i class="pi pi-exclamation-triangle text-red-500 text-3xl"></i>
        <span>¿Estás seguro de que deseas eliminar al usuario <strong>{{ usuarioToDelete?.username }}</strong>?</span>
      </div>
      <template #footer>
        <Button
          label="Cancelar"
          @click="deleteDialogVisible = false"
          severity="secondary"
          outlined
        />
        <Button
          label="Eliminar"
          @click="deleteUsuario"
          severity="danger"
          :loading="deleteForm.processing"
        />
      </template>
    </Dialog>
  </div>
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
import Password from 'primevue/password';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import type { User, Rol } from '@/types';

const props = defineProps<{
  usuarios: User[];
  roles: Rol[];
}>();

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
</script>
