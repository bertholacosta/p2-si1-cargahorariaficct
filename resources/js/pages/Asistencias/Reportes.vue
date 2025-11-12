<template>
    <div class="container mx-auto p-6">
        <Card>
            <template #title>
                <div class="flex items-center gap-3">
                    <i class="pi pi-chart-bar text-3xl text-blue-500"></i>
                    <span>Reportes de Horarios y Asistencias</span>
                </div>
            </template>
            <template #content>
                <div class="space-y-6">
                    <!-- Descripción -->
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4">
                        <p class="text-gray-700">
                            <i class="pi pi-info-circle mr-2"></i>
                            Genera reportes detallados de asistencias aplicando filtros por gestión, fechas, docente, aula o grupo.
                        </p>
                    </div>

                    <!-- Filtros -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Gestión -->
                        <div class="field">
                            <label for="gestion" class="block mb-2 font-semibold">
                                <i class="pi pi-calendar mr-2"></i>Gestión
                            </label>
                            <Select
                                v-model="filtros.id_gestion"
                                :options="gestiones"
                                optionLabel="label"
                                optionValue="id"
                                placeholder="Seleccionar gestión"
                                class="w-full"
                                showClear
                            />
                        </div>

                        <!-- Fecha Inicio -->
                        <div class="field">
                            <label for="fechaInicio" class="block mb-2 font-semibold">
                                <i class="pi pi-calendar-plus mr-2"></i>Fecha Inicio
                            </label>
                            <DatePicker
                                v-model="filtros.fecha_inicio"
                                dateFormat="yy-mm-dd"
                                placeholder="Seleccionar fecha"
                                class="w-full"
                                showIcon
                            />
                        </div>

                        <!-- Fecha Fin -->
                        <div class="field">
                            <label for="fechaFin" class="block mb-2 font-semibold">
                                <i class="pi pi-calendar-minus mr-2"></i>Fecha Fin
                            </label>
                            <DatePicker
                                v-model="filtros.fecha_fin"
                                dateFormat="yy-mm-dd"
                                placeholder="Seleccionar fecha"
                                class="w-full"
                                showIcon
                            />
                        </div>

                        <!-- Docente -->
                        <div class="field">
                            <label for="docente" class="block mb-2 font-semibold">
                                <i class="pi pi-user mr-2"></i>Docente
                            </label>
                            <Select
                                v-model="filtros.codigo_docente"
                                :options="docentes"
                                optionLabel="nombre_completo"
                                optionValue="codigo"
                                placeholder="Todos los docentes"
                                class="w-full"
                                filter
                                showClear
                            >
                                <template #value="slotProps">
                                    <div v-if="slotProps.value" class="flex items-center gap-2">
                                        <Avatar
                                            :label="obtenerIniciales(slotProps.value)"
                                            shape="circle"
                                            size="small"
                                            class="bg-blue-500 text-white"
                                        />
                                        <span>{{ obtenerNombreDocente(slotProps.value) }}</span>
                                    </div>
                                    <span v-else>{{ slotProps.placeholder }}</span>
                                </template>
                                <template #option="slotProps">
                                    <div class="flex items-center gap-2">
                                        <Avatar
                                            :label="obtenerInicialesObj(slotProps.option)"
                                            shape="circle"
                                            size="small"
                                            class="bg-blue-500 text-white"
                                        />
                                        <div>
                                            <div class="font-semibold">{{ slotProps.option.nombre_completo }}</div>
                                            <div class="text-sm text-gray-500">{{ slotProps.option.codigo }}</div>
                                        </div>
                                    </div>
                                </template>
                            </Select>
                        </div>

                        <!-- Aula -->
                        <div class="field">
                            <label for="aula" class="block mb-2 font-semibold">
                                <i class="pi pi-building mr-2"></i>Aula
                            </label>
                            <Select
                                v-model="filtros.id_aula"
                                :options="aulas"
                                optionLabel="nombre"
                                optionValue="id"
                                placeholder="Todas las aulas"
                                class="w-full"
                                filter
                                showClear
                            />
                        </div>

                        <!-- Grupo -->
                        <div class="field">
                            <label for="grupo" class="block mb-2 font-semibold">
                                <i class="pi pi-users mr-2"></i>Grupo
                            </label>
                            <Select
                                v-model="filtros.id_grupo"
                                :options="grupos"
                                optionLabel="nombre"
                                optionValue="id"
                                placeholder="Todos los grupos"
                                class="w-full"
                                filter
                                showClear
                            />
                        </div>
                    </div>

                    <!-- Resumen de filtros activos -->
                    <div v-if="hayFiltrosActivos" class="bg-green-50 border border-green-200 rounded p-3">
                        <div class="flex items-start gap-2">
                            <i class="pi pi-filter text-green-600 mt-1"></i>
                            <div class="flex-1">
                                <p class="font-semibold text-green-800 mb-2">Filtros aplicados:</p>
                                <div class="flex flex-wrap gap-2">
                                    <Tag v-if="filtros.id_gestion" severity="info" rounded>
                                        <i class="pi pi-calendar mr-1"></i>
                                        {{ obtenerNombreGestion(filtros.id_gestion) }}
                                    </Tag>
                                    <Tag v-if="filtros.fecha_inicio" severity="info" rounded>
                                        <i class="pi pi-calendar-plus mr-1"></i>
                                        Desde: {{ formatearFecha(filtros.fecha_inicio) }}
                                    </Tag>
                                    <Tag v-if="filtros.fecha_fin" severity="info" rounded>
                                        <i class="pi pi-calendar-minus mr-1"></i>
                                        Hasta: {{ formatearFecha(filtros.fecha_fin) }}
                                    </Tag>
                                    <Tag v-if="filtros.codigo_docente" severity="success" rounded>
                                        <i class="pi pi-user mr-1"></i>
                                        {{ obtenerNombreDocente(filtros.codigo_docente) }}
                                    </Tag>
                                    <Tag v-if="filtros.id_aula" severity="warning" rounded>
                                        <i class="pi pi-building mr-1"></i>
                                        {{ obtenerNombreAula(filtros.id_aula) }}
                                    </Tag>
                                    <Tag v-if="filtros.id_grupo" severity="danger" rounded>
                                        <i class="pi pi-users mr-1"></i>
                                        {{ obtenerNombreGrupo(filtros.id_grupo) }}
                                    </Tag>
                                </div>
                            </div>
                            <Button
                                icon="pi pi-times"
                                rounded
                                text
                                severity="secondary"
                                @click="limpiarFiltros"
                                v-tooltip.top="'Limpiar filtros'"
                            />
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="flex flex-wrap gap-3 justify-end">
                        <Button
                            label="Limpiar Filtros"
                            icon="pi pi-filter-slash"
                            severity="secondary"
                            outlined
                            @click="limpiarFiltros"
                            :disabled="!hayFiltrosActivos"
                        />
                        <Button
                            label="Generar Reporte"
                            icon="pi pi-file-excel"
                            severity="success"
                            @click="generarReporte"
                            :loading="generando"
                        />
                    </div>

                    <!-- Información adicional -->
                    <div class="bg-gray-50 border border-gray-200 rounded p-4">
                        <h3 class="font-semibold mb-2 flex items-center gap-2">
                            <i class="pi pi-info-circle text-blue-500"></i>
                            Información del Reporte
                        </h3>
                        <ul class="list-disc list-inside space-y-1 text-gray-700 text-sm">
                            <li>El reporte incluye detalles completos de horarios: fecha, hora, docente, materia, grupo y aula</li>
                            <li>Los estados de asistencia se muestran con colores: verde (presente/justificada), rojo (falta), amarillo (retraso)</li>
                            <li>Se incluye información de justificaciones y observaciones</li>
                            <li>El archivo se genera en formato Excel (.xlsx) con formato profesional</li>
                            <li>Si no seleccionas filtros, se generará el reporte completo de todos los registros</li>
                        </ul>
                    </div>
                </div>
            </template>
        </Card>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Select from 'primevue/select';
import DatePicker from 'primevue/datepicker';
import Avatar from 'primevue/avatar';
import Tag from 'primevue/tag';

const props = defineProps({
    gestiones: Array,
    docentes: Array,
    aulas: Array,
    grupos: Array,
});

// Preparar docentes con nombre completo
const docentesFormateados = computed(() => {
    return props.docentes.map(doc => ({
        ...doc,
        nombre_completo: `${doc.nombre} ${doc.apellidos}`
    }));
});

const filtros = ref({
    id_gestion: null,
    fecha_inicio: null,
    fecha_fin: null,
    codigo_docente: null,
    id_aula: null,
    id_grupo: null,
});

const generando = ref(false);

const hayFiltrosActivos = computed(() => {
    return Object.values(filtros.value).some(v => v !== null && v !== undefined);
});

const limpiarFiltros = () => {
    filtros.value = {
        id_gestion: null,
        fecha_inicio: null,
        fecha_fin: null,
        codigo_docente: null,
        id_aula: null,
        id_grupo: null,
    };
};

const generarReporte = () => {
    generando.value = true;
    
    // Construir query params
    const params = new URLSearchParams();
    
    if (filtros.value.id_gestion) params.append('id_gestion', filtros.value.id_gestion);
    if (filtros.value.fecha_inicio) params.append('fecha_inicio', formatearFechaParaBackend(filtros.value.fecha_inicio));
    if (filtros.value.fecha_fin) params.append('fecha_fin', formatearFechaParaBackend(filtros.value.fecha_fin));
    if (filtros.value.codigo_docente) params.append('codigo_docente', filtros.value.codigo_docente);
    if (filtros.value.id_aula) params.append('id_aula', filtros.value.id_aula);
    if (filtros.value.id_grupo) params.append('id_grupo', filtros.value.id_grupo);
    
    // Descargar archivo
    const url = `/asistencias/exportar-reporte-horarios?${params.toString()}`;
    window.location.href = url;
    
    setTimeout(() => {
        generando.value = false;
    }, 2000);
};

// Utilidades
const obtenerNombreGestion = (id) => {
    const gestion = props.gestiones.find(g => g.id === id);
    return gestion ? gestion.label : '';
};

const obtenerNombreDocente = (codigo) => {
    const docente = docentesFormateados.value.find(d => d.codigo === codigo);
    return docente ? docente.nombre_completo : '';
};

const obtenerIniciales = (codigo) => {
    const docente = docentesFormateados.value.find(d => d.codigo === codigo);
    if (!docente) return '?';
    return `${docente.nombre[0]}${docente.apellidos[0]}`;
};

const obtenerInicialesObj = (docente) => {
    if (!docente) return '?';
    return `${docente.nombre[0]}${docente.apellidos[0]}`;
};

const obtenerNombreAula = (id) => {
    const aula = props.aulas.find(a => a.id === id);
    return aula ? aula.nombre : '';
};

const obtenerNombreGrupo = (id) => {
    const grupo = props.grupos.find(g => g.id === id);
    return grupo ? grupo.nombre : '';
};

const formatearFecha = (fecha) => {
    if (!fecha) return '';
    if (typeof fecha === 'string') return fecha;
    const d = new Date(fecha);
    return d.toISOString().split('T')[0];
};

const formatearFechaParaBackend = (fecha) => {
    if (!fecha) return '';
    if (typeof fecha === 'string') return fecha;
    const d = new Date(fecha);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};
</script>

<style scoped>
.field label {
    color: #374151;
}
</style>
