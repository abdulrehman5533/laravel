"""
Production Configuration for AI Agent
"""
import os
from typing import List

class ProductionSettings:
    # Server Configuration
    HOST = os.getenv("HOST", "0.0.0.0")
    PORT = int(os.getenv("PORT", "8001"))
    DEBUG = False
    RELOAD = False
    WORKERS = int(os.getenv("WORKERS", "4"))
    
    # API Configuration
    API_TITLE = "Jewellery AI Agent - Production"
    API_VERSION = "1.0.0"
    API_DESCRIPTION = "Production-ready AI Agent for Jewellery Management System"
    
    # Logging
    LOG_LEVEL = os.getenv("LOG_LEVEL", "INFO")
    LOG_FILE = os.getenv("LOG_FILE", "logs/ai_agent.log")
    
    # Timeouts
    REQUEST_TIMEOUT = int(os.getenv("REQUEST_TIMEOUT", "30"))
    GROQ_TIMEOUT = int(os.getenv("GROQ_TIMEOUT", "60"))
    
    # Rate Limiting
    RATE_LIMIT_ENABLED = os.getenv("RATE_LIMIT_ENABLED", "true").lower() == "true"
    RATE_LIMIT_REQUESTS = int(os.getenv("RATE_LIMIT_REQUESTS", "100"))
    RATE_LIMIT_PERIOD = int(os.getenv("RATE_LIMIT_PERIOD", "60"))
    
    # API Keys
    GROQ_API_KEY = os.getenv("GROQ_API_KEY", "")
    GOOGLE_APPLICATION_CREDENTIALS = os.getenv("GOOGLE_APPLICATION_CREDENTIALS", "")
    
    # Languages
    SUPPORTED_LANGUAGES = ["ur", "en", "hi", "pa"]
    DEFAULT_LANGUAGE = os.getenv("DEFAULT_LANGUAGE", "ur")
    
    # Audio Settings
    AUDIO_SAMPLE_RATE = int(os.getenv("AUDIO_SAMPLE_RATE", "16000"))
    AUDIO_ENCODING = os.getenv("AUDIO_ENCODING", "LINEAR16")
    MAX_AUDIO_SIZE = int(os.getenv("MAX_AUDIO_SIZE", "52428800"))  # 50MB
    
    # Storage
    UPLOAD_DIR = os.getenv("UPLOAD_DIR", "uploads")
    VOICE_OUTPUT_DIR = os.getenv("VOICE_OUTPUT_DIR", "voice_outputs")
    
    # Laravel Backend
    LARAVEL_API_URL = os.getenv("LARAVEL_API_URL", "http://localhost:8000/api")
    LARAVEL_API_KEY = os.getenv("LARAVEL_API_KEY", "")
    
    # Cache Settings
    CACHE_ENABLED = os.getenv("CACHE_ENABLED", "true").lower() == "true"
    CACHE_TTL = int(os.getenv("CACHE_TTL", "3600"))
    
    # Error Handling
    DETAILED_ERRORS = False
    ERROR_LOG_FILE = os.getenv("ERROR_LOG_FILE", "logs/errors.log")
    
    @classmethod
    def validate(cls):
        """Validate production settings"""
        errors = []
        
        if not cls.GROQ_API_KEY:
            errors.append("GROQ_API_KEY is not set")
        
        if not os.path.exists(cls.UPLOAD_DIR):
            os.makedirs(cls.UPLOAD_DIR, exist_ok=True)
        
        if not os.path.exists(cls.VOICE_OUTPUT_DIR):
            os.makedirs(cls.VOICE_OUTPUT_DIR, exist_ok=True)
        
        if not os.path.exists("logs"):
            os.makedirs("logs", exist_ok=True)
        
        return errors

production_settings = ProductionSettings()
