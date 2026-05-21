<template>
  <div class="bg-[#1a1a20] border border-[#2a2a32] rounded-lg p-3">
    <div class="flex items-center gap-2 mb-2">
      <span class="text-[11px] font-semibold text-[#aaa]">📜 生成历史</span>
      <button @click="selMode=!selMode" class="ml-auto px-2 py-0.5 bg-[#25252d] border border-[#333] rounded text-[10px] text-[#aaa]">☐ 多选</button>
    </div>
    <div class="grid gap-1.5 max-h-[400px] overflow-y-auto" :class="selMode ? 'grid-cols-[repeat(auto-fill,minmax(80px,1fr))]' : 'grid-cols-[repeat(auto-fill,minmax(80px,1fr))]'">
      <div v-for="h in items" :key="h.loadId" @click="selMode ? toggleSel(h.loadId) : open(h)"
        :class="['aspect-[9/16] rounded overflow-hidden cursor-pointer border-2 relative', selMode && sels.has(h.loadId) ? 'border-[#667eea]' : 'border-transparent hover:border-[#667eea]']">
        <div v-if="h.loading" class="w-full h-full bg-[#0a0a0e] flex items-center justify-center">
          <div class="w-5 h-5 border-2 border-[#667eea] border-t-transparent rounded-full animate-spin"></div>
        </div>
        <img v-else-if="h.url" :src="h.url" class="w-full h-full object-contain bg-[#0a0a0e]" />
        <div v-else class="w-full h-full bg-[#0a0a0e] flex items-center justify-center text-[10px] text-[#e74c3c] p-1 text-center">{{ h.error }}</div>
        <div v-if="selMode" :class="['absolute top-1 right-1 w-4 h-4 rounded-full border-2 flex items-center justify-center text-[10px] text-white z-10',
          sels.has(h.loadId) ? 'bg-[#667eea] border-[#667eea]' : 'bg-[#1a1a20cc] border-[#444]']">
          {{ sels.has(h.loadId) ? '✓' : '' }}
        </div>
      </div>
    </div>
    <div v-if="items.length===0" class="text-[10px] text-[#888] text-center py-4">暂无</div>
  </div>
</template>
<script setup>
import { ref } from 'vue'
const props = defineProps({ items: Array, loading: Boolean, loadId: String })
const emit = defineEmits(['select'])
const selMode = ref(false)
const sels = ref(new Set())

function toggleSel(id) {
  const s = new Set(sels.value)
  if (s.has(id)) s.delete(id); else s.add(id); sels.value = s
}
function open(h) {
  if (!h.loading && h.url) emit('select', h)
}
</script>
