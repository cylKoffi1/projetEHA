import { createApp } from 'vue'
import CaracteristiquesTree from './components/CaracteristiquesTree.vue'

const app = createApp({})
app.component('caracteristiques-tree', CaracteristiquesTree)

app.mount('#app')

window.vueComponentRef = null

// Dès que Vue est prêt, on stocke la référence
window.addEventListener('caracteristiques-tree-ready', () => {
  const el = document.querySelector('caracteristiques-tree')
  if (el && el.__vueParentComponent?.exposed) {
    window.vueComponentRef = el.__vueParentComponent.exposed
    console.log("✅ Composant Vue prêt")
     
    // Si on a des données en attente, on les injecte
    if (window.treeDataBuffer) {
      vueComponentRef.setTree(window.treeDataBuffer)
      window.treeDataBuffer = null
    }
  }
})
console.log("🔧 Vue monté dans #app")
