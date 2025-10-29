<template>
  <AppLayout :title="`Materias Habilitadas - ${docente.nombre} ${docente.apellidos}`">
    <div class="flex flex-col h-full space-y-4 lg:space-y-6">
      <!-- Header -->
      <div class="shrink-0">
        <div class="flex items-center gap-3 mb-4">
          <Button
            icon="pi pi-arrow-left"
            @click="goBack"
            text
            rounded
            severity="secondary"
            class="!p-2"
          />
          <div>
            <h2 class="text-xl lg:text-2xl font-bold text-gray-800 dark:text-white">
              Materias Habilitadas
            </h2>
            <p class="text-sm lg:text-base text-gray-600 dark:text-gray-400 mt-1">
              Docente: {{ docente.nombre }} {{ docente.apellidos }} ({{ docente.codigo }})
            </p>
          </div>
        </div>

        <!-- Información del docente -->
        <Card class="mb-4">
          <template #content>
            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
              <div class="space-y-2">
                <div class="flex items-center gap-2">
                  <i class="pi pi-user text-blue-500"></i>
                  <span class="text-sm font-semibold">{{ docente.nombre }} {{ docente.apellidos }}</span>
                </div>
                <div class="flex items-center gap-2">
                  <i class="pi pi-id-card text-green-500"></i>
                  <span class="text-xs sm:text-sm">CI: {{ docente.ci }}</span>
                </div>
                <div v-if="docente.usuario" class="flex items-center gap-2">
                  <i class="pi pi-at text-purple-500"></i>
                  <span class="text-xs sm:text-sm">Usuario: {{ docente.usuario.username }}</span>
                </div>
              </div>
              <div class="text-left sm:text-right">
                <p class="text-xs text-gray-500">Materias habilitadas</p>
                <p class="text-2xl font-bold text-blue-600">{{ selectedMaterias.length }}</p>
              </div>
            </div>
          </template>
        </Card>
      </div>

      <!-- Contenido principal con scroll -->
      <div class="flex-1 overflow-hidden">
        <Card class="h-full flex flex-col">
          <template #content>
            <div class="space-y-4">
              <!-- Botones de acción -->
              <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                <Button
                  label="Seleccionar Todas"
                  icon="pi pi-check-square"
                  @click="selectAll"
                  severity="secondary"
                  outlined
                  size="small"
                  class="w-full sm:w-auto text-sm"
                />
                <Button
                  label="Deseleccionar Todas"
                  icon="pi pi-times"
                  @click="deselectAll"
                  severity="secondary"
                  outlined
                  size="small"
                  class="w-full sm:w-auto text-sm"
                />
                <Button
                  label="Guardar Cambios"
                  icon="pi pi-save"
                  @click="saveChanges"
                  severity="success"
                  :loading="form.processing"
                  size="small"
                  class="w-full sm:w-auto text-sm"
                />
              </div>

              <!-- Lista de materias con scroll -->
              <div class="flex-1 overflow-auto max-h-[calc(100vh-400px)]">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                  <div
                    v-for="materia in todasMaterias"
                    :key="materia.id"
                    @click="toggleMateria(materia.id)"
                    :class="[
                      'border-2 rounded-lg p-4 cursor-pointer transition-all',
                      isMateriaSelected(materia.id)
                        ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                        : 'border-gray-200 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-600'
                    ]"
                  >
                    <div class="flex items-start justify-between gap-2">
                      <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-2">
                          <Checkbox
                            :modelValue="isMateriaSelected(materia.id)"
                            :binary="true"
                            @click.stop="toggleMateria(materia.id)"
                          />
                          <Tag
                            :value="materia.sigla"
                            :severity="isMateriaSelected(materia.id) ? 'info' : 'secondary'"
                            class="text-xs font-mono"
                          />
                        </div>
                        <h3 class="font-semibold text-sm text-gray-800 dark:text-white mb-2 line-clamp-2">
                          {{ materia.nombre }}
                        </h3>
                        <div class="flex flex-wrap gap-2 text-xs text-gray-600 dark:text-gray-400">
                          <span v-if="materia.carga_horaria" class="flex items-center gap-1">
                            <i class="pi pi-clock text-xs"></i>
                            {{ materia.carga_horaria }} hrs
                          </span>
                          <span v-if="materia.creditos" class="flex items-center gap-1">
                            <i class="pi pi-star text-xs"></i>
                            {{ materia.creditos }} créditos
                          </span>
                        </div>
                      </div>
                      <i
                        v-if="isMateriaSelected(materia.id)"
                        class="pi pi-check-circle text-blue-500 text-lg"
                      ></i>
                    </div>
                  </div>
                </div>

                <!-- Mensaje cuando no hay materias -->
                <div v-if="todasMaterias.length === 0" class="text-center py-8">
                  <i class="pi pi-inbox text-4xl text-gray-400 mb-4"></i>
                  <p class="text-gray-500">No hay materias disponibles</p>
                </div>
              </div>
            </div>
          </template>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Checkbox from 'primevue/checkbox';

interface Materia {
  id: number;
  sigla: string;
  nombre: string;
  carga_horaria: number | null;
  creditos: number | null;
}

interface Usuario {
  id: number;
  username: string;
  email: string;
}

interface Docente {
  codigo: string;
  nombre: string;
  apellidos: string;
  ci: string;
  usuario?: Usuario;
  materias: Materia[];
}

const props = defineProps<{
  docente: Docente;
  todasMaterias: Materia[];
}>();

// Inicializar con las materias ya habilitadas
const selectedMaterias = ref<number[]>(
  props.docente.materias.map(m => m.id)
);

const form = useForm({
  materias: selectedMaterias.value,
});

const isMateriaSelected = (materiaId: number): boolean => {
  return selectedMaterias.value.includes(materiaId);
};

const toggleMateria = (materiaId: number) => {
  const index = selectedMaterias.value.indexOf(materiaId);
  if (index > -1) {
    selectedMaterias.value.splice(index, 1);
  } else {
    selectedMaterias.value.push(materiaId);
  }
};

const selectAll = () => {
  selectedMaterias.value = props.todasMaterias.map(m => m.id);
};

const deselectAll = () => {
  selectedMaterias.value = [];
};

const saveChanges = () => {
  form.materias = selectedMaterias.value;
  form.put(`/docentes/${props.docente.codigo}/materias-habilitadas`, {
    preserveScroll: true,
  });
};

const goBack = () => {
  router.visit('/docentes');
};
</script>
