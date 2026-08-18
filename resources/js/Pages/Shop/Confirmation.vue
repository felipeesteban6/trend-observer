<script setup>
import { Head, Link } from '@inertiajs/vue3'
import ShopLayout from '@/Layouts/ShopLayout.vue'

const props = defineProps({
  order: Object,
})

const statusLabels = {
  pending_payment: 'Esperando confirmación de pago',
  paid: 'Pago confirmado',
  payment_failed: 'El pago falló',
  submitted_to_supplier: 'Pago confirmado — pedido enviado al proveedor',
  submission_failed: 'Pago confirmado — hubo un problema al enviar el pedido, lo estamos revisando',
  cancelled: 'Pedido cancelado',
}
</script>

<template>
  <Head title="Pedido" />
  <ShopLayout>
    <div class="mx-auto max-w-xl rounded-lg border border-slate-200 bg-white p-6 text-center">
      <h1 class="text-xl font-semibold text-slate-900">¡Gracias por tu compra!</h1>
      <p class="mt-2 text-sm text-slate-500">Pedido #{{ order.order_number.slice(0, 8) }}</p>
      <p class="mt-4 inline-block rounded-full bg-slate-100 px-4 py-1 text-sm font-medium text-slate-700">
        {{ statusLabels[order.status] ?? order.status }}
      </p>

      <div class="mt-6 space-y-2 text-left">
        <div v-for="item in order.items" :key="item.id" class="flex justify-between text-sm">
          <span class="text-slate-600">{{ item.quantity }}× {{ item.product_name }}</span>
          <span class="text-slate-800">{{ order.currency }} {{ Number(item.subtotal).toFixed(2) }}</span>
        </div>
        <div class="flex justify-between border-t border-slate-200 pt-2 text-sm font-semibold">
          <span>Total</span>
          <span>{{ order.currency }} {{ Number(order.total).toFixed(2) }}</span>
        </div>
      </div>

      <Link href="/tienda" class="mt-6 inline-block text-sm text-indigo-600 hover:underline">Volver al catálogo</Link>
    </div>
  </ShopLayout>
</template>
