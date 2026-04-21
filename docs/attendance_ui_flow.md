<?php
// UI Flow Description for Attendance (Web & Mobile)

/**
 * 1. User logs in (web/mobile app)
 * 2. Dashboard shows "Check In" button (disabled if already checked in)
 * 3. On tap/click:
 *    - Request location permission
 *    - Fetch device info (browser/app, OS, device ID)
 *    - Show spinner/loading
 *    - Send check-in request to API with lat/lng, device info, etc.
 * 4. API validates geo-fence, device, session, etc.
 * 5. On success:
 *    - Show confirmation: "Checked in at [address], [time]"
 *    - Show "Check Out" button
 *    - Show map with current location and branch
 * 6. On check-out:
 *    - Repeat location/device capture
 *    - Show working hours summary
 *    - Show feedback if late/early
 * 7. If GPS denied or error:
 *    - Show error message, retry option
 * 8. If offline:
 *    - Store locally, sync when online
 * 9. Admin view:
 *    - Live map of all check-ins today
 *    - List of late/early/absent
 *    - Manual override UI
 */
