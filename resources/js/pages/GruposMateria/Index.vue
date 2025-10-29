<template>
  <AppLayout :title="`Grupos - ${materia.sigla}`">
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
              Grupos de la Materia
            </h2>
            <p class="text-sm lg:text-base text-gray-600 dark:text-gray-400 mt-1">
              Materia: {{ materia.sigla }} - {{ materia.nombre }}
            </p>
          </div>
        </div>

        <!-- Información de la materia -->
        <Card class="mb-4">
          <template #content>
            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
              <div class="space-y-2">
                <div class="flex items-center gap-2">
                  <Tag :value="materia.sigla" severity="info" class="text-sm font-mono" />
                  <span class="text-sm font-semibold">{{ materia.nombre }}</span>
                </div>
                <div class="flex flex-wrap gap-3 text-xs text-gray-600 dark:text-gray-400">
                  <span v-if="materia.carga_horaria" class="flex items-center gap-1">
                    <i class="pi pi-clock"></i>
                    {{ materia.carga_horaria }} hrs
                  </span>
                  <span v-if="materia.creditos" class="flex items-center gap-1">
                    <i class="pi pi-star"></i>
                    {{ materia.creditos }} créditos
                  </span>
                </div>
              </div>
              <div class="text-left sm:text-right">
                <p class="text-xs text-gray-500">Grupos asignados</p>
                <p class="text-2xl font-bold text-blue-600">{{ selectedGrupos.length }}</p>
              </div>
            </div>
          </template>
        </Card>
      </div>

      <!-- Contenido principal con scroll -->
      <div class="flex-1 overflow-hidden">
        <div class="h-full flex flex-col gap-4">
          <!-- Grupos Asignados -->
          <Card class="shrink-0">
            <template #title>
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <i class="pi pi-check-circle text-green-500 text-lg"></i>
                  <span class="text-base sm:text-lg">Grupos Asignados</span>
                </div>
                <Tag :value="selectedGrupos.length.toString()" severity="success" />
              </div>
            </template>
            <template #content>
              <div v-if="selectedGrupos.length > 0" class="flex flex-wrap gap-3">
                <div
                  v-for="grupoId in selectedGrupos"
                  :key="grupoId"
                  class="relative"
                >
                  <div
                    class="flex items-center gap-3 px-4 py-3 bg-green-50 dark:bg-green-900/20 border-2 border-green-500 rounded-lg"
                  >
                    <div
                      class="flex items-center justify-center w-12 h-12 bg-green-500 text-white rounded-lg font-bold text-lg"
                    >
                      {{ getGrupoNombre(grupoId) }}
                    </div>
                    <div>
                      <p class="font-semibold text-sm text-gray-800 dark:text-white">
                        Grupo {{ getGrupoNombre(grupoId) }}
                      </p>
                      <p class="text-xs text-gray-500 dark:text-gray-400">Asignado</p>
                    </div>
                    <Button
                      icon="pi pi-times"
                      @click="toggleGrupo(grupoId)"
                      severity="danger"
                      text
                      rounded
                      size="small"
                      class="!p-2"
                      v-tooltip.top="'Quitar grupo'"
                    />
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-6 text-gray-500">
                <i class="pi pi-info-circle text-3xl mb-2"></i>
                <p class="text-sm">No hay grupos asignados a esta materia</p>
              </div>
            </template>
          </Card>

          <!-- Grupos Disponibles -->
          <Card class="flex-1 overflow-hidden flex flex-col">
            <template #title>
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <i class="pi pi-plus-circle text-blue-500 text-lg"></i>
                  <span class="text-base sm:text-lg">Agregar Grupos</span>
                </div>
                <Tag :value="gruposDisponibles.length.toString()" severity="info" />
              </div>
            </template>
            <template #content>
              <div class="flex-1 overflow-auto">
                <div v-if="gruposDisponibles.length > 0" class="space-y-2">
                  <div
                    v-for="grupo in gruposDisponibles"
                    :key="grupo.id"
                    @click="toggleGrupo(grupo.id)"
                    class="flex items-center gap-3 px-4 py-3 border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-lg cursor-pointer hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/10 transition-all"
                  >
                    <div
                      class="flex items-center justify-center w-12 h-12 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg font-bold text-lg"
                    >
                      {{ grupo.nombre }}
                    </div>
                    <div class="flex-1">
                      <p class="font-semibold text-sm text-gray-800 dark:text-white">
                        Grupo {{ grupo.nombre }}
                      </p>
                      <p class="text-xs text-gray-500 dark:text-gray-400">Click para asignar</p>
                    </div>
                    <Button
                      icon="pi pi-plus"
                      severity="info"
                      text
                      rounded
                      size="small"
                      class="!p-2"
                    />
                  </div>
                </div>
                <div v-else class="text-center py-6 text-gray-500">
                  <i class="pi pi-check-circle text-3xl text-green-500 mb-2"></i>
                  <p class="text-sm">Todos los grupos están asignados</p>
                </div>
              </div>
            </template>
          </Card>
        </div>
      </div>

      <!-- Botón de guardar flotante -->
      <div class="fixed bottom-6 right-6 z-10">
        <Button
          label="Guardar Cambios"
          icon="pi pi-save"
          @click="saveChanges"
          severity="success"
          :loading="form.processing"
          size="large"
          class="shadow-lg"
        />
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Tag from 'primevue/tag';

interface Grupo {
  id: number;
  nombre: string;
}

interface Materia {
  id: number;
  sigla: string;
  nombre: string;
  carga_horaria: number | null;
  creditos: number | null;
  grupos: Grupo[];
}

const props = defineProps<{
  materia: Materia;
  todosGrupos: Grupo[];
}>();

// Inicializar con los grupos ya asignados
const selectedGrupos = ref<number[]>(
  props.materia.grupos.map(g => g.id)
);

const form = useForm({
  grupos: selectedGrupos.value,
});

// Computed para grupos disponibles (no asignados)
const gruposDisponibles = computed(() => {
  return props.todosGrupos.filter(g => !selectedGrupos.value.includes(g.id));
});

const getGrupoNombre = (grupoId: number): string => {
  const grupo = props.todosGrupos.find(g => g.id === grupoId);
  return grupo ? grupo.nombre : '';
};

const isGrupoSelected = (grupoId: number): boolean => {
  return selectedGrupos.value.includes(grupoId);
};

const toggleGrupo = (grupoId: number) => {
  const index = selectedGrupos.value.indexOf(grupoId);
  if (index > -1) {
    selectedGrupos.value.splice(index, 1);
  } else {
    selectedGrupos.value.push(grupoId);
  }
};

const saveChanges = () => {
  form.grupos = selectedGrupos.value;
  form.put(`/materias/${props.materia.id}/grupos`, {
    preserveScroll: true,
  });
};

const goBack = () => {
  router.visit('/materias');
};

const goToGrupos = () => {
  router.visit('/grupos');
};
</script>
