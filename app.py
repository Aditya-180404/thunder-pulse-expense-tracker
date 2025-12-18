from flask import Flask, request, jsonify
from flask_cors import CORS
from datetime import datetime
import google.generativeai as genai
import os
from dotenv import load_dotenv

# ---------------- LOAD ENV ----------------
load_dotenv()

# ---------------- APP ----------------
app = Flask(__name__)
CORS(app)

# ---------------- GEMINI CONFIG ----------------
genai.configure(api_key=os.getenv("AIzaSyDk98ikm0rfjqSb69DrUZFrO_Z-TDfmUT0"))
model = genai.GenerativeModel("models/gemini-flash-lite-latest")

# ---------------- SYSTEM PROMPT ----------------
SYSTEM_PROMPT = """
You are an AI Expense Tracker Assistant.

You must ONLY help with:
- Expense tracking
- Budget planning
- Saving money
- Reducing unnecessary expenses
- Financial discipline

STRICT RULES:
- Do NOT answer questions unrelated to money, expenses, or budgeting.
- Do NOT explain why you refuse.
- Do NOT answer general knowledge, programming, politics, or entertainment.

If the question is unrelated, reply exactly with:
"I'm designed only to assist with expense tracking and saving money. Please ask a finance-related question."
"""

# ---------------- HARD KEYWORD FILTER ----------------
ALLOWED_KEYWORDS = [
    "expense","expenses","money","budget","save","saving",
    "spend","spent","spending","income","cost","rent",
    "food","shopping","emi","loan","bill","electricity",
    "gas","salary","allowance","balance"
]

# ---------------- ROUTE ----------------
@app.route("/chat", methods=["POST"])
def chat():
    data = request.json

    message = data.get("message", "").strip()
    context = data.get("context", {})

    if not message:
        return jsonify({"reply": "Please enter a message."})

    # HARD BLOCK (double protection)
    if not any(word in message.lower() for word in ALLOWED_KEYWORDS):
        return jsonify({
            "reply": "I'm designed only to assist with expense tracking and saving money. Please ask a finance-related question."
        })

    try:
        chat_session = model.start_chat(history=[
            {"role": "system", "parts": [SYSTEM_PROMPT]}
        ])

        # Inject real expense data
        context_prompt = f"""
User Financial Summary:
Monthly Allowance: ₹{context.get('allowance')}
Total Spent This Month: ₹{context.get('spent')}
Remaining Balance: ₹{context.get('remaining')}
"""

        final_prompt = context_prompt + "\nUser Question: " + message

        response = chat_session.send_message(final_prompt)
        time = datetime.now().strftime("%H:%M")

        return jsonify({
            "reply": f"[{time}] {response.text}"
        })

    except Exception:
        return jsonify({
            "reply": "AI service is temporarily unavailable."
        }), 500


if __name__ == "__main__":
    app.run(debug=True)
