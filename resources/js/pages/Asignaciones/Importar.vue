<template>
  <AppLayout title="Importar Asignaciones Masivas">
    <div class="container mx-auto px-4 py-6 max-w-7xl">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Importación Masiva de Asignaciones</h1>
        <p class="text-sm text-gray-600 mt-1">
          Importe múltiples asignaciones desde un archivo Excel
        </p>
      </div>

      <!-- Paso 1: Configuración -->
      <Card v-if="paso === 1" class="mb-6">
        <template #title>
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold">
              1
            </div>
            <span>Configuración Inicial</span>
          </div>
        </template>
        <template #content>
          <div class="space-y-6">
            <!-- Selección de gestión -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Gestión *
              </label>
              <Select
                v-model="form.id_gestion"
                :options="gestiones"
                optionLabel="nombre_completo"
                optionValue="id"
                placeholder="Seleccionar gestión"
                class="w-full"
              />
            </div>

            <!-- Opción de auto-asignación -->
            <div class="flex items-start gap-3 p-4 bg-blue-50 border border-blue-200 rounded-lg">
              <Checkbox v-model="form.auto_asignar" inputId="auto_asignar" binary />
              <div class="flex-1">
                <label for="auto_asignar" class="font-medium text-gray-900 cursor-pointer">
                  Habilitar asignación automática de docentes
                </label>
                <p class="text-sm text-gray-600 mt-1">
                  Cuando use "AUTO" en el código de docente, el sistema asignará automáticamente
                  un docente habilitado para esa materia (el que tenga menos carga).
                </p>
              </div>
            </div>

            <!-- Descargar plantilla -->
            <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg">
              <div class="flex items-start gap-3">
                <i class="pi pi-info-circle text-2xl text-amber-600 flex-shrink-0"></i>
                <div class="flex-1">
                  <h4 class="font-semibold text-gray-900 mb-2">
                    Descargue primero la plantilla Excel
                  </h4>
                  <p class="text-sm text-gray-700 mb-3">
                    La plantilla incluye todos los códigos válidos de materias, docentes, aulas,
                    días y horarios. Complete los datos según las instrucciones.
                  </p>
                  <Button
                    label="Descargar Plantilla Excel"
                    icon="pi pi-download"
                    @click="descargarPlantilla"
                    :loading="descargandoPlantilla"
                    :disabled="!form.id_gestion"
                    severity="success"
                  />
                </div>
              </div>
            </div>

            <!-- Siguiente paso -->
            <div class="flex justify-end pt-4">
              <Button
                label="Siguiente: Subir Archivo"
                icon="pi pi-arrow-right"
                iconPos="right"
                @click="paso = 2"
                :disabled="!form.id_gestion"
              />
            </div>
          </div>
        </template>
      </Card>

      <!-- Paso 2: Subir archivo -->
      <Card v-if="paso === 2" class="mb-6">
        <template #title>
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold">
              2
            </div>
            <span>Subir Archivo Excel</span>
          </div>
        </template>
        <template #content>
          <div class="space-y-4">
            <!-- File upload -->
            <FileUpload
              ref="fileUpload"
              mode="basic"
              accept=".xlsx,.xls,.csv"
              :maxFileSize="10000000"
              :auto="false"
              chooseLabel="Seleccionar Archivo Excel"
              @select="archivoSeleccionado"
              class="w-full"
            />

            <div v-if="archivo" class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border">
              <i class="pi pi-file-excel text-3xl text-green-600"></i>
              <div class="flex-1">
                <p class="font-medium text-gray-900">{{ archivo.name }}</p>
                <p class="text-sm text-gray-600">
                  {{ (archivo.size / 1024).toFixed(2) }} KB
                </p>
              </div>
              <Button
                icon="pi pi-times"
                text
                rounded
                severity="danger"
                @click="quitarArchivo"
              />
            </div>

            <!-- Botones -->
            <div class="flex gap-3 pt-4">
              <Button
                label="Volver"
                icon="pi pi-arrow-left"
                @click="paso = 1"
                severity="secondary"
                outlined
              />
              <Button
                label="Procesar Archivo"
                icon="pi pi-cog"
                @click="procesarArchivo"
                :loading="procesando"
                :disabled="!archivo"
              />
            </div>
          </div>
        </template>
      </Card>

      <!-- Paso 3: Preview y validación -->
      <Card v-if="paso === 3" class="mb-6">
        <template #title>
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold">
              3
            </div>
            <span>Revisión y Validación</span>
          </div>
        </template>
        <template #content>
          <div class="space-y-6">
            <!-- Resumen -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg text-center">
                <i class="pi pi-check-circle text-3xl text-blue-600 mb-2 block"></i>
                <p class="text-2xl font-bold text-blue-700">{{ preview.total_filas }}</p>
                <p class="text-sm text-gray-600">Filas Válidas</p>
              </div>

              <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-center">
                <i class="pi pi-times-circle text-3xl text-red-600 mb-2 block"></i>
                <p class="text-2xl font-bold text-red-700">{{ preview.errores?.length || 0 }}</p>
                <p class="text-sm text-gray-600">Filas con Errores</p>
              </div>

              <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-center">
                <i class="pi pi-exclamation-triangle text-3xl text-yellow-600 mb-2 block"></i>
                <p class="text-2xl font-bold text-yellow-700">{{ preview.advertencias?.length || 0 }}</p>
                <p class="text-sm text-gray-600">Advertencias</p>
              </div>
            </div>

            <!-- Errores -->
            <div v-if="preview.errores && preview.errores.length > 0" class="space-y-2">
              <h3 class="font-semibold text-red-700 flex items-center gap-2">
                <i class="pi pi-times-circle"></i>
                Errores Encontrados
              </h3>
              <div
                v-for="(error, index) in preview.errores"
                :key="`error-${index}`"
                class="p-3 bg-red-50 border border-red-200 rounded-lg"
              >
                <p class="font-medium text-red-900">Fila {{ error.fila }}:</p>
                <ul class="list-disc list-inside text-sm text-red-700 mt-1">
                  <li v-for="(mensaje, idx) in error.errores" :key="idx">{{ mensaje }}</li>
                </ul>
              </div>
            </div>

            <!-- Advertencias -->
            <div v-if="preview.advertencias && preview.advertencias.length > 0" class="space-y-2">
              <h3 class="font-semibold text-yellow-700 flex items-center gap-2">
                <i class="pi pi-exclamation-triangle"></i>
                Advertencias
              </h3>
              <Accordion>
                <AccordionTab
                  v-for="(adv, index) in preview.advertencias"
                  :key="`adv-${index}`"
                  :header="`Fila ${adv.fila} - ${adv.mensajes.length} advertencia(s)`"
                >
                  <ul class="list-disc list-inside text-sm text-yellow-700">
                    <li v-for="(mensaje, idx) in adv.mensajes" :key="idx">{{ mensaje }}</li>
                  </ul>
                </AccordionTab>
              </Accordion>
            </div>

            <!-- Preview de asignaciones -->
            <div v-if="preview.preview && preview.preview.length > 0">
              <h3 class="font-semibold text-gray-900 flex items-center gap-2 mb-3">
                <i class="pi pi-list"></i>
                Preview de Asignaciones ({{ preview.preview.length }})
              </h3>
              <DataTable
                :value="preview.preview"
                :paginator="true"
                :rows="10"
                stripedRows
                showGridlines
                class="text-sm"
              >
                <Column field="fila" header="Fila" :style="{ width: '60px' }" />
                <Column field="codigo_materia" header="Materia" />
                <Column field="numero_grupo" header="Grupo" />
                <Column field="codigo_docente" header="Docente" />
                <Column field="dia" header="Día" />
                <Column field="hora_inicio" header="Hora" />
                <Column field="codigo_aula" header="Aula" />
                <Column field="modulos" header="Módulos" />
              </DataTable>
            </div>

            <!-- Botones -->
            <div class="flex gap-3 pt-4">
              <Button
                label="Cancelar"
                icon="pi pi-times"
                @click="cancelarImportacion"
                severity="danger"
                outlined
              />
              <Button
                label="Importar Asignaciones"
                icon="pi pi-check"
                @click="confirmarImportacion"
                :loading="importando"
                :disabled="preview.tiene_errores"
                severity="success"
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
import Select from 'primevue/select';
import Checkbox from 'primevue/checkbox';
import FileUpload from 'primevue/fileupload';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Accordion from 'primevue/accordion';
import AccordionTab from 'primevue/accordiontab';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';

const props = defineProps<{
  gestiones: any[];
}>();

const toast = useToast();

// Estado
const paso = ref(1);
const form = ref<{
  id_gestion: number | null;
  auto_asignar: boolean;
}>({
  id_gestion: null,
  auto_asignar: false,
});
const archivo = ref<File | null>(null);
const preview = ref<any>({});
const descargandoPlantilla = ref(false);
const procesando = ref(false);
const importando = ref(false);

// Métodos
const descargarPlantilla = async () => {
  if (!form.value.id_gestion) return;

  descargandoPlantilla.value = true;

  try {
    const response = await axios.get('/asignaciones/importar/plantilla', {
      params: { id_gestion: form.value.id_gestion },
      responseType: 'blob',
    });

    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `Plantilla_Asignaciones.xlsx`);
    document.body.appendChild(link);
    link.click();
    link.remove();

    toast.add({
      severity: 'success',
      summary: 'Descarga Exitosa',
      detail: 'Plantilla descargada correctamente',
      life: 3000,
    });
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'Error al descargar la plantilla',
      life: 5000,
    });
  } finally {
    descargandoPlantilla.value = false;
  }
};

const archivoSeleccionado = (event: any) => {
  archivo.value = event.files[0];
};

const quitarArchivo = () => {
  archivo.value = null;
};

const procesarArchivo = async () => {
  if (!archivo.value) return;

  procesando.value = true;

  try {
    const formData = new FormData();
    formData.append('archivo', archivo.value);
    formData.append('id_gestion', form.value.id_gestion?.toString() || '');
    formData.append('auto_asignar', form.value.auto_asignar ? '1' : '0');

    const response = await axios.post('/asignaciones/importar/procesar', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });

    preview.value = response.data;
    paso.value = 3;

    toast.add({
      severity: 'info',
      summary: 'Archivo Procesado',
      detail: `${preview.value.total_filas} filas validadas`,
      life: 3000,
    });
  } catch (error: any) {
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: error.response?.data?.message || 'Error al procesar el archivo',
      life: 5000,
    });
  } finally {
    procesando.value = false;
  }
};

const confirmarImportacion = async () => {
  importando.value = true;

  try {
    const response = await axios.post('/asignaciones/importar/confirmar');

    if (response.data.success) {
      toast.add({
        severity: 'success',
        summary: 'Importación Exitosa',
        detail: `${response.data.importadas} asignaciones importadas correctamente`,
        life: 5000,
      });

      // Redirigir a la lista de asignaciones
      router.visit('/asignaciones');
    }
  } catch (error: any) {
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: error.response?.data?.message || 'Error al importar',
      life: 5000,
    });
  } finally {
    importando.value = false;
  }
};

const cancelarImportacion = async () => {
  try {
    await axios.post('/asignaciones/importar/cancelar');
    paso.value = 1;
    archivo.value = null;
    preview.value = {};
    form.value = {
      id_gestion: null,
      auto_asignar: false,
    };
  } catch (error) {
    console.error('Error al cancelar:', error);
  }
};
</script>
