"""
Production-Ready AI Agent Server
"""
import logging
import os
from contextlib import asynccontextmanager
from fastapi import FastAPI, File, UploadFile, Form, HTTPException
from fastapi.responses import FileResponse, JSONResponse
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from typing import Optional
import uvicorn

# Load environment variables
from dotenv import load_dotenv
load_dotenv()

from production_config import production_settings
from agents.voice_agent import VoiceAgent
from agents.chat_agent import ChatAgent
from agents.tts_agent import TTSAgent
from utils.response_formatter import ResponseFormatter

# Configure logging
logging.basicConfig(
    level=getattr(logging, production_settings.LOG_LEVEL),
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler(production_settings.LOG_FILE),
        logging.StreamHandler()
    ]
)
logger = logging.getLogger(__name__)

# Validate production settings
validation_errors = production_settings.validate()
if validation_errors:
    logger.warning(f"Configuration warnings: {validation_errors}")

# Initialize agents
voice_agent = VoiceAgent()
chat_agent = ChatAgent()
tts_agent = TTSAgent()

# Pydantic models
class ChatRequest(BaseModel):
    message: str
    language: Optional[str] = None
    context: Optional[dict] = None

class TTSRequest(BaseModel):
    text: str
    language: Optional[str] = None
    voice_gender: Optional[str] = "FEMALE"

@asynccontextmanager
async def lifespan(app: FastAPI):
    """Lifespan context manager for startup and shutdown"""
    logger.info("AI Agent starting up...")
    yield
    logger.info("AI Agent shutting down...")

app = FastAPI(
    title=production_settings.API_TITLE,
    version=production_settings.API_VERSION,
    description=production_settings.API_DESCRIPTION,
    lifespan=lifespan
)

# CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Health check
@app.get("/health")
async def health_check():
    logger.info("Health check requested")
    return ResponseFormatter.success(
        data={"status": "healthy", "version": production_settings.API_VERSION},
        message="AI Agent is running"
    )

# Voice endpoints
@app.post("/api/voice/transcribe")
async def transcribe_voice(
    file: UploadFile = File(...),
    language: Optional[str] = Form(None)
):
    """Convert voice to text"""
    try:
        logger.info(f"Transcribe request received for language: {language}")
        audio_content = await file.read()
        result = await voice_agent.transcribe_audio(audio_content, language)
        logger.info("Transcription completed successfully")
        return result
    except Exception as e:
        logger.error(f"Voice transcription failed: {str(e)}")
        return ResponseFormatter.error(
            f"Voice transcription failed: {str(e)}",
            error_code="VOICE_ERROR",
            status_code=500
        )

@app.post("/api/voice/transcribe-auto")
async def transcribe_voice_auto(file: UploadFile = File(...)):
    """Convert voice to text with automatic language detection"""
    try:
        logger.info("Auto transcribe request received")
        audio_content = await file.read()
        result = await voice_agent.transcribe_with_language_detection(audio_content)
        logger.info("Auto transcription completed successfully")
        return result
    except Exception as e:
        logger.error(f"Auto transcription failed: {str(e)}")
        return ResponseFormatter.error(
            f"Auto transcription failed: {str(e)}",
            error_code="AUTO_TRANSCRIBE_ERROR",
            status_code=500
        )

# Chat endpoints
@app.post("/api/chat/message")
async def process_chat(request: ChatRequest):
    """Process chat message"""
    try:
        logger.info(f"Chat message received: {request.message[:50]}...")
        result = await chat_agent.process_message(
            request.message,
            request.language,
            request.context
        )
        logger.info("Chat processing completed successfully")
        return result
    except Exception as e:
        logger.error(f"Chat processing failed: {str(e)}")
        return ResponseFormatter.error(
            f"Chat processing failed: {str(e)}",
            error_code="CHAT_ERROR",
            status_code=500
        )

@app.post("/api/chat/voice-to-chat")
async def voice_to_chat(
    file: UploadFile = File(...),
    language: Optional[str] = Form(None)
):
    """Convert voice to text and process as chat"""
    try:
        logger.info("Voice-to-chat request received")
        audio_content = await file.read()
        transcribe_result = await voice_agent.transcribe_audio(audio_content, language)
        
        if transcribe_result["status"] != "success":
            return transcribe_result
        
        transcript = transcribe_result["data"]["transcript"]
        detected_language = transcribe_result["data"]["language"]
        
        chat_result = await chat_agent.process_message(
            transcript,
            detected_language
        )
        
        logger.info("Voice-to-chat completed successfully")
        return {
            **chat_result,
            "transcript": transcript
        }
    except Exception as e:
        logger.error(f"Voice-to-chat failed: {str(e)}")
        return ResponseFormatter.error(
            f"Voice-to-chat failed: {str(e)}",
            error_code="VOICE_CHAT_ERROR",
            status_code=500
        )

# Text-to-Speech endpoints
@app.post("/api/tts/synthesize")
async def synthesize_speech(request: TTSRequest):
    """Convert text to speech"""
    try:
        logger.info(f"TTS request received for language: {request.language}")
        result = await tts_agent.synthesize_speech(
            request.text,
            request.language,
            request.voice_gender
        )
        logger.info("Speech synthesis completed successfully")
        return result
    except Exception as e:
        logger.error(f"Speech synthesis failed: {str(e)}")
        return ResponseFormatter.error(
            f"Speech synthesis failed: {str(e)}",
            error_code="TTS_ERROR",
            status_code=500
        )

@app.post("/api/tts/synthesize-auto")
async def synthesize_speech_auto(request: TTSRequest):
    """Convert text to speech with automatic language detection"""
    try:
        logger.info("Auto TTS request received")
        result = await tts_agent.synthesize_with_language_detection(request.text)
        logger.info("Auto speech synthesis completed successfully")
        return result
    except Exception as e:
        logger.error(f"Auto synthesis failed: {str(e)}")
        return ResponseFormatter.error(
            f"Auto synthesis failed: {str(e)}",
            error_code="AUTO_SYNTHESIS_ERROR",
            status_code=500
        )

@app.get("/api/tts/download/{filename}")
async def download_audio(filename: str):
    """Download generated audio file"""
    try:
        logger.info(f"Audio download requested: {filename}")
        filepath = os.path.join(production_settings.VOICE_OUTPUT_DIR, filename)
        
        if not os.path.exists(filepath):
            logger.warning(f"Audio file not found: {filename}")
            return ResponseFormatter.error(
                "Audio file not found",
                error_code="FILE_NOT_FOUND",
                status_code=404
            )
        
        return FileResponse(
            filepath,
            media_type="audio/mpeg",
            filename=filename
        )
    except Exception as e:
        logger.error(f"Download failed: {str(e)}")
        return ResponseFormatter.error(
            f"Download failed: {str(e)}",
            error_code="DOWNLOAD_ERROR",
            status_code=500
        )

# Full automation endpoint
@app.post("/api/automation/voice-to-voice")
async def voice_to_voice(
    file: UploadFile = File(...),
    language: Optional[str] = Form(None)
):
    """Full automation: Voice -> Chat -> Voice"""
    try:
        logger.info("Voice-to-voice automation started")
        
        # Step 1: Transcribe voice
        audio_content = await file.read()
        transcribe_result = await voice_agent.transcribe_audio(audio_content, language)
        
        if transcribe_result["status"] != "success":
            return transcribe_result
        
        transcript = transcribe_result["data"]["transcript"]
        detected_language = transcribe_result["data"]["language"]
        
        # Step 2: Process as chat
        chat_result = await chat_agent.process_message(
            transcript,
            detected_language
        )
        
        if chat_result["status"] != "success":
            return chat_result
        
        response_text = chat_result["data"].get("answer") or chat_result["data"].get("extracted")
        
        # Step 3: Convert response to speech
        tts_result = await tts_agent.synthesize_speech(
            response_text,
            detected_language
        )
        
        logger.info("Voice-to-voice automation completed successfully")
        return {
            "status": "success",
            "message": "Full automation completed",
            "data": {
                "input_transcript": transcript,
                "input_language": detected_language,
                "response_text": response_text,
                "audio_file": tts_result["data"]["filename"] if tts_result["status"] == "success" else None,
                "audio_url": f"/api/tts/download/{tts_result['data']['filename']}" if tts_result["status"] == "success" else None
            }
        }
    except Exception as e:
        logger.error(f"Voice-to-voice automation failed: {str(e)}")
        return ResponseFormatter.error(
            f"Voice-to-voice automation failed: {str(e)}",
            error_code="AUTOMATION_ERROR",
            status_code=500
        )

# Info endpoints
@app.get("/api/info/languages")
async def get_supported_languages():
    """Get list of supported languages"""
    logger.info("Supported languages requested")
    return ResponseFormatter.success(
        data=production_settings.SUPPORTED_LANGUAGES,
        message="Supported languages"
    )

@app.get("/api/info/status")
async def get_agent_status():
    """Get agent status and capabilities"""
    logger.info("Agent status requested")
    return ResponseFormatter.success(
        data={
            "name": production_settings.API_TITLE,
            "version": production_settings.API_VERSION,
            "capabilities": [
                "voice_to_text",
                "text_to_voice",
                "chat_processing",
                "voice_to_chat",
                "voice_to_voice_automation",
                "multi_language_support"
            ],
            "supported_languages": production_settings.SUPPORTED_LANGUAGES,
            "default_language": production_settings.DEFAULT_LANGUAGE
        },
        message="Agent status"
    )

if __name__ == "__main__":
    logger.info(f"Starting AI Agent on {production_settings.HOST}:{production_settings.PORT}")
    logger.info(f"Debug mode: {production_settings.DEBUG}")
    logger.info(f"Workers: {production_settings.WORKERS}")
    
    uvicorn.run(
        app,
        host=production_settings.HOST,
        port=production_settings.PORT,
        workers=production_settings.WORKERS,
        log_level=production_settings.LOG_LEVEL.lower()
    )
