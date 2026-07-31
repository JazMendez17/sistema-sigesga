import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useAppStore = defineStore('app', () => {
  const notifications = ref([])
  const sidebarOpen = ref(false)

  function toggleSidebar() {
    sidebarOpen.value = !sidebarOpen.value
  }

  function addNotification(notification) {
    notifications.value.push(notification)
  }

  function clearNotifications() {
    notifications.value = []
  }

  return {
    notifications,
    sidebarOpen,
    toggleSidebar,
    addNotification,
    clearNotifications,
  }
})
