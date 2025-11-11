<template>
    <Dialog 
        v-model:visible="visible" 
        modal 
        header="Generar Código QR"
        :style="{ width: '500px' }"
        @hide="cerrarModal"
    >
        <div v-if="!qrGenerado" class="space-y-4">
            <div>
                <p class="text-gray-700 mb-4">
                    Genere un código QR para registrar su asistencia de forma rápida. 
                    El código QR será válido por el tiempo que especifique.
                </p>
                
                <div class="field">
                    <label class="font-semibold block mb-2">Duración del QR</label>
                    <Dropdown
                        v-model="duracionSeleccionada"
                        :options="opcionesDuracion"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Seleccione duración"
                        class="w-full"
                    />
                    <small class="text-gray-500">El QR expirará después de este tiempo</small>
                </div>
            </div>
        </div>

        <div v-else class="space-y-4">
            <div class="text-center">
                <Message severity="success" :closable="false" class="mb-4">
                    <div class="flex flex-col items-center">
                        <i class="pi pi-check-circle text-3xl mb-2"></i>
                        <span class="font-semibold">¡Código QR generado exitosamente!</span>
                    </div>
                </Message>

                <div class="bg-white p-4 rounded-lg border-2 border-gray-200 inline-block">
                    <img :src="qrImage" alt="Código QR" class="w-64 h-64 mx-auto" />
                </div>

                <div class="mt-4 text-left bg-blue-50 p-4 rounded-lg">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-700 font-semibold">Válido por:</span>
                            <Tag :value="`${minutosValidez} minutos`" severity="info" />
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-700 font-semibold">Expira:</span>
                            <span class="text-gray-600 text-sm">{{ formatearFechaExpiracion(expiraEn) }}</span>
                        </div>
                        <div v-if="tiempoRestante > 0" class="flex items-center justify-between">
                            <span class="text-gray-700 font-semibold">Tiempo restante:</span>
                            <Tag :value="tiempoRestanteFormateado" severity="warning" />
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-sm text-gray-600">
                    <i class="pi pi-info-circle mr-1"></i>
                    Escanee este código para registrar su asistencia automáticamente
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-between w-full">
                <Button
                    v-if="qrGenerado"
                    label="Descargar QR"
                    icon="pi pi-download"
                    @click="descargarQR"
                    severity="secondary"
                    outlined
                />
                <div class="flex gap-2 ml-auto">
                    <Button
                        v-if="!qrGenerado"
                        label="Cancelar"
                        icon="pi pi-times"
                        @click="cerrarModal"
                        text
                    />
                    <Button
                        v-if="!qrGenerado"
                        label="Generar QR"
                        icon="pi pi-qrcode"
                        @click="generarQR"
                        :loading="cargando"
                    />
                    <Button
                        v-if="qrGenerado"
                        label="Cerrar"
                        icon="pi pi-times"
                        @click="cerrarModal"
                        text
                    />
                    <Button
                        v-if="qrGenerado"
                        label="Invalidar QR"
                        icon="pi pi-ban"
                        @click="invalidarQR"
                        severity="danger"
                        outlined
                    />
                </div>
            </div>
        </template>
    </Dialog>
</template>

<script setup>
import { ref, computed, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import Message from 'primevue/message';
import Tag from 'primevue/tag';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    idAsignacion: {
        type: Number,
        required: true
    },
    visible: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:visible', 'qr-generado']);

const visible = computed({
    get: () => props.visible,
    set: (value) => emit('update:visible', value)
});

const toast = useToast();
const cargando = ref(false);
const qrGenerado = ref(false);
const qrImage = ref('');
const qrUrl = ref('');
const qrToken = ref('');
const expiraEn = ref('');
const minutosValidez = ref(0);
const tiempoRestante = ref(0);
const duracionSeleccionada = ref(180);

const opcionesDuracion = [
    { label: '30 minutos', value: 30 },
    { label: '1 hora', value: 60 },
    { label: '2 horas', value: 120 },
    { label: '3 horas (recomendado)', value: 180 },
    { label: '4 horas', value: 240 },
    { label: '6 horas', value: 360 },
];

let intervaloActualizacion = null;

const tiempoRestanteFormateado = computed(() => {
    if (tiempoRestante.value <= 0) return 'Expirado';
    
    const horas = Math.floor(tiempoRestante.value / 60);
    const minutos = tiempoRestante.value % 60;
    
    if (horas > 0) {
        return `${horas}h ${minutos}m`;
    }
    return `${minutos}m`;
});

const generarQR = async () => {
    cargando.value = true;
    try {
        const response = await axios.post(route('qr.generar'), {
            id_asignacion: props.idAsignacion,
            duracion_minutos: duracionSeleccionada.value
        });

        if (response.data.success) {
            qrImage.value = response.data.qr_image;
            qrUrl.value = response.data.qr_url;
            qrToken.value = response.data.token;
            expiraEn.value = response.data.expira_en;
            minutosValidez.value = response.data.minutos_validez;
            tiempoRestante.value = response.data.minutos_validez;
            qrGenerado.value = true;

            toast.add({
                severity: 'success',
                summary: 'QR Generado',
                detail: response.data.message,
                life: 3000
            });

            emit('qr-generado', response.data);

            // Iniciar contador regresivo
            iniciarContadorRegresivo();
        }
    } catch (error) {
        console.error('Error al generar QR:', error);
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: error.response?.data?.message || 'Error al generar el código QR',
            life: 3000
        });
    } finally {
        cargando.value = false;
    }
};

const invalidarQR = async () => {
    try {
        const response = await axios.post(route('qr.invalidar'), {
            id_asignacion: props.idAsignacion
        });

        if (response.data.success) {
            toast.add({
                severity: 'success',
                summary: 'QR Invalidado',
                detail: response.data.message,
                life: 3000
            });
            cerrarModal();
        }
    } catch (error) {
        console.error('Error al invalidar QR:', error);
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: error.response?.data?.message || 'Error al invalidar el código QR',
            life: 3000
        });
    }
};

const descargarQR = () => {
    const link = document.createElement('a');
    link.href = qrImage.value;
    link.download = `QR-Asistencia-${Date.now()}.png`;
    link.click();
};

const formatearFechaExpiracion = (fecha) => {
    return new Date(fecha).toLocaleString('es-BO', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const iniciarContadorRegresivo = () => {
    intervaloActualizacion = setInterval(() => {
        if (tiempoRestante.value > 0) {
            tiempoRestante.value--;
        } else {
            detenerContadorRegresivo();
            toast.add({
                severity: 'warn',
                summary: 'QR Expirado',
                detail: 'El código QR ha expirado. Genere uno nuevo si es necesario.',
                life: 5000
            });
        }
    }, 60000); // Actualizar cada minuto
};

const detenerContadorRegresivo = () => {
    if (intervaloActualizacion) {
        clearInterval(intervaloActualizacion);
        intervaloActualizacion = null;
    }
};

const cerrarModal = () => {
    detenerContadorRegresivo();
    qrGenerado.value = false;
    qrImage.value = '';
    qrUrl.value = '';
    qrToken.value = '';
    duracionSeleccionada.value = 180;
    emit('update:visible', false);
};

onUnmounted(() => {
    detenerContadorRegresivo();
});
</script>
