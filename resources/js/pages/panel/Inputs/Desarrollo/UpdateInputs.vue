<script setup>
import axios from 'axios';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import { useToast } from 'primevue/usetoast';
import { ref, watch } from 'vue';
import InputNumber from 'primevue/inputnumber';

const props = defineProps({
    visible: Boolean,
    inputId: Number,
});
const emit = defineEmits(['update:visible', 'updated']);

const toast = useToast();
const serverErrors = ref({});
const submitted = ref(false);
const loading = ref(false);

const dialogVisible = ref(props.visible);
watch(
    () => props.visible,
    (val) => (dialogVisible.value = val),
);
watch(dialogVisible, (val) => emit('update:visible', val));

const input = ref({
    name: '',
    price: null,
    quantity: null,
    state: true,
    idAlmacen: null,
    idSupplier: null,
    description: null,
});

const almacens = ref([]);
const suppliers = ref([]);

watch(
    () => props.visible,
    async (val) => {
        if (val && props.inputId) {
            await fetchInput();
            await fetchAlmacens();
            await fetchSuppliers();
        }
    },
);

const fetchInput = async () => {
    loading.value = true;
    try {
        const { data } = await axios.get(`/insumo/${props.inputId}`);
        const i = data.input;
        input.value = {
            name: i.name,
            price: i.price,
            quantity: i.quantity,
            state: i.state,
            idAlmacen: i.idAlmacen,
            idSupplier: i.idSupplier,
            description: i.description,

        };
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudo cargar los insumos',
            life: 3000,
        });
    } finally {
        loading.value = false;
    }
    
};

const fetchAlmacens = async () => {
    try {
        const { data } = await axios.get('/almacen');
        almacens.value = data.data.map((c) => ({ label: c.name, value: c.id }));
    } catch (e) {
        toast.add({ severity: 'warn', summary: 'Advertencia', detail: 'No se pudieron cargar los almacens' });
    }
};

const fetchSuppliers = async () => {
    try {
        const { data } = await axios.get('/proveedor');
        suppliers.value = data.data.map((a) => ({ label: a.name, value: a.id }));
    } catch (e) {
        toast.add({ severity: 'warn', summary: 'Advertencia', detail: 'No se pudieron cargar los proveedores' });
    }
};

const updateInput = async () => {
    submitted.value = true;
    serverErrors.value = {};

    try {
        const dataToSend = {
            name: input.value.name,
price: parseFloat(input.value.price),
            quantity: input.value.quantity,
            state: input.value.state === true,
            idAlmacen: input.value.idAlmacen, 
            idSupplier: input.value.idSupplier,            
            description: input.value.description,

        };
console.log('Datos a enviar:', {
    name: input.value.name,
    price: input.value.price,
    quantity: input.value.quantity,
    state: input.value.state === true,
    idAlmacen: input.value.idAlmacen,
    idSupplier: input.value.idSupplier,
    description: input.value.description,
});
        await axios.put(`/insumo/${props.inputId}`, dataToSend);

        toast.add({
            severity: 'success',
            summary: 'Actualizado',
            detail: 'Insumo actualizado correctamente',
            life: 3000,
        });

        dialogVisible.value = false;
        emit('updated');
    } catch (error) {
        if (error.response?.data?.errors) {
            serverErrors.value = error.response.data.errors;
            toast.add({
                severity: 'error',
                summary: 'Error de validación',
                detail: error.response.data.message || 'Revisa los campos.',
                life: 5000,
            });
        } else {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'No se pudo actualizar el insumo',
                life: 3000,
            });
        }
    }
};
</script>

<template>
    <Dialog v-model:visible="dialogVisible" header="Editar Insumo" modal :closable="true" :closeOnEscape="true" :style="{ width: '700px' }">
        <div class="flex flex-col gap-6">
            <div class="grid grid-cols-12 gap-4">
                <!-- Nombre -->
                <div class="col-span-10">
                    <label class="mb-2 block font-bold">Nombre <span class="text-red-500">*</span></label>
                    <InputText v-model="input.name" required maxlength="100" fluid :class="{ 'p-invalid': serverErrors.name }" />
                    <small v-if="serverErrors.name" class="p-error">{{ serverErrors.name[0] }}</small>
                </div>

                <!-- Estado -->
                <div class="col-span-2">
                    <label class="mb-2 block font-bold">Estado <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-3">
                        <Checkbox v-model="input.state" :binary="true" fluid />
                        <Tag :value="input.state ? 'Activo' : 'Inactivo'" :severity="input.state ? 'success' : 'danger'" />
                    </div>
                </div>
                <!-- precio -->

 <div class="col-span-6">
                    <label for="price" class="block font-bold mb-2">Precio <span class="text-red-500">*</span></label>
                    <InputNumber
                        id="price"
                        v-model="input.price"
                        :minFractionDigits="2"
                        :maxFractionDigits="2"
                        mode="currency"
                        currency="USD"
                        locale="en-US"
                        class="w-full"
                        :class="{ 'p-invalid': serverErrors.price }"
                    />
                    <small v-if="serverErrors.price" class="p-error">{{ serverErrors.price[0] }}</small>
                </div>

                <!-- Cantidad -->
                <div class="col-span-6">
                    <label class="mb-2 block font-bold">Cantidad <span class="text-red-500">*</span></label>
                    <InputText v-model.number="input.quantity" type="number" fluid min="1" />
                    <small v-if="submitted && !input.quantity" class="text-red-500">La cantidad es obligatoria.</small>
                    <small v-else-if="input.quantity < 1" class="text-red-500">Debe ser al menos 1.</small>
                    <small v-else-if="serverErrors.quantity" class="text-red-500">{{ serverErrors.quantity[0] }}</small>
                </div>

                <!-- Almacen -->
                <div class="col-span-6">
                    <label class="mb-2 block font-bold">Almacen <span class="text-red-500">*</span></label>
                    <Select
                        v-model="input.idAlmacen"
                        fluid
                        :options="almacens"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Seleccione Almacen"
                    />
                    <small v-if="submitted && !input.idAlmacen" class="text-red-500">El Almacen es obligatorio.</small>
                    <small v-else-if="serverErrors.idAlmacen" class="text-red-500">{{ serverErrors.idAlmacen[0] }}</small>
                </div>

                <!-- Proveedor -->
                <div class="col-span-6">
                    <label class="mb-2 block font-bold">Proveedor <span class="text-red-500">*</span></label>
                    <Select
                        v-model="input.idSupplier"
                        :options="suppliers"
                        fluid
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Seleccione Proveedor"
                    />
                    <small v-if="submitted && !input.idSupplier" class="text-red-500">El Proveedor es obligatorio.</small>
                    <small v-else-if="serverErrors.idSupplier" class="text-red-500">{{ serverErrors.idSupplier[0] }}</small>
                </div>

                <!-- description -->
                <div class="col-span-12">
                    <label class="mb-2 block font-bold">Descripcion <span class="text-red-500">*</span></label>
                    <InputText v-model="input.description" required maxlength="150" fluid :class="{ 'p-invalid': serverErrors.description }" />
                    <small v-if="serverErrors.description" class="p-error">{{ serverErrors.description[0] }}</small>
                </div>
            </div>
        </div>

        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="dialogVisible = false" />
            <Button label="Guardar" icon="pi pi-check" @click="updateInput" :loading="loading" />
        </template>
    </Dialog>
</template>
