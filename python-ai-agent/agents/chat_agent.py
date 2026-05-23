import re
import requests
from groq import Groq
from config import settings
from utils.language_detector import LanguageDetector
from utils.response_formatter import ResponseFormatter

class ChatAgent:
    def __init__(self):
        if settings.GROQ_API_KEY:
            self.groq_client = Groq(api_key=settings.GROQ_API_KEY)
        else:
            self.groq_client = None

        # Initialize OpenAI client if configured
        from os import getenv
        openai_key = getenv('OPENAI_API_KEY')
        self.openai_client = None
        if openai_key:
            try:
                from openai import OpenAI as OpenAIClient
                self.openai_client = OpenAIClient(api_key=openai_key)
            except Exception:
                self.openai_client = None
        self.language_detector = LanguageDetector()
        self.laravel_url = settings.LARAVEL_API_URL
    
    async def process_message(self, message: str, language: str = None, context: dict = None) -> dict:
        """Process user message and generate response"""
        try:
            if not language:
                language = self.language_detector.detect_language(message)
            
            intent = self._detect_intent(message)
            
            response = await self._handle_intent(intent, message, language, context or {})
            
            return response
        
        except Exception as e:
            return ResponseFormatter.error(
                f"Message processing failed: {str(e)}",
                error_code="PROCESSING_ERROR",
                status_code=500
            )
    
    def _detect_intent(self, message: str) -> str:
        """Detect user intent from message"""
        message_lower = message.lower()
        
        intents = {
            'stock': r'(stock|inventory|aaya|received|add|update|quantity|kitna)',
            'employee': r'(employee|worker|staff|naya|new|hire|join)',
            'salary': r'(salary|wage|pay|compensation|increment|bonus)',
            'check': r'(check|kitna|how much|available|status)',
            'general': r'.*'
        }
        
        for intent, pattern in intents.items():
            if re.search(pattern, message_lower, re.IGNORECASE):
                return intent
        
        return 'general'
    
    async def _handle_intent(self, intent: str, message: str, language: str, context: dict) -> dict:
        """Handle specific intent"""
        
        if intent == 'stock':
            return await self._handle_stock_query(message, language, context)
        elif intent == 'employee':
            return await self._handle_employee_query(message, language, context)
        elif intent == 'salary':
            return await self._handle_salary_query(message, language, context)
        elif intent == 'check':
            return await self._handle_check_query(message, language, context)
        else:
            return await self._handle_general_query(message, language)
    
    async def _handle_stock_query(self, message: str, language: str, context: dict) -> dict:
        """Handle stock-related queries"""
        try:
            response = self.groq_client.chat.completions.create(
                model="mixtral-8x7b-32768",
                messages=[
                    {
                        "role": "system",
                        "content": "You are a jewellery inventory assistant. Extract product name and quantity from user message. Respond in JSON format: {\"product_name\": \"\", \"quantity\": 0, \"action\": \"add/check/update\"}"
                    },
                    {"role": "user", "content": message}
                ],
                max_tokens=200
            )
            
            extracted = response.choices[0].message.content
            
            return ResponseFormatter.success(
                data={"extracted": extracted, "intent": "stock"},
                message="Stock query processed"
            )
        
        except Exception as e:
            return ResponseFormatter.error(
                f"Stock query failed: {str(e)}",
                error_code="STOCK_QUERY_ERROR",
                status_code=500
            )
    
    async def _handle_employee_query(self, message: str, language: str, context: dict) -> dict:
        """Handle employee-related queries"""
        try:
            response = self.groq_client.chat.completions.create(
                model="mixtral-8x7b-32768",
                messages=[
                    {
                        "role": "system",
                        "content": "You are an HR assistant. Extract employee details from user message. Respond in JSON format: {\"name\": \"\", \"position\": \"\", \"salary\": 0, \"phone\": \"\"}"
                    },
                    {"role": "user", "content": message}
                ],
                max_tokens=200
            )
            
            extracted = response.choices[0].message.content
            
            return ResponseFormatter.success(
                data={"extracted": extracted, "intent": "employee"},
                message="Employee query processed"
            )
        
        except Exception as e:
            return ResponseFormatter.error(
                f"Employee query failed: {str(e)}",
                error_code="EMPLOYEE_QUERY_ERROR",
                status_code=500
            )
    
    async def _handle_salary_query(self, message: str, language: str, context: dict) -> dict:
        """Handle salary-related queries"""
        try:
            response = self.groq_client.chat.completions.create(
                model="mixtral-8x7b-32768",
                messages=[
                    {
                        "role": "system",
                        "content": "You are a payroll assistant. Extract salary details from user message. Respond in JSON format: {\"employee_name\": \"\", \"base_salary\": 0, \"bonus\": 0}"
                    },
                    {"role": "user", "content": message}
                ],
                max_tokens=200
            )
            
            extracted = response.choices[0].message.content
            
            return ResponseFormatter.success(
                data={"extracted": extracted, "intent": "salary"},
                message="Salary query processed"
            )
        
        except Exception as e:
            return ResponseFormatter.error(
                f"Salary query failed: {str(e)}",
                error_code="SALARY_QUERY_ERROR",
                status_code=500
            )
    
    async def _handle_check_query(self, message: str, language: str, context: dict) -> dict:
        """Handle check/status queries"""
        try:
            response = self.groq_client.chat.completions.create(
                model="mixtral-8x7b-32768",
                messages=[
                    {
                        "role": "system",
                        "content": "You are a business intelligence assistant. Answer the user's query about business metrics. Keep response concise."
                    },
                    {"role": "user", "content": message}
                ],
                max_tokens=300
            )
            
            answer = response.choices[0].message.content
            
            return ResponseFormatter.success(
                data={"answer": answer, "intent": "check"},
                message="Query answered"
            )
        
        except Exception as e:
            return ResponseFormatter.error(
                f"Check query failed: {str(e)}",
                error_code="CHECK_QUERY_ERROR",
                status_code=500
            )
    
    async def _handle_general_query(self, message: str, language: str) -> dict:
        """Handle general queries with AI"""
        try:
            response = self.groq_client.chat.completions.create(
                model="mixtral-8x7b-32768",
                messages=[
                    {
                        "role": "system",
                        "content": f"You are a helpful jewellery management system assistant. Answer in {language} language. Keep responses short and actionable."
                    },
                    {"role": "user", "content": message}
                ],
                max_tokens=500
            )
            
            answer = response.choices[0].message.content
            
            return ResponseFormatter.success(
                data={"answer": answer, "intent": "general"},
                message="Query answered"
            )
        
        except Exception as e:
            return ResponseFormatter.error(
                f"General query failed: {str(e)}",
                error_code="GENERAL_QUERY_ERROR",
                status_code=500
            )
