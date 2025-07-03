import { onMounted } from 'vue'

onMounted(() => {
  window.dispatchEvent(new CustomEvent('caracteristiques-tree-ready'))
})
<template>
  <div>
    <p>✅ Composant monté avec {{ localTree.length }} racines</p>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
const props = defineProps({ tree: { type: Array, default: () => [] } })
const localTree = ref(props.tree)

onMounted(() => {
  window.dispatchEvent(new CustomEvent('caracteristiques-tree-ready'))
})
defineExpose({
  setTree(tree) {
    localTree.value = tree
    console.log('✅ Arbre mis à jour depuis JS')
  }
})
</script>
