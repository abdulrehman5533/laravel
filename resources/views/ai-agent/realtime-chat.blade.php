@extends('layouts.app')

@section('content')
<div class="container-fluid h-100">
    <div class="row h-100">
        <!-- Sidebar -->
        <div class="col-lg-3 border-end bg-light" style="max-height: 90vh; overflow-y: auto;">
            <div class="p-3">
                <div class="d-flex align-items-center mb-4">
                    <h5 class="mb-0">⚡ Real-time AI</h5>
                    <span class="badge bg-success ms-2">LIVE</span>
                </div>

                <div class="list-group mb-4">
                    <button class="list-group-item list-group-item-action" onclick="startNewChat()">
                        <i class="fas fa-plus me-2"></i> نیا چیٹ
                    </button>
                    <button class="list-group-item list-group-item-action" onclick="showCommands()">
                        <i class="fas fa-book me-2"></i> کمانڈز
                    </button>
                    <button class="list-group-item list-group-item-action" onclick="showStatus()">
                        <i class="fas fa-info-circle me-2"></i> اسٹیٹس
                    </button>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">💡 مثالیں</h6>
                        <small class="text-muted d-block mb-2">
                            "نیا ملازم علی شامل کریں، سیلری 50000"
                        </small>
                        <small class="text-muted d-block mb-2">
                            "سونے کی انگوٹھی شامل کریں، وزن 10، قیمت 50000"
                        </small>
                        <small class="text-muted d-block">
                            "احمد کو کسٹمر کے طور پر شامل کریں"
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Area -->
        <div class="col-lg-9 d-flex flex-column" style="max-height: 90vh;">
            <!-- Messages Container -->
            <div id="chatMessages" class="flex-grow-1 overflow-auto p-4" style="background: #f8f9fa;">
                <div class="text-center text-muted mb-4">
                    <h6>السلام علیکم! 👋</h6>
                    <p class="mb-0">میں آپ کی مدد کے لیے یہاں ہوں۔</p>
                    <small>براہ کرم اپنا سوال یا کمانڈ درج کریں</small>
                </div>
            </div>

            <!-- Input Area -->
            <div class="p-3 border-top bg-white">
                <div class="input-group mb-2">
                    <input 
                        type="text" 
                        id="messageInput" 
                        class="form-control" 
                        placeholder="اپنا پیغام درج کریں..." 
                        autocomplete="off"
                    />
                    <button class="btn btn-primary" onclick="sendMessage()" id="sendBtn">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
                <small class="text-muted">
                    <i class="fas fa-lightbulb me-1"></i> Enter دبائیں یا بھیجیں بٹن دبائیں
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Commands Modal -->
<div class="modal fade" id="commandsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">📚 دستیاب کمانڈز</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="commandsList">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">لوڈ ہو رہا ہے...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .message {
        margin-bottom: 15px;
        animation: slideIn 0.3s ease-in;
        display: flex;
        align-items: flex-end;
        gap: 10px;
    }

    .message.user {
        justify-content: flex-end;
    }

    .message.ai {
        justify-content: flex-start;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes blink {
        0%, 49% {
            opacity: 1;
        }
        50%, 100% {
            opacity: 0;
        }
    }

    .message-content {
        max-width: 70%;
        padding: 12px 16px;
        border-radius: 12px;
        word-wrap: break-word;
        line-height: 1.5;
    }

    .user .message-content {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-bottom-right-radius: 4px;
    }

    .ai .message-content {
        background: #e9ecef;
        color: #333;
        border-bottom-left-radius: 4px;
    }

    .ai .message-content.error {
        background: #f8d7da;
        color: #721c24;
    }

    .ai .message-content.success {
        background: #d4edda;
        color: #155724;
    }

    .cursor {
        display: inline-block;
        width: 2px;
        height: 1em;
        background-color: #333;
        margin-left: 2px;
        animation: blink 1s infinite;
    }

    .ai .message-content .cursor {
        background-color: #333;
    }

    .timestamp {
        font-size: 0.75rem;
        opacity: 0.7;
        margin-top: 4px;
    }

    #chatMessages {
        direction: rtl;
    }

    .message-content {
        direction: rtl;
        text-align: right;
    }

    .list-group-item {
        direction: rtl;
        text-align: right;
    }

    .streaming {
        animation: pulse 1s infinite;
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.7;
        }
    }
</style>

<script>
    let isLoading = false;
    let eventSource = null;

    function sendMessage() {
        const message = document.getElementById('messageInput').value.trim();
        if (!message || isLoading) return;

        isLoading = true;
        document.getElementById('sendBtn').disabled = true;

        // Add user message
        addMessage(message, 'user');
        document.getElementById('messageInput').value = '';

        // Create streaming message container
        const streamingMessageDiv = createStreamingMessage();

        // Connect to SSE stream
        eventSource = new EventSource(
            `{{ route("realtime-ai.stream") }}?message=${encodeURIComponent(message)}&language=ur`
        );

        let fullResponse = '';

        eventSource.addEventListener('message', function(event) {
            const data = JSON.parse(event.data);

            if (data.type === 'chunk') {
                fullResponse += data.content;
                updateStreamingMessage(streamingMessageDiv, fullResponse);
            } else if (data.type === 'done') {
                eventSource.close();
                
                // Remove streaming indicator
                const cursor = streamingMessageDiv.querySelector('.cursor');
                if (cursor) cursor.remove();
                
                // Add success indicator if action was taken
                if (data.action_taken) {
                    streamingMessageDiv.classList.add('success');
                }
                
                isLoading = false;
                document.getElementById('sendBtn').disabled = false;
                document.getElementById('messageInput').focus();
            } else if (data.type === 'error') {
                eventSource.close();
                streamingMessageDiv.classList.add('error');
                updateStreamingMessage(streamingMessageDiv, data.content);
                
                isLoading = false;
                document.getElementById('sendBtn').disabled = false;
            }
        });

        eventSource.addEventListener('error', function() {
            eventSource.close();
            updateStreamingMessage(streamingMessageDiv, 'کنکشن میں خرابی ہے۔');
            streamingMessageDiv.classList.add('error');
            
            isLoading = false;
            document.getElementById('sendBtn').disabled = false;
        });
    }

    function createStreamingMessage() {
        const messagesDiv = document.getElementById('chatMessages');
        const messageDiv = document.createElement('div');
        messageDiv.className = 'message ai streaming';
        
        const contentDiv = document.createElement('div');
        contentDiv.className = 'message-content';
        contentDiv.innerHTML = '<span class="cursor"></span>';
        
        messageDiv.appendChild(contentDiv);
        messagesDiv.appendChild(messageDiv);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
        
        return messageDiv;
    }

    function updateStreamingMessage(messageDiv, text) {
        const contentDiv = messageDiv.querySelector('.message-content');
        contentDiv.textContent = text;
        
        // Add cursor if still streaming
        if (!messageDiv.classList.contains('success') && !messageDiv.classList.contains('error')) {
            const cursor = document.createElement('span');
            cursor.className = 'cursor';
            contentDiv.appendChild(cursor);
        }
        
        // Auto scroll
        const messagesDiv = document.getElementById('chatMessages');
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    function addMessage(text, sender, className = '') {
        const messagesDiv = document.getElementById('chatMessages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}`;
        
        const contentDiv = document.createElement('div');
        contentDiv.className = `message-content ${className}`;
        contentDiv.textContent = text;
        
        const timeDiv = document.createElement('div');
        timeDiv.className = 'timestamp';
        timeDiv.textContent = new Date().toLocaleTimeString('ur-PK', { hour: '2-digit', minute: '2-digit' });
        
        messageDiv.appendChild(contentDiv);
        messageDiv.appendChild(timeDiv);
        messagesDiv.appendChild(messageDiv);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    function showCommands() {
        const modal = new bootstrap.Modal(document.getElementById('commandsModal'));
        const commandsList = document.getElementById('commandsList');
        commandsList.innerHTML = '<div class="spinner-border" role="status"><span class="visually-hidden">لوڈ ہو رہا ہے...</span></div>';
        
        fetch('{{ route("realtime-ai.status") }}')
            .then(response => response.json())
            .then(data => {
                let html = '<div class="row">';
                
                const commands = {
                    '👥 ملازمین': data.data.capabilities.filter(c => c.includes('Employee')),
                    '📦 پروڈکٹس': data.data.capabilities.filter(c => c.includes('Inventory')),
                    '🛍️ کسٹمرز': data.data.capabilities.filter(c => c.includes('Customer')),
                    '💰 سیلز': data.data.capabilities.filter(c => c.includes('Sales')),
                    '📊 تجزیہ': data.data.capabilities.filter(c => c.includes('Analytics'))
                };

                for (const [category, cmds] of Object.entries(commands)) {
                    if (cmds.length > 0) {
                        html += `<div class="col-md-6 mb-3">
                            <h6 class="fw-bold">${category}</h6>
                            <ul class="list-unstyled">`;
                        cmds.forEach(cmd => {
                            html += `<li class="mb-2"><small class="text-muted">• ${cmd}</small></li>`;
                        });
                        html += `</ul></div>`;
                    }
                }
                
                html += '</div>';
                commandsList.innerHTML = html;
            })
            .catch(error => {
                commandsList.innerHTML = '<div class="alert alert-danger">کمانڈز لوڈ نہیں ہو سکے</div>';
            });
        
        modal.show();
    }

    function showStatus() {
        fetch('{{ route("realtime-ai.status") }}')
            .then(response => response.json())
            .then(data => {
                const status = data.data;
                let message = `⚡ ${status.name}\n`;
                message += `ورژن: ${status.version}\n`;
                message += `حالت: ${status.status}\n`;
                message += `AI ماڈل: ${status.ai_model}\n`;
                message += `Streaming: ${status.streaming ? '✅ فعال' : '❌ غیر فعال'}\n\n`;
                message += `صلاحیتیں:\n`;
                status.capabilities.forEach(cap => {
                    message += `${cap}\n`;
                });
                addMessage(message, 'ai');
            });
    }

    function startNewChat() {
        if (eventSource) {
            eventSource.close();
        }
        document.getElementById('chatMessages').innerHTML = `
            <div class="text-center text-muted mb-4">
                <h6>السلام علیکم! 👋</h6>
                <p class="mb-0">میں آپ کی مدد کے لیے یہاں ہوں۔</p>
                <small>براہ کرم اپنا سوال یا کمانڈ درج کریں</small>
            </div>
        `;
    }

    // Allow Enter key to send message
    document.getElementById('messageInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !isLoading) {
            sendMessage();
        }
    });

    // Load initial status
    window.addEventListener('load', function() {
        showStatus();
    });

    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (eventSource) {
            eventSource.close();
        }
    });
</script>
@endsection
