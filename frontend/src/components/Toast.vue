<template>
  <div v-if="msg" :class="['fixed top-3 right-3 px-4 py-2 rounded-lg text-xs transition-transform z-[100]',
    type==='err' ? 'border border-[#ef4444] text-[#ef4444]' : 'border border-[#22c55e] text-[#22c55e]',
    'bg-[#1a1a20]']">{{ msg }}</div>
</template>
<script setup>
defineProps({ msg: String, type: { type: String, default: 'ok' } })
</script>
