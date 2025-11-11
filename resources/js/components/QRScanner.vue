<template>
    <Dialog 
        v-model:visible="visible" 
        modal 
        header="Escanear Código QR"
        :style="{ width: '600px' }"
        @hide="cerrarModal"
    >
        <div class="space-y-4">
            <div v-if="!escaneando && !resultado">
                <Message severity="info" :closable="false" class="mb-4">
                    <div class="flex items-start">
                        <i class="pi pi-info-circle text-xl mr-2"></i>
                        <div>
                            <p class="font-semibold mb-1">Instrucciones:</p>
                            <ul class="text-sm space-y-1 ml-4 list-disc">
                                <li>Permita el acceso a la cámara cuando se solicite</li>
                                <li>Apunte la cámara hacia el código QR</li>
                                <li>Mantenga el código dentro del marco</li>
                                <li>El escaneo es automático</li>
                            </ul>
                        </div>
                    </div>
                </Message>
            </div>

            <!-- Visor de cámara -->
            <div v-if="escaneando" class="relative">
                <div id="qr-reader" class="w-full rounded-lg overflow-hidden"></div>
                <div class="mt-2 text-center text-sm text-gray-600">
                    <i class="pi pi-spin pi-spinner mr-1"></i>
                    Buscando código QR...
                </div>
            </div>

            <!-- Resultado del escaneo -->
            <div v-if="resultado" class="space-y-4">
                <Message 
                    :severity="resultado.success ? 'success' : 'error'" 
                    :closable="false"
                >
                    <div class="flex flex-col items-center text-center">
                        <i 
                            :class="resultado.success ? 'pi pi-check-circle' : 'pi pi-times-circle'" 
                            class="text-3xl mb-2"
                        ></i>
                        <span class="font-semibold text-lg">{{ resultado.message }}</span>
                    </div>
                </Message>

                <div v-if="resultado.success && resultado.clase" class="bg-green-50 p-4 rounded-lg border border-green-200">
                    <h4 class="font-semibold text-green-800 mb-3 flex items-center">
                        <i class="pi pi-check mr-2"></i>
                        Asistencia Registrada
                    </h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Materia:</span>
                            <span class="font-semibold">{{ resultado.clase.materia }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Grupo:</span>
                            <span class="font-semibold">{{ resultado.clase.grupo }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Día:</span>
                            <span class="font-semibold">{{ resultado.clase.dia }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Hora:</span>
                            <span class="font-semibold">{{ resultado.clase.hora }}</span>
                        </div>
                    </div>
                </div>

                <div v-else-if="!resultado.success" class="bg-red-50 p-4 rounded-lg border border-red-200">
                    <p class="text-red-800 text-sm">{{ resultado.message }}</p>
                    <div v-if="resultado.codigo_error" class="mt-2 text-xs text-gray-600">
                        Código de error: {{ resultado.codigo_error }}
                    </div>
                </div>
            </div>

            <!-- Error de cámara -->
            <div v-if="errorCamara" class="space-y-4">
                <Message severity="error" :closable="false">
                    <div class="flex items-start">
                        <i class="pi pi-exclamation-triangle text-xl mr-2"></i>
                        <div>
                            <p class="font-semibold mb-1">Error de Cámara</p>
                            <p class="text-sm">{{ errorCamara }}</p>
                        </div>
                    </div>
                </Message>
                <div class="text-sm text-gray-600">
                    <p class="font-semibold mb-2">Posibles soluciones:</p>
                    <ul class="list-disc ml-5 space-y-1">
                        <li>Verifique que su navegador tenga permisos para acceder a la cámara</li>
                        <li>Asegúrese de que ninguna otra aplicación esté usando la cámara</li>
                        <li>Intente recargar la página</li>
                    </ul>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end gap-2">
                <Button
                    v-if="!escaneando && !resultado"
                    label="Cancelar"
                    icon="pi pi-times"
                    @click="cerrarModal"
                    text
                />
                <Button
                    v-if="!escaneando && !resultado"
                    label="Iniciar Escaneo"
                    icon="pi pi-camera"
                    @click="iniciarEscaneo"
                    :loading="cargando"
                />
                <Button
                    v-if="escaneando"
                    label="Detener"
                    icon="pi pi-stop"
                    @click="detenerEscaneo"
                    severity="danger"
                />
                <Button
                    v-if="resultado"
                    label="Escanear Otro"
                    icon="pi pi-refresh"
                    @click="reiniciarEscaneo"
                    outlined
                />
                <Button
                    v-if="resultado"
                    label="Cerrar"
                    icon="pi pi-times"
                    @click="cerrarModal"
                />
            </div>
        </template>
    </Dialog>
</template>

<script setup>
import { ref, computed, onUnmounted } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import Message from 'primevue/message';
import { Html5Qrcode } from 'html5-qrcode';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:visible', 'asistencia-registrada']);

const visible = computed({
    get: () => props.visible,
    set: (value) => emit('update:visible', value)
});

const toast = useToast();
const cargando = ref(false);
const escaneando = ref(false);
const resultado = ref(null);
const errorCamara = ref('');

let html5QrCode = null;

const iniciarEscaneo = async () => {
    cargando.value = true;
    errorCamara.value = '';
    resultado.value = null;

    try {
        html5QrCode = new Html5Qrcode("qr-reader");
        
        const config = {
            fps: 10,
            qrbox: { width: 250, height: 250 },
            aspectRatio: 1.0
        };

        await html5QrCode.start(
            { facingMode: "environment" }, // Cámara trasera en móviles
            config,
            onScanSuccess,
            onScanFailure
        );

        escaneando.value = true;
    } catch (error) {
        console.error('Error al iniciar escáner:', error);
        errorCamara.value = 'No se pudo acceder a la cámara. Verifique los permisos.';
    } finally {
        cargando.value = false;
    }
};

const onScanSuccess = async (decodedText, decodedResult) => {
    console.log('QR escaneado:', decodedText);
    
    // Detener el escaneo
    await detenerEscaneo();

    // Extraer el token del URL
    try {
        const url = new URL(decodedText);
        const token = url.pathname.split('/').pop();
        
        // Verificar y registrar asistencia
        await verificarQR(token);
    } catch (error) {
        console.error('Error al procesar QR:', error);
        resultado.value = {
            success: false,
            message: 'Código QR inválido o mal formado',
            codigo_error: 'INVALID_QR_FORMAT'
        };
    }
};

const onScanFailure = (error) => {
    // No hacer nada, es normal que falle mientras busca el QR
};

const verificarQR = async (token) => {
    try {
        const response = await axios.post(route('qr.verificar', { token }));
        
        if (response.data.success) {
            resultado.value = {
                success: true,
                message: response.data.message,
                clase: response.data.clase,
                asistencia: response.data.asistencia
            };

            toast.add({
                severity: 'success',
                summary: 'Asistencia Registrada',
                detail: response.data.message,
                life: 5000
            });

            emit('asistencia-registrada', response.data);
        } else {
            resultado.value = {
                success: false,
                message: response.data.message
            };

            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: response.data.message,
                life: 5000
            });
        }
    } catch (error) {
        console.error('Error al verificar QR:', error);
        
        let mensaje = 'Error al procesar el código QR';
        let codigoError = 'UNKNOWN_ERROR';

        if (error.response?.status === 404) {
            mensaje = 'Código QR inválido';
            codigoError = 'INVALID_TOKEN';
        } else if (error.response?.status === 410) {
            mensaje = 'El código QR ha expirado';
            codigoError = 'EXPIRED_QR';
        } else if (error.response?.status === 403) {
            mensaje = 'No tiene permiso para usar este código QR';
            codigoError = 'FORBIDDEN';
        }

        resultado.value = {
            success: false,
            message: error.response?.data?.message || mensaje,
            codigo_error: codigoError
        };

        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: mensaje,
            life: 5000
        });
    }
};

const detenerEscaneo = async () => {
    if (html5QrCode && escaneando.value) {
        try {
            await html5QrCode.stop();
            html5QrCode.clear();
        } catch (error) {
            console.error('Error al detener escáner:', error);
        }
    }
    escaneando.value = false;
};

const reiniciarEscaneo = () => {
    resultado.value = null;
    errorCamara.value = '';
    iniciarEscaneo();
};

const cerrarModal = async () => {
    await detenerEscaneo();
    resultado.value = null;
    errorCamara.value = '';
    emit('update:visible', false);
};

onUnmounted(async () => {
    await detenerEscaneo();
});
</script>

<style scoped>
#qr-reader {
    border: 2px solid #e5e7eb;
}

:deep(#qr-reader__dashboard_section_swaplink) {
    display: none !important;
}
</style>
