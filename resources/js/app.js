import Alpine from 'alpinejs';
import { createIcons, icons } from 'lucide';

window.Alpine = Alpine;
Alpine.start();

// Inisialisasi Lucide setelah DOM siap
document.addEventListener('DOMContentLoaded', () => {
    createIcons({ icons });
});