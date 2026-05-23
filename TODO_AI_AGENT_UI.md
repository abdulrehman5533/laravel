# TODO_AI_AGENT_UI

## Step 1 — UI upgrade (no backend breaking)
- [ ] Add message-level AI actions toolbar (copy, explain, summarize, regenerate) with safe UI placeholders.
- [ ] Add “Stop generating” button for streaming UX (closes current stream fetch / SSE).
- [ ] Upgrade markdown rendering to a safer, more complete renderer (while preventing XSS).
- [ ] Improve tool receipt UI: collapsible payload + better error styling.

## Step 2 — Advanced AI features UI
- [ ] Add attachment upload button + preview receipt in chat.
- [ ] Add voice controls: record mic -> voice-to-chat, and speaker -> TTS (UI should degrade gracefully if endpoints fail).
- [ ] Add model/provider selector dropdown.

## Step 3 — Endpoint alignment (only if missing)
- [ ] Verify public routes for analytics + attachments + voice endpoints used by UI.
- [ ] Add missing public routes or adjust UI endpoints to match existing controllers.

## Step 4 — QA
- [ ] Test: send message (standard)
- [ ] Test: send message (streaming) + stop generating
- [ ] Test: copy message content
- [ ] Test: upload attachment
- [ ] Test: voice-to-chat and TTS

