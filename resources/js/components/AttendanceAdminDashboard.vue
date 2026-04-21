<template>
  <div class="attendance-admin">
    <h1>Attendance Admin Dashboard</h1>
    <div>
      <h2>Live Map (Today)</h2>
      <div id="attendance-map" style="height:400px;"></div>
    </div>
    <div>
      <h2>Attendance Logs</h2>
      <div class="filters">
        <input v-model="filters.user" placeholder="User name/email" />
        <input v-model="filters.branch" placeholder="Branch" />
        <input v-model="filters.status" placeholder="Status (present/leave/late/early)" />
        <input v-model="filters.date" type="date" />
        <button @click="fetchAttendances">Filter</button>
        <button @click="exportCSV">Export CSV</button>
      </div>
      <table>
        <thead>
          <tr>
            <th>User</th>
            <th>Check In</th>
            <th>Check Out</th>
            <th>Status</th>
            <th>Branch</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="record in filteredAttendances" :key="record.id">
            <td>{{ record.user_name }}</td>
            <td>{{ record.check_in_time }}</td>
            <td>{{ record.check_out_time }}</td>
            <td :style="statusStyle(record.status)">{{ record.status }}</td>
            <td>{{ record.branch_name }}</td>
            <td>
              <button @click="openOverride(record)">Manual Override</button>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="feedback.message" :class="['feedback', feedback.type]">{{ feedback.message }}</div>
    </div>
    <div v-if="showOverrideModal">
      <div class="modal">
        <h3>Manual Override</h3>
        <form @submit.prevent="submitOverride">
          <label>Action:
            <select v-model="overrideForm.action">
              <option value="approve">Approve</option>
              <option value="reject">Reject</option>
              <option value="edit">Edit</option>
            </select>
          </label>
          <label>Reason:
            <input v-model="overrideForm.reason" required />
          </label>
          <label v-if="overrideForm.action === 'edit'">Edit Data (JSON):
            <textarea v-model="overrideForm.data"></textarea>
          </label>
          <button type="submit">Submit</button>
          <button @click="showOverrideModal=false">Cancel</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import 'leaflet.markercluster';
export default {
  data() {
    return {
      attendances: [],
      map: null,
      markers: [],
      markerCluster: null,
      filters: { user: '', branch: '', status: '', date: '' },
      showOverrideModal: false,
      overrideForm: {
        attendance_id: null,
        action: 'approve',
        reason: '',
        data: ''
      },
      feedback: { message: '', type: '' }
    };
  },
  computed: {
    filteredAttendances() {
      return this.attendances.filter(a => {
        const userMatch = !this.filters.user || (a.user_name && a.user_name.toLowerCase().includes(this.filters.user.toLowerCase()));
        const branchMatch = !this.filters.branch || (a.branch_name && a.branch_name.toLowerCase().includes(this.filters.branch.toLowerCase()));
        const statusMatch = !this.filters.status || (a.status && a.status.toLowerCase().includes(this.filters.status.toLowerCase()));
        const dateMatch = !this.filters.date || (a.check_in_time && a.check_in_time.startsWith(this.filters.date));
        return userMatch && branchMatch && statusMatch && dateMatch;
      });
    }
  },
  mounted() {
    this.fetchAttendances();
    this.initMap();
  },
  methods: {
    fetchAttendances() {
      let url = '/api/v1/hr/attendance/today-map';
      if (this.filters.date) {
        url += '?date=' + encodeURIComponent(this.filters.date);
      }
      fetch(url)
        .then(res => res.json())
        .then(data => {
          this.attendances = data.records || [];
          this.plotMarkers();
        });
    },
    exportCSV() {
      const rows = [
        ['User', 'Check In', 'Check Out', 'Status', 'Branch'],
        ...this.filteredAttendances.map(a => [a.user_name, a.check_in_time, a.check_out_time, a.status, a.branch_name])
      ];
      const csv = rows.map(r => r.map(x => '"' + (x || '') + '"').join(',')).join('\n');
      const blob = new Blob([csv], { type: 'text/csv' });
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.download = 'attendance.csv';
      link.click();
    },
    statusStyle(status) {
      if (!status) return {};
      if (status.includes('late')) return { color: 'orange', fontWeight: 'bold' };
      if (status.includes('early')) return { color: 'red', fontWeight: 'bold' };
      if (status.includes('leave')) return { color: 'blue', fontWeight: 'bold' };
      return {};
    },
    initMap() {
      this.map = window.L && window.L.map ? window.L.map('attendance-map').setView([20.5937, 78.9629], 5) : null;
      if (this.map) {
        window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '© OpenStreetMap contributors'
        }).addTo(this.map);
        this.markerCluster = window.L.markerClusterGroup ? window.L.markerClusterGroup() : null;
        if (this.markerCluster) this.map.addLayer(this.markerCluster);
      }
    },
    plotMarkers() {
      if (!this.map) return;
      if (this.markerCluster) this.markerCluster.clearLayers();
      this.markers.forEach(m => this.map.removeLayer(m));
      this.markers = [];
      this.attendances.forEach(a => {
        if (a.check_in_lat && a.check_in_lng) {
          let iconColor = 'blue';
          if (a.status && a.status.includes('late')) iconColor = 'orange';
          if (a.status && a.status.includes('early')) iconColor = 'red';
          if (a.status && a.status.includes('leave')) iconColor = 'green';
          const icon = window.L.icon ? window.L.icon({
            iconUrl: `https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-${iconColor}.png`,
            iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png', shadowSize: [41, 41]
          }) : undefined;
          const marker = window.L.marker([a.check_in_lat, a.check_in_lng], icon ? { icon } : {})
            .bindPopup(`<b>${a.user_name}</b><br>Check-in: ${a.check_in_time}`);
          if (this.markerCluster) this.markerCluster.addLayer(marker);
          else marker.addTo(this.map);
          this.markers.push(marker);
        }
        // Show check-out location if available
        if (a.check_out_lat && a.check_out_lng) {
          const outIcon = window.L.icon ? window.L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-grey.png',
            iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png', shadowSize: [41, 41]
          }) : undefined;
          const outMarker = window.L.marker([a.check_out_lat, a.check_out_lng], outIcon ? { icon: outIcon } : {})
            .bindPopup(`<b>${a.user_name}</b><br>Check-out: ${a.check_out_time}`);
          if (this.markerCluster) this.markerCluster.addLayer(outMarker);
          else outMarker.addTo(this.map);
          this.markers.push(outMarker);
        }
      });
    },
    openOverride(record) {
      this.overrideForm.attendance_id = record.id;
      this.overrideForm.action = 'approve';
      this.overrideForm.reason = '';
      this.overrideForm.data = '';
      this.showOverrideModal = true;
    },
    submitOverride() {
      fetch('/api/v1/hr/attendance/manual-override', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(this.overrideForm)
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            this.feedback = { message: 'Override successful!', type: 'success' };
            this.fetchAttendances();
          } else {
            this.feedback = { message: data.error || 'Override failed', type: 'error' };
          }
        })
        .catch(() => {
          this.feedback = { message: 'Override failed', type: 'error' };
        });
      this.showOverrideModal = false;
    }
  }
};
</script>

<style scoped>
.attendance-admin { padding: 2rem; }
table { width: 100%; border-collapse: collapse; }
th, td { border: 1px solid #ccc; padding: 0.5rem; }
.modal { background: #fff; border: 1px solid #333; padding: 1rem; position: fixed; top: 20%; left: 30%; width: 40%; z-index: 1000; }
 .filters { margin-bottom: 1rem; }
 .filters input { margin-right: 0.5rem; }
 .feedback.success { color: green; margin-top: 1rem; }
 .feedback.error { color: red; margin-top: 1rem; }
</style>
