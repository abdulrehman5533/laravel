import os
import io
from config import settings
from utils.language_detector import LanguageDetector
from utils.response_formatter import ResponseFormatter

class VoiceAgent:
    def __init__(self):
        self.language_detector = LanguageDetector()
        self.google_available = False
        
        try:
            if settings.GOOGLE_APPLICATION_CREDENTIALS:
                from google.cloud import speech_v1
                self.client = speech_v1.SpeechClient()
                self.google_available = True
        except Exception as e:
            print(f"Google Cloud not available: {e}")
    
    async def transcribe_audio(self, audio_file: bytes, language: str = None) -> dict:
        """Convert audio to text"""
        try:
            if not language:
                language = settings.DEFAULT_LANGUAGE
            
            if not self.google_available:
                return ResponseFormatter.error(
                    "Google Cloud credentials not configured. Please set GOOGLE_APPLICATION_CREDENTIALS in .env",
                    error_code="GOOGLE_NOT_CONFIGURED",
                    status_code=503,
                    details={"hint": "Voice features require Google Cloud setup. Chat features work without it."}
                )
            
            from google.cloud import speech_v1
            
            lang_code = self.language_detector.get_language_code(language)
            
            audio = speech_v1.RecognitionAudio(content=audio_file)
            config = speech_v1.RecognitionConfig(
                encoding=speech_v1.RecognitionConfig.AudioEncoding.LINEAR16,
                sample_rate_hertz=settings.AUDIO_SAMPLE_RATE,
                language_code=lang_code,
                enable_automatic_punctuation=True,
                model="latest_long"
            )
            
            response = self.client.recognize(config=config, audio=audio)
            
            if response.results:
                transcript = response.results[0].alternatives[0].transcript
                confidence = response.results[0].alternatives[0].confidence
                
                return ResponseFormatter.success(
                    data={
                        "transcript": transcript,
                        "confidence": confidence,
                        "language": language
                    },
                    message="Audio transcribed successfully"
                )
            else:
                return ResponseFormatter.error(
                    "No speech detected in audio",
                    error_code="NO_SPEECH_DETECTED",
                    status_code=400
                )
        
        except Exception as e:
            return ResponseFormatter.error(
                f"Transcription failed: {str(e)}",
                error_code="TRANSCRIPTION_ERROR",
                status_code=500
            )
    
    async def transcribe_with_language_detection(self, audio_file: bytes) -> dict:
        """Transcribe with automatic language detection"""
        try:
            result = await self.transcribe_audio(audio_file, settings.DEFAULT_LANGUAGE)
            
            if result["status"] == "success":
                return result
            
            return await self.transcribe_audio(audio_file, "en")
        
        except Exception as e:
            return ResponseFormatter.error(
                f"Language detection transcription failed: {str(e)}",
                error_code="LANG_DETECT_ERROR",
                status_code=500
            )
