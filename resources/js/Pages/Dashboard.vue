<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
  ranking: Array,
  date: String,
  availableDates: Array,
})

function changeDate(e) {
  router.get('/dashboard', { date: e.target.value }, { preserveState: true })
}

function label(row) {
  return row.keyword?.term ?? row.supplier_product?.name ?? row.label
}

function source(row) {
  return row.search_keyword_id ? 'Búsqueda (Google Trends)' : 'Proveedor (CJ Dropshipping)'
}
</script>

<template>
  <Head title="Dashboard" />
  <AppLayout>
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-xl font-semibold">Ranking de productos con más señal</h1>
        <p class="text-sm text-slate-500">Combina crecimiento de búsquedas (Google Trends) y ventas/ranking del proveedor.</p>
      </div>
      <select v-if="availableDates?.length" class="rounded-md border-slate-300 text-sm" :value="date" @change="changeDate">
        <option v-for="d in availableDates" :key="d" :value="d">{{ d }}</option>
      </select>
    </div>

    <div v-if="!ranking?.length" class="rounded-lg border border-dashed border-slate-300 p-8 text-center text-slate-500">
      Todavía no hay datos calculados. Agregá keywords en la sección "Keywords" y esperá a que corra el job diario
      (o disparalo manualmente con <code>php artisan schedule:run</code> en el servidor).
    </div>

    <div v-else class="overflow-hidden rounded-lg border border-slate-200 bg-white">
      <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
          <tr>
            <th class="px-4 py-3">#</th>
            <th class="px-4 py-3">Producto / término</th>
            <th class="px-4 py-3">Fuente</th>
            <th class="px-4 py-3">Score</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="(row, i) in ranking" :key="row.id">
            <td class="px-4 py-3 text-slate-400">{{ i + 1 }}</td>
            <td class="px-4 py-3 font-medium">{{ label(row) }}</td>
            <td class="px-4 py-3 text-slate-500">{{ source(row) }}</td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <div class="h-2 w-24 overflow-hidden rounded-full bg-slate-100">
                  <div class="h-full rounded-full bg-indigo-500" :style="{ width: Math.min(100, row.total_score) + '%' }" />
                </div>
                <span class="tabular-nums text-slate-700">{{ row.total_score }}</span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>
