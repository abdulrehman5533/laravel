@echo off
REM Test AI Agent Fixes
REM This script tests all the fixes applied

echo.
echo ========================================
echo AI AGENT - INSTANT FIX TEST
echo ========================================
echo.

REM Test 1: Python Agent Health
echo [TEST 1] Checking Python Agent Health...
curl -s http://localhost:8001/health >nul 2>&1
if errorlevel 1 (
    echo ERROR: Python agent not running on port 8001
    echo Please start it with: python-ai-agent\start-agent.bat
    pause
    exit /b 1
) else (
    echo OK: Python agent is running
)

REM Test 2: Python Agent Status
echo.
echo [TEST 2] Getting Python Agent Status...
curl -s http://localhost:8001/api/info/status | findstr "online" >nul 2>&1
if errorlevel 1 (
    echo WARNING: Could not verify agent status
) else (
    echo OK: Agent status verified
)

REM Test 3: Send Test Message
echo.
echo [TEST 3] Sending test message...
curl -s -X POST http://localhost:8001/api/chat/message ^
  -H "Content-Type: application/json" ^
  -d "{\"message\": \"HI\", \"language\": \"ur\"}" | findstr "success" >nul 2>&1
if errorlevel 1 (
    echo ERROR: Message processing failed
) else (
    echo OK: Message processed successfully
)

REM Test 4: Check Laravel Configuration
echo.
echo [TEST 4] Checking Laravel configuration...
if exist ".env" (
    findstr "PYTHON_AGENT_URL" .env >nul 2>&1
    if errorlevel 1 (
        echo WARNING: PYTHON_AGENT_URL not found in .env
    ) else (
        echo OK: PYTHON_AGENT_URL configured
    )
) else (
    echo ERROR: .env file not found
)

REM Test 5: Check AdvancedAIService
echo.
echo [TEST 5] Checking AdvancedAIService...
if exist "app\Services\AdvancedAIService.php" (
    findstr "callPythonAgent" app\Services\AdvancedAIService.php >nul 2>&1
    if errorlevel 1 (
        echo ERROR: Python agent fallback not found
    ) else (
        echo OK: Python agent fallback implemented
    )
) else (
    echo ERROR: AdvancedAIService not found
)

echo.
echo ========================================
echo ALL TESTS COMPLETED
echo ========================================
echo.
echo Next steps:
echo 1. Open browser: http://localhost:8000/ai-agent/chat
echo 2. Send a message
echo 3. You should get instant response!
echo.

pause
