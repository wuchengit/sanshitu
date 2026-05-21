<script setup>
import { computed } from "vue"

defineProps({ modelValue: String, customPresets: Array, user: Object })
defineEmits(['update:modelValue', 'edit', 'delete'])
const items = computed(() => {
  const defaults = [
    { id:'classic', name:'经典三视图', desc:'9:16 面部+三视图' },
    { id:'fullbody', name:'全身定妆照', desc:'9:16 正面站立' },
    { id:'pure', name:'纯三视图', desc:'16:9 全身+头部' },
    { id:'sidebyside', name:'面部三视图', desc:'16:9 面部三视角' },
    { id:'detail', name:'三视图+细节', desc:'16:9 三视图+特写' },
    { id:'halfbody', name:'半身三视图', desc:'16:9 半身三视角' },
    { id:'headshot', name:'头像三视图', desc:'全身照+头部三视图' }
  ]
  return [...defaults, ...(props.customPresets||[])]
})
</script>
