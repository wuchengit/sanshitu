<template>
  <div class="bg-[#1a1a20] border border-[#2a2a32] rounded-lg p-3">
    <div class="flex items-center gap-2 mb-2">
      <span class="text-[11px] font-semibold text-[#aaa]">📐 人物资产结构</span>
      <button @click="$emit('update:modelValue','classic')" class="ml-auto px-1.5 py-px bg-transparent border border-[#333] rounded text-[8px] text-[#555] hover:text-[#e74c3c]">重置</button>
    </div>
    <div class="flex gap-1.5 overflow-x-auto">
      <div v-for="l in layouts" :key="l.id" @click="$emit('update:modelValue', l.id)"
        :class="['flex-shrink-0 w-[100px] bg-[#25252d] border rounded-md p-1.5 cursor-pointer text-center transition-all',
          modelValue===l.id ? 'border-[#667eea] bg-[#667eea15]' : 'border-[#333] hover:border-[#667eea66]']">
        <div class="w-full aspect-[9/10] bg-[#1a1a20] rounded mb-1 overflow-hidden" v-html="l.svg"></div>
        <div class="text-[9px] font-semibold text-[#ccc]">{{ l.name }}</div>
        <div class="text-[8px] text-[#777]">{{ l.desc }}</div>
      </div>
    </div>
  </div>
</template>
<script setup>
defineProps({ modelValue: String })
defineEmits(['update:modelValue'])
const layouts = [
  { id:'classic', name:'经典三视图', desc:'9:16 面部+三视图', svg:'<svg viewBox="0 0 90 100"><rect width="90" height="100" fill="#1e1e26"/><rect x="5" y="2" width="80" height="45" rx="3" fill="#667eea22" stroke="#667eea44"/><rect x="5" y="50" width="25" height="48" rx="2" fill="#667eea11"/><rect x="32" y="50" width="25" height="48" rx="2" fill="#667eea11"/><rect x="59" y="50" width="25" height="48" rx="2" fill="#667eea11"/></svg>' },
  { id:'fullbody', name:'全身定妆照', desc:'9:16 正面站立', svg:'<svg viewBox="0 0 90 100"><rect width="90" height="100" fill="#1e1e26"/><rect x="15" y="5" width="60" height="90" rx="4" fill="#667eea11" stroke="#667eea33"/></svg>' },
  { id:'pure', name:'纯三视图', desc:'16:9 全身+头部', svg:'<svg viewBox="0 0 90 100"><rect width="90" height="100" fill="#1e1e26"/><rect x="2" y="5" width="28" height="90" rx="2" fill="#667eea11"/><rect x="31" y="5" width="28" height="90" rx="2" fill="#667eea11"/><rect x="60" y="5" width="28" height="90" rx="2" fill="#667eea11"/></svg>' },
  { id:'sidebyside', name:'面部三视图', desc:'16:9 面部三视角', svg:'<svg viewBox="0 0 90 100"><rect width="90" height="100" fill="#1e1e26"/><circle cx="15" cy="50" r="12" fill="none" stroke="#667eea44"/><circle cx="45" cy="50" r="12" fill="none" stroke="#667eea44"/><circle cx="75" cy="50" r="12" fill="none" stroke="#667eea44"/></svg>' },
  { id:'detail', name:'三视图+细节', desc:'16:9 三视图+特写', svg:'<svg viewBox="0 0 90 100"><rect width="90" height="100" fill="#1e1e26"/><rect x="2" y="2" width="42" height="55" rx="2" fill="#667eea11"/><rect x="46" y="2" width="42" height="55" rx="2" fill="#667eea11"/><rect x="2" y="60" width="86" height="38" rx="2" fill="#667eea0a"/></svg>' },
  { id:'halfbody', name:'半身三视图', desc:'16:9 半身三视角', svg:'<svg viewBox="0 0 90 100"><rect width="90" height="100" fill="#1e1e26"/><rect x="2" y="15" width="28" height="70" rx="2" fill="#667eea11"/><rect x="31" y="15" width="28" height="70" rx="2" fill="#667eea11"/><rect x="60" y="15" width="28" height="70" rx="2" fill="#667eea11"/></svg>' },
  { id:'headshot', name:'头像三视图', desc:'全身照+头部三视图', svg:'<svg viewBox="0 0 90 100"><rect width="90" height="100" fill="#1e1e26"/><rect x="5" y="2" width="38" height="55" rx="2" fill="#667eea11"/><circle cx="55" cy="20" r="10" fill="none" stroke="#667eea44"/><circle cx="75" cy="20" r="10" fill="none" stroke="#667eea44"/><circle cx="65" cy="40" r="10" fill="none" stroke="#667eea44"/></svg>' }
]
</script>
