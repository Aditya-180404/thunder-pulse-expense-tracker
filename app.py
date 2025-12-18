from flask import Flask, render_template, request, jsonify
from datetime import datetime
import google.generativeai as genai
from flask_cors import CORS

app = Flask(__name__)
CORS(app)

# 🔑 Configure Gemini API Key
genai.configure(api_key="AIzaSyCWkoJPlh3RHhhsqkTdmSPjWAQdEEMcxsc")

# ✅ Free-tier friendly model
model = genai.GenerativeModel("models/gemini-flash-lite-latest")
chat = model.start_chat(history=[])

# Keywords for exiting the chat
exit_keywords = ["exit", "break", "close"]

@app.route("/")
def index():
    return render_template("index.html")


@app.route("/chat", methods=["POST"])
def chat_response():
    user_message = request.json.get("message", "").strip()

    if not user_message:
        return jsonify({"reply": "Please type a message."})

    # Exit keywords
    if user_message.lower() in exit_keywords:
        return jsonify({"reply": "Goodbye! 👋 Thanks for chatting with us."})

    # Developer info response
    if "who developed" in user_message.lower() or "who made you" in user_message.lower():
        return jsonify({
            "reply": "I was developed by Google and our awesome team THUNDER PLUS!"
        })

    try:
        # Send message to Gemini
        response = chat.send_message(user_message)

        # Timestamp
        timestamp = datetime.now().strftime("%H:%M:%S")
        reply_with_time = f"[{timestamp}] {response.text}"

        return jsonify({"reply": reply_with_time})

    except Exception as e:
        return jsonify({"reply": f"Error: {str(e)}"})


if __name__ == "__main__":
    app.run(debug=True)
