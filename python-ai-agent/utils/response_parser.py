import re
import json
from typing import Optional, Dict

class ResponseParser:
    @staticmethod
    def extract_json(text: str) -> Optional[Dict]:
        """Attempt to extract a JSON object from free-text AI responses.
        Tries multiple strategies:
        - find first {...} block
        - strip markdown fences and try again
        - replace single quotes with double if safe
        - try to locate matching braces
        Returns parsed dict or None.
        """
        if not text:
            return None

        # Remove markdown code fences
        cleaned = re.sub(r'```[\s\S]*?```', lambda m: m.group(0).strip('`'), text)

        # Try to find the largest JSON-like block using braces matching
        brace_positions = []
        stack = []
        for i, ch in enumerate(cleaned):
            if ch == '{':
                stack.append(i)
            elif ch == '}' and stack:
                start = stack.pop()
                if not stack:
                    # top-level block
                    brace_positions.append((start, i))

        # Try blocks from largest to smallest
        for start, end in reversed(brace_positions):
            candidate = cleaned[start:end+1]
            try:
                return json.loads(candidate)
            except Exception:
                # try fixing single quotes
                cand2 = candidate.replace("'", '"')
                try:
                    return json.loads(cand2)
                except Exception:
                    continue

        # Fallback: regex that matches a JSON object
        m = re.search(r'\{[\s\S]*\}', cleaned)
        if m:
            candidate = m.group()
            try:
                return json.loads(candidate)
            except Exception:
                try:
                    return json.loads(candidate.replace("'", '"'))
                except Exception:
                    return None

        return None
