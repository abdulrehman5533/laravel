# TODO - AI Automation Upgrades

## Step 1: Replace keyword automation with real tool-calling
- (IN PROGRESS) Update `python-ai-agent/app.py` to:
  - return strict structured JSON
  - use tool calls (function name + args)
  - execute DB tools deterministically


## Step 2: Remove hardcoded OpenAI key
- Update `python-ai-agent/app.py` to load `OPENAI_API_KEY` from environment only.

## Step 3: Make DB actions schema-safe
- Add safeguards:
  - table/column allow-lists
  - parameter validation
  - transactions for multi-step actions

## Step 4: Ensure response format matches Laravel UI
- Keep response fields: `{ status, message, data, action }`.

## Step 5: Add session memory for better autonomy
- Accept `context/session_id` in chat request.

## Step 6: Validate endpoints
- Ensure:
  - `GET /health`
  - `GET /api/info/status`
  - `POST /api/chat/message`

