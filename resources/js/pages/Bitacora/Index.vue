<template>
  <AppLayout title="Bitácora del Sistema">
    <div class="space-y-4">
      <!-- Header -->
      <Card>
        <template #title>
          <div class="flex justify-between items-center">
            <span>Bitácora de Actividades</span>
            <Button
              label="Exportar CSV"
              icon="pi pi-download"
              @click="exportarCSV"
              severity="success"
              size="small"
            />
          </div>
        </template>
        <template #content>
          <!-- Filtros -->
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="flex flex-col gap-2">
              <label class="font-semibold text-sm">Fecha Inicio:</label>
              <input
                v-model="filtros.fecha_inicio"
                type="date"
                class="p-2 border rounded text-sm"
              />
            </div>
            <div class="flex flex-col gap-2">
              <label class="font-semibold text-sm">Fecha Fin:</label>
              <input
                v-model="filtros.fecha_fin"
                type="date"
                class="p-2 border rounded text-sm"
              />
            </div>
            <div class="flex flex-col gap-2">
              <label class="font-semibold text-sm">Buscar:</label>
              <input
                v-model="filtros.busqueda"
                type="text"
                placeholder="Buscar en acciones..."
                class="p-2 border rounded text-sm"
              />
            </div>
            <div class="flex items-end">
              <Button
                label="Filtrar"
                icon="pi pi-search"
                @click="aplicarFiltros"
                class="w-full"
                size="small"
              />
            </div>
          </div>
        </template>
      </Card>

      <!-- Tabla -->
      <Card>
        <template #content>
          <DataTable
            :value="registros.data"
            stripedRows
            size="small"
            :paginator="false"
            class="text-sm"
          >
            <Column field="id" header="ID" style="width: 80px" />
            <Column field="fecha" header="Fecha" style="width: 180px" />
            <Column field="usuario_nombre" header="Usuario" style="width: 200px" />
            <Column field="ip" header="IP" style="width: 150px" />
            <Column field="accion" header="Acción">
              <template #body="{ data }">
                <span class="text-gray-700">{{ data.accion }}</span>
              </template>
            </Column>
          </DataTable>

          <!-- Paginación -->
          <div class="flex justify-between items-center mt-4 text-sm">
            <div class="text-gray-600">
              Mostrando {{ registros.from || 0 }} a {{ registros.to || 0 }} de {{ registros.total }} registros
            </div>
            <div class="flex gap-2">
              <Button
                v-for="link in registros.links"
                :key="link.label"
                :label="link.label.replace('&laquo;', '«').replace('&raquo;', '»')"
                @click="cambiarPagina(link.url)"
                :disabled="!link.url"
                :severity="link.active ? 'primary' : 'secondary'"
                outlined
                size="small"
                text
              />
            </div>
          </div>
        </template>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

interface Registro {
  id: number;
  accion: string;
  fecha: string;
  ip: string | null;
  usuario_nombre: string;
}

interface PaginatedData {
  data: Registro[];
  current_page: number;
  from: number;
  to: number;
  total: number;
  links: Array<{
    url: string | null;
    label: string;
    active: boolean;
  }>;
}

interface Filtros {
  fecha_inicio: string | null;
  fecha_fin: string | null;
  usuario: number | null;
  busqueda: string | null;
}

const props = defineProps<{
  registros: PaginatedData;
  filtros: Filtros;
}>();

const filtros = ref<Filtros>({
  fecha_inicio: props.filtros.fecha_inicio,
  fecha_fin: props.filtros.fecha_fin,
  usuario: props.filtros.usuario,
  busqueda: props.filtros.busqueda,
});

const aplicarFiltros = () => {
  router.visit('/bitacora', {
    data: {
      fecha_inicio: filtros.value.fecha_inicio,
      fecha_fin: filtros.value.fecha_fin,
      usuario: filtros.value.usuario,
      busqueda: filtros.value.busqueda,
    },
    preserveState: true,
  });
};

const cambiarPagina = (url: string | null) => {
  if (url) {
    router.visit(url, {
      preserveState: true,
    });
  }
};

const exportarCSV = () => {
  const params = new URLSearchParams();
  if (filtros.value.fecha_inicio) params.append('fecha_inicio', filtros.value.fecha_inicio);
  if (filtros.value.fecha_fin) params.append('fecha_fin', filtros.value.fecha_fin);
  if (filtros.value.usuario) params.append('usuario', filtros.value.usuario.toString());
  if (filtros.value.busqueda) params.append('busqueda', filtros.value.busqueda);
  
  window.location.href = `/bitacora/exportar?${params.toString()}`;
};
</script>
