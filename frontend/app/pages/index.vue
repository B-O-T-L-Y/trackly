<script setup lang="ts">
import type {EventType} from '~/types/events'
import type {Stats} from "~/types/stats";

const {sendEvent, sending, error: eventError} = useAnalytics()
const config = useRuntimeConfig()

const {
  data: statsResponse,
  pending: statsPending,
  error: statsFetchError,
  refresh: refreshStats,
} = await useApiFetch<{ data: Stats }>('/v1/stats/today')

const stats = computed<Stats>(() => statsResponse.value?.data ?? {
  date: '',
  counts: {
    page_view: 0,
    cta_click: 0,
    form_submit: 0,
  },
  total: 0,
})

const buttonsDisabled = computed(() => statsPending.value || sending.value)

const statsError = computed<string | null>(() => {
  const err = statsFetchError.value as any
  if (!err) return null
  return err?.data?.message || err?.message || 'Failed to fetch stats'
})

let intervalId: ReturnType<typeof setInterval> | null = null

const handleClick = async (type: EventType) => {
  await sendEvent(type)
  await refreshStats()
}

onMounted(() => {
  const intervalMs = Number(config.public.statsPollInterval) * 1000
  intervalId = setInterval(() => {
    refreshStats()
  }, intervalMs)
})

onBeforeUnmount(() => {
  if (intervalId) clearInterval(intervalId)
})

useHead({
  title: 'Dashboard',
})
</script>

<template>
  <main>
    <section>
      <h1>Trackly - Event Tracking Demo</h1>
      <p>
        Press one of the buttons to send an event and view today's statistics.
      </p>
      <div>
        <button :disabled="buttonsDisabled" @click="handleClick('page_view')">
          Page View
        </button>
        <button :disabled="buttonsDisabled" @click="handleClick('cta_click')">
          CTA Click
        </button>
        <button :disabled="buttonsDisabled" @click="handleClick('form_submit')">
          Form Submit
        </button>
      </div>

      <p v-if="statsPending">Loading stats…</p>
      <p v-if="sending && !statsPending">Sending event…</p>
      <p v-if="eventError">{{ eventError }}</p>
      <p v-if="statsError">{{ statsError }}</p>

      <section>
        <h2>Today Stats</h2>
        <p>Date: {{ stats.date || '-' }}</p>
        <ul>
          <li>Page View: {{ stats.counts.page_view }}</li>
          <li>CTA Click: {{ stats.counts.cta_click }}</li>
          <li>Form Submit: {{ stats.counts.form_submit }}</li>
        </ul>
        <p>Total: {{ stats.total }}</p>
      </section>
      <p>
        Polling every {{ config.public.statsPollInterval }} seconds.
      </p>
    </section>
  </main>
</template>

<style scoped></style>
