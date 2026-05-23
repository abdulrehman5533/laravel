from langdetect import detect, DetectorFactory
from config import settings

DetectorFactory.seed = 0

class LanguageDetector:
    @staticmethod
    def detect_language(text: str) -> str:
        """Detect language from text"""
        try:
            lang = detect(text)
            lang_map = {
                'ur': 'ur',
                'en': 'en',
                'hi': 'hi',
                'pa': 'pa',
                'pt': 'en',
            }
            return lang_map.get(lang, settings.DEFAULT_LANGUAGE)
        except:
            return settings.DEFAULT_LANGUAGE
    
    @staticmethod
    def get_language_code(lang: str) -> str:
        """Get full language code for Google APIs"""
        codes = {
            'ur': 'ur-PK',
            'en': 'en-US',
            'hi': 'hi-IN',
            'pa': 'pa-IN'
        }
        return codes.get(lang, 'en-US')
    
    @staticmethod
    def get_voice_name(lang: str) -> str:
        """Get voice name for Text-to-Speech"""
        voices = {
            'ur': 'ur-PK-Neural2-A',
            'en': 'en-US-Neural2-C',
            'hi': 'hi-IN-Neural2-A',
            'pa': 'pa-IN-Neural2-A'
        }
        return voices.get(lang, 'en-US-Neural2-C')
