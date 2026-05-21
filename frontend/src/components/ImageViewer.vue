<template>
  <div class="fixed inset-0 bg-black/85 z-[9999] flex items-center justify-center cursor-grab" @click.self="$emit('close')">
    <div class="relative max-w-[90vw] max-h-[90vh] flex gap-4">
      <img :src="img.url||img.thumb" class="max-w-[70vw] max-h-[90vh] object-contain" />
      <div class="bg-[#1a1a20] border border-[#2a2a32] rounded-lg p-3 text-xs text-[#aaa] max-w-[300px] overflow-y-auto">
        <div class="font-semibold mb-2">图片信息</div>
        <div class="text-[11px] space-y-1">
          <div v-if="img.info">{{ img.info }}</div>
          <div>URL: {{ img.url?.substring(0,40) }}...</div>
        </div>
        <div class="flex gap-1 mt-2">
          <button @click="download" class="px-2 py-1 bg-[#25252d] border border-[#333] rounded text-[10px] hover:bg-[#333]">⬇️ 下载</button>
        </div>
      </div>
    </div>
    <button @click="$emit('close')" class="absolute top-3 right-3 w-7 h-7 bg-[#25252d] border border-[#333] rounded-full text-[#aaa] text-sm flex items-center justify-center">✕</button>
  </div>
</template>
<script setup>
defineProps({ img: Object })
defineEmits(['close'])
function download() { window.open(this.img?.url, '_blank') }
</script>
