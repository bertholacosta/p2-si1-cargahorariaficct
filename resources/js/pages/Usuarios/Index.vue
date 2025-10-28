<template>
  <AppLayout title="Gestión de Usuarios">
    <div class="flex flex-col h-full space-y-4 lg:space-y-6">
    <!-- Header con botón de crear -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 shrink-0">
      <div>
        <h2 class="text-xl lg:text-2xl font-bold text-gray-800 dark:text-white">Gestión de Usuarios</h2>
        <p class="text-sm lg:text-base text-gray-600 dark:text-gray-400 mt-1">Administra los usuarios del sistema</p>
      </div>
      <Button
        label="Nuevo Usuario"
        icon="pi pi-plus"
        @click="openCreateDialog"
        severity="success"
        class="w-full sm:w-auto"
      />
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
                    icon="pi pi-pencil"
                    @click="openEditDialog(slotProps.data)"
                    severity="info"
                    outlined
                    size="small"
                    class="!p-1 sm:!p-2"
                  />
                  <Button
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
