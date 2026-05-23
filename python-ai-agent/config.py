"""
MAGIA LUPOS - Production AI Agent Configuration
Centralized configuration for all AI agent services
"""

import os
from dotenv import load_dotenv

load_dotenv()


class Settings:
    # ==================== SERVER CONFIG ====================
    APP_NAME = os.getenv("APP_NAME", "MAGIA AI Agent")
    APP_VERSION = os.getenv("APP_VERSION", "5.0.0")
    DEBUG = os.getenv("DEBUG", "false").lower() == "true"

    # Host & Port
    HOST = os.getenv("HOST", "0.0.0.0")
    PORT = int(os.getenv("PORT", "8001"))
    WORKERS = int(os.getenv("WORKERS", "4"))
    RELOAD = os.getenv("RELOAD", "false").lower() == "true"

    # ==================== AI PROVIDER CONFIG ====================
    # Options: 'openai', 'groq', 'gemini', 'auto'
    AI_PROVIDER = os.getenv("AI_PROVIDER", "auto")
    AI_MODEL = os.getenv("AI_MODEL", "auto")

    # API Keys
    OPENAI_API_KEY = os.getenv("OPENAI_API_KEY", "")
    GROQ_API_KEY = os.getenv("GROQ_API_KEY", "")
    GEMINI_API_KEY = os.getenv("GEMINI_API_KEY", "")

    # AI Settings
    TEMPERATURE = float(os.getenv("TEMPERATURE", "0.7"))
    MAX_TOKENS = int(os.getenv("MAX_TOKENS", "4000"))
    TOP_P = float(os.getenv("TOP_P", "0.9"))
    FREQUENCY_PENALTY = float(os.getenv("FREQUENCY_PENALTY", "0.1"))
    PRESENCE_PENALTY = float(os.getenv("PRESENCE_PENALTY", "0.1"))

    # ==================== DATABASE CONFIG ====================
    DB_HOST = os.getenv("DB_HOST", "127.0.0.1")
    DB_PORT = int(os.getenv("DB_PORT", "3306"))
    DB_USER = os.getenv("DB_USER", "root")
    DB_PASSWORD = os.getenv("DB_PASSWORD", "")
    DB_NAME = os.getenv("DB_NAME", "jewllery1")

    # ==================== REDIS / CACHE CONFIG ====================
    REDIS_HOST = os.getenv("REDIS_HOST", "127.0.0.1")
    REDIS_PORT = int(os.getenv("REDIS_PORT", "6379"))
    REDIS_DB = int(os.getenv("REDIS_DB", "0"))
    REDIS_PASSWORD = os.getenv("REDIS_PASSWORD", "")
    CACHE_ENABLED = os.getenv("CACHE_ENABLED", "true").lower() == "true"
    CACHE_TTL = int(os.getenv("CACHE_TTL", "3600"))

    # ==================== RATE LIMITING ====================
    RATE_LIMIT_ENABLED = os.getenv("RATE_LIMIT_ENABLED", "true").lower() == "true"
    RATE_LIMIT_PER_MINUTE = int(os.getenv("RATE_LIMIT_PER_MINUTE", "60"))
    RATE_LIMIT_PER_HOUR = int(os.getenv("RATE_LIMIT_PER_HOUR", "500"))

    # ==================== CONVERSATION CONFIG ====================
    MAX_CONVERSATION_HISTORY = int(os.getenv("MAX_HISTORY", "50"))
    CONVERSATION_TIMEOUT = int(os.getenv("CONV_TIMEOUT", "3600"))
    MAX_CONTEXT_TOKENS = int(os.getenv("MAX_CONTEXT_TOKENS", "3000"))

    # ==================== STREAMING CONFIG ====================
    STREAM_ENABLED = os.getenv("STREAM_ENABLED", "true").lower() == "true"
    STREAM_CHUNK_DELAY = float(os.getenv("STREAM_CHUNK_DELAY", "0.02"))

    # ==================== LANGUAGE CONFIG ====================
    SUPPORTED_LANGUAGES = ["ur", "en", "hi", "pa"]
    DEFAULT_LANGUAGE = os.getenv("DEFAULT_LANGUAGE", "en")

    LANGUAGE_NAMES = {
        "ur": "اردو",
        "en": "English",
        "hi": "हिंदी",
        "pa": "ਪੰਜਾਬੀ"
    }

    LANGUAGE_FLAGS = {
        "ur": "🇵🇰",
        "en": "🇬🇧",
        "hi": "🇮🇳",
        "pa": "🇵🇰"
    }

    # ==================== AUDIO CONFIG ====================
    AUDIO_SAMPLE_RATE = int(os.getenv("AUDIO_SAMPLE_RATE", "16000"))
    AUDIO_ENCODING = os.getenv("AUDIO_ENCODING", "LINEAR16")
    MAX_AUDIO_SIZE = int(os.getenv("MAX_AUDIO_SIZE", "52428800"))  # 50MB

    # ==================== STORAGE CONFIG ====================
    UPLOAD_DIR = os.getenv("UPLOAD_DIR", "uploads")
    VOICE_OUTPUT_DIR = os.getenv("VOICE_OUTPUT_DIR", "voice_outputs")
    LOG_DIR = os.getenv("LOG_DIR", "logs")
    EXPORT_DIR = os.getenv("EXPORT_DIR", "exports")

    # ==================== LOGGING CONFIG ====================
    LOG_LEVEL = os.getenv("LOG_LEVEL", "INFO")
    LOG_FILE = os.getenv("LOG_FILE", "logs/ai_agent.log")
    LOG_FORMAT = os.getenv("LOG_FORMAT", "%(asctime)s [%(levelname)s] %(name)s: %(message)s")

    # ==================== LARAVEL BACKEND ====================
    LARAVEL_API_URL = os.getenv("LARAVEL_API_URL", "http://localhost:8000/api")
    LARAVEL_API_KEY = os.getenv("LARAVEL_API_KEY", "")

    # ==================== SECURITY ====================
    ALLOWED_ORIGINS = os.getenv("ALLOWED_ORIGINS", "*").split(",")
    API_SECRET = os.getenv("API_SECRET", "")
    ENABLE_HTTPS = os.getenv("ENABLE_HTTPS", "false").lower() == "true"

    # ==================== ADVANCED FEATURES ====================
    ENABLE_MEMORY = os.getenv("ENABLE_MEMORY", "true").lower() == "true"
    ENABLE_TOOL_CALLING = os.getenv("ENABLE_TOOL_CALLING", "true").lower() == "true"
    ENABLE_CODE_EXECUTION = os.getenv("ENABLE_CODE_EXECUTION", "false").lower() == "true"
    ENABLE_DOCUMENT_ANALYSIS = os.getenv("ENABLE_DOCUMENT_ANALYSIS", "true").lower() == "true"

    # ==================== PROVIDER FALLBACK ORDER ====================
    PROVIDER_FALLBACK_ORDER = os.getenv(
        "PROVIDER_FALLBACK_ORDER",
        "openai,groq,gemini"
    ).split(",")

    @classmethod
    def validate(cls):
        """Validate configuration and return list of warnings"""
        warnings = []

        if not cls.OPENAI_API_KEY and not cls.GROQ_API_KEY and not cls.GEMINI_API_KEY:
            warnings.append("WARNING: No AI API key configured. Agent will have limited functionality.")

        if not cls.DB_PASSWORD:
            warnings.append("WARNING: Database password is empty.")

        if not os.path.exists(cls.UPLOAD_DIR):
            os.makedirs(cls.UPLOAD_DIR, exist_ok=True)

        if not os.path.exists(cls.VOICE_OUTPUT_DIR):
            os.makedirs(cls.VOICE_OUTPUT_DIR, exist_ok=True)

        if not os.path.exists(cls.LOG_DIR):
            os.makedirs(cls.LOG_DIR, exist_ok=True)

        if not os.path.exists(cls.EXPORT_DIR):
            os.makedirs(cls.EXPORT_DIR, exist_ok=True)

        return warnings

    @classmethod
    def get_provider_info(cls):
        """Get current provider information"""
        providers = []
        if cls.OPENAI_API_KEY:
            providers.append({"name": "OpenAI", "key": "***" + cls.OPENAI_API_KEY[-4:], "status": "configured"})
        else:
            providers.append({"name": "OpenAI", "key": None, "status": "not configured"})

        if cls.GROQ_API_KEY:
            providers.append({"name": "Groq", "key": "***" + cls.GROQ_API_KEY[-4:], "status": "configured"})
        else:
            providers.append({"name": "Groq", "key": None, "status": "not configured"})

        if cls.GEMINI_API_KEY:
            providers.append({"name": "Gemini", "key": "***" + cls.GEMINI_API_KEY[-4:], "status": "configured"})
        else:
            providers.append({"name": "Gemini", "key": None, "status": "not configured"})

        return providers


settings = Settings()