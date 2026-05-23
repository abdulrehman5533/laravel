import os
from config import settings
from utils.language_detector import LanguageDetector
from utils.response_formatter import ResponseFormatter

class TTSAgent:
    def __init__(self):
        self.language_detector = LanguageDetector()
        self.google_available = False
        os.makedirs(settings.VOICE_OUTPUT_DIR, exist_ok=True)
        
        try:
            if settings.GOOGLE_APPLICATION_CREDENTIALS:
                from google.cloud import texttospeech_v1
                self.client = texttospeech_v1.TextToSpeechClient()
                self.google_available = True
        except Exception as e:
            print(f"Google Cloud not available: {e}")
    
    async def synthesize_speech(self, text: str, language: str = None, voice_gender: str = "FEMALE") -> dict:
        """Convert text to speech"""
        try:
            if not language:
                language = self.language_detector.detect_language(text)
            
            if not self.google_available:
                return ResponseFormatter.error(
                    "Google Cloud credentials not configured. Please set GOOGLE_APPLICATION_CREDENTIALS in .env",
                    error_code="GOOGLE_NOT_CONFIGURED",
                    status_code=503,
                    details={"hint": "Voice synthesis requires Google Cloud setup. Chat features work without it."}
                )
            
            from google.cloud import texttospeech_v1
            
            lang_code = self.language_detector.get_language_code(language)
            voice_name = self.language_detector.get_voice_name(language)
            
            input_text = texttospeech_v1.SynthesisInput(text=text)
            
            voice = texttospeech_v1.VoiceSelectionParams(
                language_code=lang_code,
                name=voice_name,
                ssml_gender=texttospeech_v1.SsmlVoiceGender.FEMALE if voice_gender == "FEMALE" else texttospeech_v1.SsmlVoiceGender.MALE
            )
            
            audio_config = texttospeech_v1.AudioConfig(
                audio_encoding=texttospeech_v1.AudioEncoding.MP3,
                speaking_rate=1.0,
                pitch=0.0
            )
            
            response = self.client.synthesize_speech(
                input=input_text,
                voice=voice,
                audio_config=audio_config
            )
            
            filename = f"response_{int(__import__('time').time())}.mp3"
            filepath = os.path.join(settings.VOICE_OUTPUT_DIR, filename)
            
            with open(filepath, "wb") as out:
                out.write(response.audio_content)
            
            return ResponseFormatter.success(
                data={
                    "filename": filename,
                    "filepath": filepath,
                    "language": language,
                    "size": len(response.audio_content)
                },
                message="Speech synthesized successfully"
            )
        
        except Exception as e:
            return ResponseFormatter.error(
                f"Speech synthesis failed: {str(e)}",
                error_code="SYNTHESIS_ERROR",
                status_code=500
            )
    
    async def synthesize_with_language_detection(self, text: str) -> dict:
        """Synthesize with automatic language detection"""
        try:
            language = self.language_detector.detect_language(text)
            return await self.synthesize_speech(text, language)
        
        except Exception as e:
            return ResponseFormatter.error(
                f"Language detection synthesis failed: {str(e)}",
                error_code="LANG_DETECT_SYNTHESIS_ERROR",
                status_code=500
            )
