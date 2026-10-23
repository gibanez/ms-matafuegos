<script setup>
import { computed, ref, watch } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Tag from 'primevue/tag';
import { useConfirm } from 'primevue/useconfirm';
import { useToast } from 'primevue/usetoast';
import { api } from '../api';
import { pushNotification } from '../store';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    product: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['update:visible', 'changed']);
const toast = useToast();
const confirm = useConfirm();

const media = ref([]);
const loading = ref(false);
const uploading = ref(false);
const fileInput = ref(null);

const title = computed(() => (props.product ? `Multimedia · ${props.product.name}` : 'Multimedia'));

async function loadMedia() {
    if (!props.product) {
        media.value = [];
        return;
    }

    loading.value = true;

    try {
        const response = await api(`/api/products/${props.product.id}/media`);
        media.value = response.media || [];
    } catch (exception) {
        toast.add({
            severity: 'error',
            summary: 'No se pudo cargar la multimedia',
            detail: exception.message,
            life: 4500,
        });
    } finally {
        loading.value = false;
    }
}

function pickFile() {
    fileInput.value?.click();
}

async function upload(event) {
    const file = event.target.files?.[0];

    if (!file || !props.product) {
        return;
    }

    if (file.size > 10 * 1024 * 1024) {
        toast.add({
            severity: 'error',
            summary: 'Archivo demasiado grande',
            detail: 'El límite es de 10 MB.',
            life: 4500,
        });
        event.target.value = '';
        return;
    }

    const formData = new FormData();
    formData.append('file', file);
    uploading.value = true;

    try {
        await api(`/api/products/${props.product.id}/media`, {
            method: 'POST',
            body: formData,
        });

        toast.add({
            severity: 'success',
            summary: 'Archivo subido',
            detail: file.name,
            life: 3200,
        });
        pushNotification({
            title: 'Multimedia agregada',
            detail: `${props.product.name}: ${file.name}`,
            type: 'success',
        });

        await loadMedia();
        emit('changed');
    } catch (exception) {
        toast.add({
            severity: 'error',
            summary: 'No se pudo subir',
            detail: exception.message,
            life: 5000,
        });
    } finally {
        uploading.value = false;
        event.target.value = '';
    }
}

function confirmDelete(item) {
    if (!props.product) {
        return;
    }

    confirm.require({
        header: 'Eliminar archivo',
        message: `¿Eliminar "${item.originalFilename}"? El archivo se borra del servidor.`,
        icon: 'pi pi-exclamation-triangle',
        rejectLabel: 'Cancelar',
        acceptLabel: 'Eliminar',
        acceptClass: 'p-button-danger',
        accept: async () => {
            try {
                await api(`/api/products/${props.product.id}/media/${item.id}`, { method: 'DELETE' });
                toast.add({
                    severity: 'success',
                    summary: 'Archivo eliminado',
                    detail: item.originalFilename,
                    life: 3200,
                });
                pushNotification({
                    title: 'Multimedia eliminada',
                    detail: `${props.product.name}: ${item.originalFilename}`,
                    type: 'warning',
                });
                await loadMedia();
                emit('changed');
            } catch (exception) {
                toast.add({
                    severity: 'error',
                    summary: 'No se pudo eliminar',
                    detail: exception.message,
                    life: 5000,
                });
            }
        },
    });
}

watch(
    () => props.visible,
    (visible) => {
        if (visible) {
            loadMedia();
        }
    },
    { immediate: true },
);
</script>

<template>
    <Dialog
        :visible="props.visible"
        modal
        :draggable="false"
        :header="title"
        :style="{ width: '860px' }"
        :breakpoints="{ '960px': '92vw', '640px': '96vw' }"
        @update:visible="emit('update:visible', $event)"
    >
        <div class="media-manager">
            <div class="media-upload">
                <div class="media-upload-icon"><i class="pi pi-cloud-upload"></i></div>
                <div class="media-upload-copy">
                    <strong>Agregar imagen o video</strong>
                    <span>JPEG, PNG, WebP, GIF, MP4, WebM o MOV. Máximo 10 MB.</span>
                </div>
                <input
                    ref="fileInput"
                    type="file"
                    class="media-file-input"
                    accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/quicktime"
                    @change="upload"
                >
                <Button
                    label="Subir archivo"
                    icon="pi pi-upload"
                    :loading="uploading"
                    @click="pickFile"
                />
            </div>

            <div v-if="loading" class="media-loading">
                <i class="pi pi-spin pi-spinner"></i>
                <span>Cargando multimedia…</span>
            </div>

            <div v-else-if="media.length" class="media-grid">
                <article v-for="item in media" :key="item.id" class="media-card">
                    <div class="media-preview">
                        <img
                            v-if="item.type === 'image'"
                            :src="item.url || `/${item.path}`"
                            :alt="item.originalFilename"
                            loading="lazy"
                        >
                        <video
                            v-else
                            :src="item.url || `/${item.path}`"
                            controls
                            preload="metadata"
                        ></video>
                    </div>
                    <div class="media-card-body">
                        <div class="media-card-title">
                            <strong :title="item.originalFilename">{{ item.originalFilename }}</strong>
                            <Tag
                                :value="item.type === 'image' ? 'Imagen' : 'Video'"
                                :severity="item.type === 'image' ? 'info' : 'warn'"
                            />
                        </div>
                        <div class="media-card-actions">
                            <small>Posición {{ item.position }}</small>
                            <Button
                                icon="pi pi-trash"
                                text
                                rounded
                                size="small"
                                severity="danger"
                                aria-label="Eliminar archivo"
                                v-tooltip.top="'Eliminar archivo'"
                                @click="confirmDelete(item)"
                            />
                        </div>
                    </div>
                </article>
            </div>

            <div v-else class="table-empty media-empty">
                <i class="pi pi-images"></i>
                <strong>Sin multimedia</strong>
                <span>Subí la primera imagen o video del producto.</span>
            </div>
        </div>

        <template #footer>
            <Button label="Cerrar" text severity="secondary" @click="emit('update:visible', false)" />
        </template>
    </Dialog>
</template>
