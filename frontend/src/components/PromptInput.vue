<template>
  <div class="bg-[#1a1a20] border border-[#2a2a32] rounded-lg p-3">
    <div class="text-[11px] font-semibold text-[#aaa] mb-2">📝 提示词</div>
    <textarea v-model="text" rows="3" placeholder="描述你想要生成的画面内容..." 
      class="w-full bg-[#25252d] border border-[#333] rounded-md px-2 py-1.5 text-xs text-[#e8e8ed] resize-y focus:border-[#667eea] outline-none"></textarea>
    <div class="mt-2 text-[10px] text-[#999] font-mono whitespace-pre-wrap max-h-[100px] overflow-y-auto bg-[#0a0a0e] rounded p-1.5">{{ preview }}</div>
    <div class="flex gap-1 mt-1.5 flex-wrap items-center">
      <button @click="copy" class="px-2 py-1 bg-[#25252d] border border-[#333] rounded text-[10px] text-[#aaa] hover:bg-[#333]">📋 复制</button>
      <button @click="download" class="px-2 py-1 bg-[#25252d] border border-[#333] rounded text-[10px] text-[#aaa] hover:bg-[#333]">⬇️ 下载</button>
      <select v-model="lang" class="bg-[#25252d] border border-[#333] rounded px-2 py-1 text-[10px] text-[#aaa]">
        <option>🇨🇳 中文</option><option>🇬🇧 English</option><option>🇯🇵 日本語</option><option>🇰🇷 한국어</option><option>🇫🇷 Français</option><option>🇪🇸 Español</option>
      </select>
      <span class="text-[10px] text-[#555]">{{ text.length }}</span>
      <button @click="$emit('gen')" :disabled="loading" class="ml-auto px-4 py-2 bg-gradient-to-r from-[#667eea] to-[#764ba2] rounded-md text-white text-xs font-semibold disabled:opacity-40">
        {{ loading ? '⏳ 生成中...' : '🎨 生成图片' }}
      </button>
    </div>
  </div>
</template>
<script setup>
import { computed, ref } from 'vue'
const text = defineModel()
const props = defineProps({ loading: Boolean })
defineEmits(['gen'])
const lang = ref('🇨🇳 中文')

const preview = computed(() => {
  if (!text.value) return 'Split vertical composition...'
  return text.value
})

function copy() {
  navigator.clipboard?.writeText(text.value) || (() => {
    const ta = document.createElement('textarea')
    ta.value = text.value; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta)
  })()
}
function download() {
  const blob = new Blob([text.value], {type:'text/plain'})
  const a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = 'prompt.txt'; a.click()
}
</script>
