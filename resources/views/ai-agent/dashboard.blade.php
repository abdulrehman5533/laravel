@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">🤖 AI Agent - Voice & Chat Assistant</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Chat Interface -->
                        <div class="col-md-6">
                            <h5>💬 Chat Mode</h5>
                            <div id="chatBox" style="height: 400px; border: 1px solid #ddd; border-radius: 5px; padding: 15px; overflow-y: auto; background: #f9f9f9;">
                                <div class="text-center text-muted">
                                    <p>Chat messages will appear here...</p>
                                </div>
                            </div>
                            <div class="input-group mt-3">
                                <input type="text" id="chatInput" class="form-control" placeholder="Type your message...">
                                <button class="btn btn-primary" onclick="sendChat()">Send</button>
                            </div>
                        </div>

                        <!-- Voice Interface -->
                        <div class="col-md-6">
                            <h5>🎤 Voice Mode</h5>
                            <div class="text-center">
                                <button id="voiceBtn" class="btn btn-lg btn-danger mb-3" onclick="toggleVoiceRecording()">
                                    <i class="fas fa-microphone"></i> Start Recording
                                </button>
                                <div id="voiceStatus" class="alert alert-info" style="display:none;">
                                    Recording... <span id="recordingTime">0s</span>
                                </div>
                                <div id="voiceTranscript" class="alert alert-light" style="min-height: 100px; border: 1px solid #ddd;">
                                    <small class="text-muted">Transcript will appear here...</small>
                                </div>
                            </div>
                            <div class="mt-3">
                                <label>Language:</label>
                                <select id="language" class="form-control">
                                    <option value="ur-PK">اردو (Urdu)</option>
                                    <option value="en-US">English (US)</option>
                                    <option value="en-GB">English (UK)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Response Area -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5>📊 Response</h5>
                            <div id="responseBox" class="alert alert-light" style="min-height: 150px; border: 1px solid #ddd; padding: 15px;">
                                <small class="text-muted">Response will appear here...</small>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5>⚡ Quick Actions</h5>
                            <button class="btn btn-outline-primary btn-sm" onclick="quickAction('stock')">📦 Check Stock</button>
                            <button class="btn btn-outline-success btn-sm" onclick="quickAction('employee')">👤 Add Employee</button>
                            <button class="btn btn-outline-info btn-sm" onclick="quickAction('salary')">💰 Calculate Salary</button>
                            <button class="btn btn-outline-warning btn-sm" onclick="quickAction('inventory')">📋 Inventory Check</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let isRecording = false;
let recordingStartTime;

const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
const recognition = new SpeechRecognition();

recognition.onstart = function() {
    document.getElementById('voiceStatus').style.display = 'block';
    recordingStartTime = Date.now();
    updateRecordingTime();
};

recognition.onresult = function(event) {
    let transcript = '';
    for (let i = event.resultIndex; i < event.results.length; i++) {
        transcript += event.results[i][0].transcript;
    }
    document.getElementById('voiceTranscript').innerHTML = `<strong>Transcript:</strong> ${transcript}`;
    processVoiceInput(transcript);
};

recognition.onerror = function(event) {
    showResponse('Voice Error: ' + event.error, 'danger');
};

function toggleVoiceRecording() {
    const btn = document.getElementById('voiceBtn');
    const language = document.getElementById('language').value;
    
    if (!isRecording) {
        recognition.lang = language;
        recognition.start();
        isRecording = true;
        btn.innerHTML = '<i class="fas fa-stop"></i> Stop Recording';
        btn.classList.remove('btn-danger');
        btn.classList.add('btn-warning');
    } else {
        recognition.stop();
        isRecording = false;
        btn.innerHTML = '<i class="fas fa-microphone"></i> Start Recording';
        btn.classList.remove('btn-warning');
        btn.classList.add('btn-danger');
        document.getElementById('voiceStatus').style.display = 'none';
    }
}

function updateRecordingTime() {
    if (isRecording) {
        const elapsed = Math.floor((Date.now() - recordingStartTime) / 1000);
        document.getElementById('recordingTime').textContent = elapsed + 's';
        setTimeout(updateRecordingTime, 1000);
    }
}

function sendChat() {
    const message = document.getElementById('chatInput').value.trim();
    if (!message) return;

    addChatMessage('You', message, 'user');
    document.getElementById('chatInput').value = '';

    processChat(message);
}

function processChat(message) {
    fetch('{{ route("ai-agent.chat") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            message: message,
            language: document.getElementById('language').value
        })
    })
    .then(response => response.json())
    .then(data => {
        addChatMessage('AI Agent', data.message, 'bot');
        showResponse(data, data.status === 'success' ? 'success' : 'warning');
    })
    .catch(error => {
        showResponse('Error: ' + error.message, 'danger');
    });
}

function processVoiceInput(transcript) {
    processChat(transcript);
}

function addChatMessage(sender, message, type) {
    const chatBox = document.getElementById('chatBox');
    const messageDiv = document.createElement('div');
    messageDiv.className = `mb-2 p-2 rounded ${type === 'user' ? 'bg-primary text-white' : 'bg-light'}`;
    messageDiv.innerHTML = `<strong>${sender}:</strong> ${message}`;
    chatBox.appendChild(messageDiv);
    chatBox.scrollTop = chatBox.scrollHeight;
}

function showResponse(data, type = 'info') {
    const responseBox = document.getElementById('responseBox');
    let html = '';
    
    if (typeof data === 'string') {
        html = `<div class="alert alert-${type}">${data}</div>`;
    } else {
        html = `
            <div class="alert alert-${type}">
                <strong>${data.message || 'Response'}</strong>
                ${data.status ? `<br><small>Status: ${data.status}</small>` : ''}
                ${data.data ? `<pre>${JSON.stringify(data.data, null, 2)}</pre>` : ''}
            </div>
        `;
    }
    
    responseBox.innerHTML = html;
}

function quickAction(action) {
    const messages = {
        'stock': 'Mujhe stock check karna hai',
        'employee': 'Naya employee add karo',
        'salary': 'Salary calculate karo',
        'inventory': 'Inventory check karo'
    };
    
    document.getElementById('chatInput').value = messages[action];
    sendChat();
}

document.addEventListener('DOMContentLoaded', function() {
    addChatMessage('AI Agent', 'السلام عليكم! Hello! Main aapka AI Assistant hoon.', 'bot');
});
</script>

<style>
#chatBox {
    max-height: 400px;
}

.alert {
    margin-bottom: 0.5rem;
}

pre {
    background: #f5f5f5;
    padding: 10px;
    border-radius: 3px;
    font-size: 12px;
    max-height: 200px;
    overflow-y: auto;
}
</style>
@endsection
