# Attendance Module QA Checklist

## Web
- [ ] User can check in/out with valid location and device.
- [ ] Duplicate check-ins are prevented.
- [ ] Geo-fence and anti-spoofing logic is enforced.
- [ ] Manual override works and notifies user.
- [ ] Admin dashboard: filters, search, export, and map all function.
- [ ] Analytics endpoints return correct data.
- [ ] Error/success messages are shown for all actions.

## Mobile
- [ ] Offline check-in/out is stored and synced when online.
- [ ] Device integrity and mock location flags are sent and validated.
- [ ] User receives feedback for all actions.

## API
- [ ] All endpoints are secured (auth, policy).
- [ ] Analytics and absent report endpoints work for various date ranges.
- [ ] Batch offline sync endpoint processes records correctly.

## General
- [ ] Audit logs are created for all actions.
- [ ] Suspicious/failed attempts are logged.
- [ ] All flows tested for edge cases (late, early, leave, absent).

---

# Documentation
- See `docs/attendance_admin_usage.md` for admin and API usage.
- See `docs/attendance_production_checklist.md` for production readiness.
- For mobile/offline/anti-spoofing, see backend service stubs and roadmap.
