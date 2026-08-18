<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import ShopLayout from '@/Layouts/ShopLayout.vue'

defineProps({
  items: Array,
  subtotal: Number,
})

function updateQuantity(productId, quantity) {
  router.patch(`/tienda/carrito/${productId}`, { quantity }, { preserveScroll: true })
}

function removeItem(productId) {
  router.delete(`/tienda/carrito/${productId}`, { preserveScroll: true })
}
</script>

<template>
  <Head title="Carrito" />
  <ShopLayout>
    <h1 class="mb-6 text-xl font-semibold">Tu carrito</h1>

    <div v-if="!items.length" class="rounded-lg border border-dashed border-slate-300 p-8 text-center text-slate-500">
      Tu carrito está vacío.
      <Link href="/tienda" class="text-indigo-600 hover:underline">Ver catálogo</Link>
    </div>

    <div v-else class="space-y-4">
      <div
        v-for="item in items"
        :key="item.product.id"
        class="flex items-center gap-4 rounded-lg border border-slate-200 bg-white p-4"
      >
        <div class="h-16 w-16 shrink-0 overflow-hidden rounded bg-slate-100">
          <img v-if="item.product.image_url" :src="item.product.image_url" :alt="item.product.name" class="h-full w-full object-cover" />
        </div>
        <div class="flex-1">
          <p class="text-sm font-medium text-slate-800">{{ item.product.name }}</p>
          <p class="text-sm text-slate-500">{{ item.product.currency }} {{ Number(item.product.sale_price).toFixed(2) }}</p>
        </div>
        <input
          type="number"
          min="1"
          max="20"
          :value="item.quantity"
          @change="updateQuantity(item.product.id, Number($event.target.value))"
          class="w-16 rounded-md border-slate-300 text-sm"
        />
        <p class="w-24 text-right text-sm font-semibold text-slate-800">
          {{ item.product.currency }} {{ Number(item.subtotal).toFixed(2) }}
        </p>
        <button type="button" @click="removeItem(item.product.id)" class="text-sm text-red-500 hover:text-red-700">
          Quitar
        </button>
      </div>

      <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-white p-4">
        <p class="text-sm text-slate-500">Subtotal</p>
        <p class="text-lg font-semibold text-slate-900">{{ items[0].product.currency }} {{ Number(subtotal).toFixed(2) }}</p>
      </div>

      <Link
        href="/tienda/checkout"
        class="block w-full rounded-md bg-indigo-600 px-5 py-3 text-center text-sm font-medium text-white hover:bg-indigo-700"
      >
        Ir a pagar
      </Link>
    </div>
  </ShopLayout>
</template>
