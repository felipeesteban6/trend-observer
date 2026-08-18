<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import ShopLayout from '@/Layouts/ShopLayout.vue'

const props = defineProps({
  product: Object,
})

const form = useForm({
  product_id: props.product.id,
  quantity: 1,
})

function addToCart() {
  form.post('/tienda/carrito', { preserveScroll: true })
}
</script>

<template>
  <Head :title="product.name" />
  <ShopLayout>
    <div class="grid gap-8 md:grid-cols-2">
      <div class="aspect-square overflow-hidden rounded-lg bg-slate-100">
        <img v-if="product.image_url" :src="product.image_url" :alt="product.name" class="h-full w-full object-cover" />
      </div>

      <div>
        <h1 class="text-xl font-semibold text-slate-900">{{ product.name }}</h1>
        <p class="mt-2 text-2xl font-bold text-indigo-600">
          {{ product.currency }} {{ Number(product.sale_price).toFixed(2) }}
        </p>
        <p v-if="product.description" class="mt-4 whitespace-pre-line text-sm text-slate-600">
          {{ product.description }}
        </p>

        <form @submit.prevent="addToCart" class="mt-6 flex items-center gap-3">
          <input
            v-model.number="form.quantity"
            type="number"
            min="1"
            max="20"
            class="w-20 rounded-md border-slate-300 text-sm"
          />
          <button
            type="submit"
            :disabled="form.processing"
            class="rounded-md bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
          >
            Agregar al carrito
          </button>
        </form>
      </div>
    </div>
  </ShopLayout>
</template>
