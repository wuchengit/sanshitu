<template>
  <div class="fixed inset-0 bg-black/60 z-[9998] flex items-center justify-center" @click.self="$emit('close')">
    <div class="bg-[#1a1a20] border border-[#2a2a32] rounded-xl p-6 w-[380px] max-w-[90vw]">
      <h2 class="text-base text-[#e8e8ed] mb-4">{{ title }}</h2>
      <div class="space-y-3">
        <div>
          <label class="text-[10px] text-[#888]">名称</label>
          <input v-model="name" class="w-full bg-[#25252d] border border-[#333] rounded-md px-3 py-2 text-xs text-[#e8e8ed] focus:border-[#667eea] outline-none" />
        </div>
        <div v-if="cat==='dir'">
          <label class="text-[10px] text-[#888]">代表作</label>
          <input v-model="label" class="w-full bg-[#25252d] border border-[#333] rounded-md px-3 py-2 text-xs text-[#e8e8ed]" />
        </div>
        <div>
          <label class="text-[10px] text-[#888]">提示词（英文）</label>
          <textarea v-model="prompt" rows="4" class="w-full bg-[#25252d] border border-[#333] rounded-md px-3 py-2 text-xs text-[#e8e8ed] font-mono resize-y" />
        </div>
      </div>
      <div class="flex gap-2 mt-4">
        <button @click="save(false)" class="flex-1 py-2.5 bg-gradient-to-r from-[#667eea] to-[#764ba2] rounded-md text-white text-xs font-semibold">{{ saveLabel }}</button>
        <button v-if="!isNew" @click="save(true)" class="flex-1 py-2.5 bg-[#333] rounded-md text-white text-xs font-semibold">📋 另存</button>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, computed } from 'vue'
const props = defineProps({ category: String, preset: Object, isNew: Boolean })
const emit = defineEmits(['save', 'close'])

const name = ref(props.preset?.name || '')
const label = ref(props.preset?.label || '')
const prompt = ref(props.preset?.prompt || '')

const title = computed(() => props.isNew ? `✏️ 新建自定义` : `✏️ 编辑自定义`)
const saveLabel = computed(() => props.isNew ? '➕ 新增' : '💾 保存')

function save(asCopy) {
  emit('save', { name: name.value, label: label.value, prompt: prompt.value, asCopy })
}
</script>
