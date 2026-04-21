import { createApp } from 'vue';
import AttendanceAdminDashboard from './components/AttendanceAdminDashboard.vue';

const app = createApp({});
app.component('attendance-admin-dashboard', AttendanceAdminDashboard);
app.mount('#attendance-admin-root');
