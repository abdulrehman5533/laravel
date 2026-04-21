import L from 'leaflet';

export default {
  mounted() {
    this.fetchAttendances();
    this.initMap();
  },
  data() {
    return {
      attendances: [],
      map: null,
      markers: []
    };
  },
  methods: {
    fetchAttendances() {
      // Example API call, replace with your endpoint
      fetch('/api/v1/hr/attendance/today-map')
        .then(res => res.json())
        .then(data => {
          this.attendances = data.records || [];
          this.plotMarkers();
        });
    },
    initMap() {
      this.map = L.map('attendance-map').setView([20.5937, 78.9629], 5); // Center on India
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
      }).addTo(this.map);
    },
    plotMarkers() {
      if (!this.map) return;
      // Remove old markers
      this.markers.forEach(m => this.map.removeLayer(m));
      this.markers = [];
      this.attendances.forEach(a => {
        if (a.check_in_lat && a.check_in_lng) {
          const marker = L.marker([a.check_in_lat, a.check_in_lng])
            .addTo(this.map)
            .bindPopup(`<b>${a.user_name}</b><br>Check-in: ${a.check_in_time}`);
          this.markers.push(marker);
        }
      });
    }
  }
};
