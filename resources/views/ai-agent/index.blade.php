@extends('layouts.app')

@section('title', 'AI Agent - Voice & Chat')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <h1 class="h2 mb-1 text-dark fw-800">AI Agent Assistant</h1>
            <p class="text-muted">Multi-language voice and chat automation</p>
        </div>
        <div class="col-md-6 text-md-end">
            <span class="badge bg-success-soft text-success px-3 py-2 rounded-pill fw-700" id="agentStatus">
                <i class="fas fa-circle me-2"></i> Checking...
            </span>
        </div>
    </div>

    <div class="row">
        <!-- Chat Interface -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-premium rounded-20 overflow-hidden">
                <div class="card-header bg-white border-0 p-4">
                    <div class="d-flex align-items-center">
                        <div class="p-2 bg-primary-soft text-primary rounded-10 me-3">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h5 class="mb-0 fw-800">Chat Assistant</h5>
                    </div>
                </div>
                <div class="card-body p-4">
                    <!-- Chat Messages -->
                    <div id="chatMessages" class="mb-4" style="height: 400px; overflow-y: auto; border: 1px solid #e0e0e0; border-radius: 12px; padding: 15px;">
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-comments fa-3x opacity-50 mb-3"></i>
                            <p>Start a conversation...</p>
                        </div>
                    </div>

                    <!-- Input -->
                    <div class="input-group">
                        <input type="text" id="chatInput" class="form-control rounded-start-12" placeholder="Type your message..." />
                        <button class="btn btn-primary rounded-end-12" id="sendBtn" type="button">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>

                    <!-- Language Selection -->
                    <div class="mt-3">
                        <label class="small text-muted">Language:</label>
                        <select id="chatLanguage" class="form-select form-select-sm">
                            <option value="ur">Urdu (اردو)</option>
                            <option value="en">English</option>
                            <option value="hi">Hindi (हिंदी)</option>
                            <option value="pa">Punjabi (ਪੰਜਾਬੀ)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Voice Interface -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-premium rounded-20 overflow-hidden">
                <div class="card-header bg-white border-0 p-4">
                    <div class="d-flex align-items-center">
                        <div class="p-2 bg-success-soft text-success rounded-10 me-3">
                            <i class="fas fa-microphone"></i>
                        </div>
                        <h5 class="mb-0 fw-800">Voice Assistant</h5>
                    </div>
                </div>
                <div class="card-body p-4">
                    <!-- Voice Status -->
                    <div class="text-center mb-4">
                        <div id="voiceStatus" class="badge bg-light text-dark px-3 py-2 rounded-pill fw-700">
                            <i class="fas fa-circle-notch fa-spin me-2"></i> Ready
                        </div>
                    </div>

                    <!-- Voice Buttons -->
                    <div class="d-grid gap-2 mb-4">
                        <button class="btn btn-lg btn-success rounded-12" id="recordBtn">
                            <i class="fas fa-microphone me-2"></i> Start Recording
                        </button>
                        <button class="btn btn-lg btn-danger rounded-12 d-none" id="stopBtn">
                            <i class="fas fa-stop-circle me-2"></i> Stop Recording
                        </button>
                    </div>

                    <!-- Transcription Display -->
                    <div id="transcription" class="alert alert-info rounded-12 d-none">
                        <strong>Transcribed:</strong>
                        <p id="transcriptionText" class="mb-0 mt-2"></p>
                    </div>

                    <!-- Response Audio -->
                    <div id="responseAudio" class="alert alert-success rounded-12 d-none">
                        <strong>Response:</strong>
                        <audio id="audioPlayer" controls class="w-100 mt-2"></audio>
                    </div>

                    <!-- Language Selection -->
                    <div class="mt-3">
                        <label class="small text-muted">Language:</label>
                        <select id="voiceLanguage" class="form-select form-select-sm">
                            <option value="ur">Urdu (اردو)</option>
                            <option value="en">English</option>
                            <option value="hi">Hindi (हिंदी)</option>
                            <option value="pa">Punjabi (ਪੰਜਾਬੀ)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Full Automation -->
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card border-0 shadow-premium rounded-20 overflow-hidden">
                <div class="card-header bg-white border-0 p-4">
                    <div class="d-flex align-items-center">
                        <div class="p-2 bg-warning-soft text-warning rounded-10 me-3">
                            <i class="fas fa-magic"></i>
                        </div>
                        <h5 class="mb-0 fw-800">Full Automation (Voice → Chat → Voice)</h5>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label fw-700">Upload Audio File</label>
                            <input type="file" id="automationFile" class="form-control rounded-12" accept="audio/*" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-700">Language</label>
                            <select id="automationLanguage" class="form-select rounded-12">
                                <option value="ur">Urdu (اردو)</option>
                                <option value="en">English</option>
                                <option value="hi">Hindi (हिंदی)</option>
                                <option value="pa">Punjabi (ਪੰਜਾਬੀ)</option>
                            </select>
                        </div>
                    </div>
                    <button class="btn btn-lg btn-warning rounded-12 w-100 mt-3" id="automationBtn">
                        <i class="fas fa-play me-2"></i> Process Voice
                    </button>

                    <!-- Automation Result -->
                    <div id="automationResult" class="alert alert-info rounded-12 d-none mt-3">
                        <h6>Processing Result:</h6>
                        <p><strong>Input:</strong> <span id="automationInput"></span></p>
                        <p><strong>Response:</strong> <span id="automationResponse"></span></p>
                        <audio id="automationAudio" controls class="w-100 mt-2"></audio>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const API_URL = '{{ env("PYTHON_AI_AGENT_URL", "http://localhost:8000/api") }}';
    let mediaRecorder;
    let audioChunks = [];

    // Check Agent Status
    async function checkAgentStatus() {
        try {
            const response = await fetch(`${API_URL}/info/status`);
            const data = await response.json();
            if (data.status === 'success') {
                document.getElementById('agentStatus').innerHTML = '<i class="fas fa-circle me-2"></i> Online';
                document.getElementById('agentStatus').classList.remove('bg-danger-soft', 'text-danger');
                document.getElementById('agentStatus').classList.add('bg-success-soft', 'text-success');
            }
        } catch (error) {
            console.error('Agent status check failed:', error);
            document.getElementById('agentStatus').innerHTML = '<i class="fas fa-circle me-2"></i> Offline';
            document.getElementById('agentStatus').classList.remove('bg-success-soft', 'text-success');
            document.getElementById('agentStatus').classList.add('bg-danger-soft', 'text-danger');
        }
    }

    // Chat Functionality
    document.getElementById('sendBtn').addEventListener('click', async () => {
        const message = document.getElementById('chatInput').value;
        const language = document.getElementById('chatLanguage').value;

        if (!message.trim()) return;

        // Add user message to chat
        addChatMessage(message, 'user');
        document.getElementById('chatInput').value = '';

        try {
            const response = await fetch(`{{ route('ai-agent.chat') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ message, language })
            });

            const data = await response.json();
            if (data.status === 'success') {
                const botMessage = data.data.answer || data.data.extracted || 'Processing...';
                addChatMessage(botMessage, 'bot');
            } else {
                addChatMessage('Error: ' + data.message, 'error');
            }
        } catch (error) {
            addChatMessage('Connection error', 'error');
        }
    });

    function addChatMessage(message, sender) {
        const chatMessages = document.getElementById('chatMessages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `mb-3 ${sender === 'user' ? 'text-end' : ''}`;
        messageDiv.innerHTML = `
            <div class="d-inline-block px-3 py-2 rounded-12 ${
                sender === 'user' ? 'bg-primary text-white' : 
                sender === 'error' ? 'bg-danger text-white' : 
                'bg-light text-dark'
            }">
                ${message}
            </div>
        `;
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Voice Recording
    document.getElementById('recordBtn').addEventListener('click', async () => {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        mediaRecorder = new MediaRecorder(stream);
        audioChunks = [];

        mediaRecorder.ondataavailable = (event) => {
            audioChunks.push(event.data);
        };

        mediaRecorder.onstop = async () => {
            const audioBlob = new Blob(audioChunks, { type: 'audio/wav' });
            await processVoiceAutomation(audioBlob);
        };

        mediaRecorder.start();
        document.getElementById('recordBtn').classList.add('d-none');
        document.getElementById('stopBtn').classList.remove('d-none');
        document.getElementById('voiceStatus').innerHTML = '<i class="fas fa-circle text-danger me-2"></i> Recording...';
    });

    document.getElementById('stopBtn').addEventListener('click', () => {
        mediaRecorder.stop();
        document.getElementById('recordBtn').classList.remove('d-none');
        document.getElementById('stopBtn').classList.add('d-none');
        document.getElementById('voiceStatus').innerHTML = '<i class="fas fa-circle-notch fa-spin me-2"></i> Processing...';
    });

    // Full Automation
    document.getElementById('automationBtn').addEventListener('click', async () => {
        const file = document.getElementById('automationFile').files[0];
        if (!file) {
            alert('Please select an audio file');
            return;
        }

        const language = document.getElementById('automationLanguage').value;
        const formData = new FormData();
        formData.append('audio', file);
        formData.append('language', language);

        try {
            const response = await fetch(`{{ route('ai-agent.voice-to-voice') }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            });

            const data = await response.json();
            if (data.status === 'success') {
                document.getElementById('automationInput').textContent = data.data.input_transcript;
                document.getElementById('automationResponse').textContent = data.data.response_text;
                document.getElementById('automationAudio').src = data.data.audio_url;
                document.getElementById('automationResult').classList.remove('d-none');
            }
        } catch (error) {
            alert('Error processing audio');
        }
    });

    async function processVoiceAutomation(audioBlob) {
        const language = document.getElementById('voiceLanguage').value;
        const formData = new FormData();
        formData.append('audio', audioBlob);
        formData.append('language', language);

        try {
            const response = await fetch(`{{ route('ai-agent.voice-to-voice') }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            });

            const data = await response.json();
            if (data.status === 'success') {
                document.getElementById('transcriptionText').textContent = data.data.input_transcript;
                document.getElementById('transcription').classList.remove('d-none');
                document.getElementById('audioPlayer').src = data.data.audio_url;
                document.getElementById('responseAudio').classList.remove('d-none');
                document.getElementById('voiceStatus').innerHTML = '<i class="fas fa-circle text-success me-2"></i> Ready';
            }
        } catch (error) {
            document.getElementById('voiceStatus').innerHTML = '<i class="fas fa-circle text-danger me-2"></i> Error';
        }
    }

    // Initialize
    checkAgentStatus();
    setInterval(checkAgentStatus, 30000);
</script>

<style>
    .bg-primary-soft { background-color: rgba(59, 130, 246, 0.1) !important; }
    .bg-success-soft { background-color: rgba(16, 185, 129, 0.1) !important; }
    .bg-warning-soft { background-color: rgba(245, 158, 11, 0.1) !important; }
    .bg-danger-soft { background-color: rgba(239, 68, 68, 0.1) !important; }
    .text-primary { color: #3b82f6 !important; }
    .text-success { color: #10b981 !important; }
    .text-warning { color: #f59e0b !important; }
    .text-danger { color: #ef4444 !important; }
    .rounded-12 { border-radius: 12px !important; }
    .rounded-20 { border-radius: 20px !important; }
    .fw-800 { font-weight: 800 !important; }
    .shadow-premium {
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05), 0 4px 18px -7px rgba(0, 0, 0, 0.05) !important;
    }
</style>
@endpush

@endsection
