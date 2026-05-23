from typing import Any, Dict, Optional

class ResponseFormatter:
    @staticmethod
    def success(data: Any = None, message: str = "Success", status_code: int = 200) -> Dict:
        """Format success response"""
        return {
            "status": "success",
            "code": status_code,
            "message": message,
            "data": data
        }
    
    @staticmethod
    def error(message: str, error_code: str = "ERROR", status_code: int = 400, details: Optional[Dict] = None) -> Dict:
        """Format error response"""
        return {
            "status": "error",
            "code": status_code,
            "error_code": error_code,
            "message": message,
            "details": details or {}
        }
    
    @staticmethod
    def processing(message: str = "Processing...", progress: int = 0) -> Dict:
        """Format processing response"""
        return {
            "status": "processing",
            "message": message,
            "progress": progress
        }
    
    @staticmethod
    def clarification_needed(fields: list, message: str = "More information needed") -> Dict:
        """Format clarification needed response"""
        return {
            "status": "clarification_needed",
            "message": message,
            "required_fields": fields
        }
