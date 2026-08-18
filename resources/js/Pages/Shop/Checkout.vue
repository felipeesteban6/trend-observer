<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import ShopLayout from '@/Layouts/ShopLayout.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'

defineProps({
  items: Array,
  subtotal: Number,
})

const form = useForm({
  customer_name: '',
  customer_email: '',
  customer_phone: '',
  street: '',
  city: '',
  region: '',
  postal_code: '',
  country_code: '',
})

function submit() {
  form.post('/tienda/checkout')
}
</script>

<template>
  <Head title="Checkout" />
  <ShopLayout>
    <h1 class="mb-6 text-xl font-semibold">Datos de envío</h1>

    <div class="grid gap-8 md:grid-cols-3">
      <form @submit.prevent="submit" class="space-y-4 md:col-span-2">
        <div>
          <InputLabel for="customer_name" value="Nombre completo" />
          <TextInput id="customer_name" v-model="form.customer_name" class="mt-1 block w-full" required />
          <InputError :message="form.errors.customer_name" class="mt-1" />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
          <div>
            <InputLabel for="customer_email" value="Email" />
            <TextInput id="customer_email" type="email" v-model="form.customer_email" class="mt-1 block w-full" required />
            <InputError :message="form.errors.customer_email" class="mt-1" />
          </div>
          <div>
            <InputLabel for="customer_phone" value="Teléfono" />
            <TextInput id="customer_phone" v-model="form.customer_phone" class="mt-1 block w-full" />
            <InputError :message="form.errors.customer_phone" class="mt-1" />
          </div>
        </div>

        <div>
          <InputLabel for="street" value="Dirección" />
          <TextInput id="street" v-model="form.street" class="mt-1 block w-full" required />
          <InputError :message="form.errors.street" class="mt-1" />
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
          <div>
            <InputLabel for="city" value="Ciudad" />
            <TextInput id="city" v-model="form.city" class="mt-1 block w-full" required />
            <InputError :message="form.errors.city" class="mt-1" />
          </div>
          <div>
            <InputLabel for="region" value="Región / Estado" />
            <TextInput id="region" v-model="form.region" class="mt-1 block w-full" required />
            <InputError :message="form.errors.region" class="mt-1" />
          </div>
          <div>
            <InputLabel for="postal_code" value="Código postal" />
            <TextInput id="postal_code" v-model="form.postal_code" class="mt-1 block w-full" />
            <InputError :message="form.errors.postal_code" class="mt-1" />
          </div>
        </div>

        <div class="max-w-[8rem]">
          <InputLabel for="country_code" value="País (ISO-2)" />
          <TextInput id="country_code" v-model="form.country_code" maxlength="2" placeholder="CL" class="mt-1 block w-full uppercase" required />
          <InputError :message="form.errors.country_code" class="mt-1" />
        </div>

        <PrimaryButton :disabled="form.processing" class="w-full justify-center py-3">
          Pagar con Mercado Pago
        </PrimaryButton>
      </form>

      <div class="rounded-lg border border-slate-200 bg-white p-4">
        <p class="mb-3 text-sm font-medium text-slate-700">Resumen</p>
        <div v-for="item in items" :key="item.product.id" class="flex justify-between py-1 text-sm">
          <span class="text-slate-600">{{ item.quantity }}× {{ item.product.name }}</span>
          <span class="text-slate-800">{{ Number(item.subtotal).toFixed(2) }}</span>
        </div>
        <div class="mt-3 flex justify-between border-t border-slate-200 pt-3 text-sm font-semibold">
          <span>Total</span>
          <span>{{ items[0]?.product.currency }} {{ Number(subtotal).toFixed(2) }}</span>
        </div>
      </div>
    </div>
  </ShopLayout>
</template>
