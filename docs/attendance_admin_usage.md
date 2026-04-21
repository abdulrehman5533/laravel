# Attendance Admin Usage

## Filters & Search
- Filter by user, branch, status, and date using the dashboard UI.
- Search is case-insensitive and supports partial matches.

## Export
- Click "Export CSV" to download filtered attendance logs.

## Map
- Markers are color-coded:
  - Blue: Present
  - Orange: Late
  - Red: Early leave
  - Green: On leave
  - Grey: Check-out location
- Markers are clustered for dense locations.

## Manual Override
- Click "Manual Override" to approve, reject, or edit a record.
- Success/error feedback is shown in real time.

## Analytics
- Use date filter for daily trends.
- For advanced analytics, see backend endpoints or request custom reports.

---

# API Usage

- `GET /api/v1/hr/attendance/today-map?date=YYYY-MM-DD` — Get attendance for a specific date.
- `POST /api/v1/hr/attendance/manual-override` — Admin override (see API docs for payload).

---

# User Feedback
- All actions show real-time success/error messages.
- Map and table update automatically after changes.

---

# Remaining
- For anti-spoofing, offline sync, and advanced analytics, see backend roadmap.
