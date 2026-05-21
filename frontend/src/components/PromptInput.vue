<template>
  <div class="bg-[#1a1a20] border border-[#2a2a32] rounded-lg p-3">
    <div class="text-[11px] font-semibold text-[#aaa] mb-2">📝 提示词</div>
    <textarea v-model="text" rows="3" placeholder="选择结构和风格自动生成提示词..."
      class="w-full bg-[#25252d] border border-[#333] rounded-md px-2 py-1.5 text-xs text-[#e8e8ed] resize-y focus:border-[#667eea] outline-none font-mono"
      :style="{fontSize: fontSize+'px'}"></textarea>
    <div class="text-[10px] text-[#aaa] font-semibold mt-2 mb-1">📋 提示词预览</div>
    <div class="text-[#999] font-mono whitespace-pre-wrap max-h-[120px] overflow-y-auto bg-[#0a0a0e] rounded p-2 text-xs leading-relaxed"
      :style="{fontSize: fontSize+'px'}">{{ text || '选择结构和风格后自动生成提示词' }}</div>
    <div class="flex gap-1 mt-1.5 flex-wrap items-center">
      <button @click="copy" class="px-2 py-1 bg-[#25252d] border border-[#333] rounded text-[10px] text-[#aaa] hover:bg-[#333] hover:text-white">📋 复制</button>
      <button @click="download" class="px-2 py-1 bg-[#25252d] border border-[#333] rounded text-[10px] text-[#aaa] hover:bg-[#333] hover:text-white">⬇️ 下载</button>
      <button @click="doTranslate" :disabled="translating" class="px-2 py-1 bg-[#25252d] border border-[#333] rounded text-[10px] text-[#aaa] hover:bg-[#333] hover:text-white">{{ translating ? '⏳' : '🌐 翻译' }}</button>
      <select v-model="lang" class="bg-[#25252d] border border-[#333] rounded px-2 py-1 text-[10px] text-[#aaa]">
        <option value="zh">🇨🇳 中文</option><option value="en">🇬🇧 English</option><option value="ja">🇯🇵 日本語</option>
        <option value="ko">🇰🇷 한국어</option><option value="fr">🇫🇷 Français</option><option value="es">🇪🇸 Español</option>
      </select>
      <span class="text-[10px] text-[#888] ml-1">字号</span>
      <select v-model="fontSize" class="bg-[#25252d] border border-[#333] rounded px-2 py-1 text-[10px] text-[#aaa]">
        <option :value="10">小</option><option :value="12">默认</option><option :value="14">中</option><option :value="16">大</option>
      </select>
      <button v-if="text !== origPrompt && origPrompt" @click="$emit('restore')" class="px-2 py-1 bg-[#25252d] border border-[#333] rounded text-[10px] text-[#aaa] hover:bg-[#333]">↩ 恢复原文</button>
      <span class="text-[10px] text-[#555] ml-auto">{{ text.length }}</span>
    </div>
    <div class="flex gap-1 mt-2">
      <button @click="$emit('gen')" :disabled="loading" class="flex-1 py-2 bg-gradient-to-r from-[#667eea] to-[#764ba2] rounded-md text-white text-xs font-semibold disabled:opacity-40 hover:opacity-90">
        {{ loading ? '⏳ 生成中...' : '🎨 生成图片' }}
      </button>
    </div>
  </div>
</template>
<script setup>
import { ref } from 'vue'
const text = defineModel('prompt')
const props = defineProps({ loading: Boolean, origPrompt: String })
const emit = defineEmits(['gen', 'translate', 'restore'])
const lang = ref('zh')
const translating = ref(false)
const fontSize = ref(12)

function copy() {
  const ta = document.createElement('textarea'); ta.value = text.value
  document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta)
}
function download() {
  const blob = new Blob([text.value], {type:'text/plain'})
  const a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = 'prompt.txt'; a.click()
}
async function doTranslate() {
  translating.value = true
  await emit('translate', lang.value)
  translating.value = false
}
</script>
