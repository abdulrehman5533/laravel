@extends('layouts.app')

@section('title', 'AI Assistant')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-robot me-2"></i>AI Assistant
                    </h4>
                    <div class="d-flex gap-2">
                        <select id="provider-select" class="form-select form-select-sm" style="width: auto;">
                            <option value="openai">OpenAI GPT-4</option>
                            <option value="deepseek">DeepSeek</option>
                            <option value="anthropic">Claude</option>
                        </select>
                        <button id="clear-chat" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-trash me-1"></i>Clear Chat
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="chat-container" class="chat-container">
                        <div id="chat-messages" class="chat-messages">
                            <!-- Welcome message -->
                            <div class="message-wrapper ai-message">
                                <div class="message-avatar">
                                    <i class="fas fa-robot"></i>
                                </div>
                                <div class="message-content">
                                    <div class="message-text">
                                        Hello! I'm your **{{ config('app.name', 'MAGIA LUPOS') }} Assistant**. I am powered by both advanced cloud AI (when available) and **Native Business Intelligence**.
                                        <div class="mt-2">I can help you analyze your business data in real-time:</div>
                                        <ul class="mt-2 mb-0">
                                            <li>📊 **Sales**: "What are my sales today?"</li>
                                            <li>💎 **Inventory**: "Show me stock value"</li>
                                            <li>✨ **Market**: "Current gold rate"</li>
                                            <li>👥 **Customers**: "How many clients do we have?"</li>
                                            <li>💰 **Finance**: "Total expenses"</li>
                                        </ul>
                                        How can I assist you manage your jewelry empire today?
                                    </div>
                                    <div class="message-time">{{ now()->format('H:i') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Typing indicator -->
                    <div id="typing-indicator" class="typing-indicator d-none">
                        <div class="message-wrapper ai-message">
                            <div class="message-avatar">
                                <i class="fas fa-robot"></i>
                            </div>
                            <div class="message-content">
                                <div class="typing-dots">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Input area -->
                    <div class="chat-input-container">
                        <div class="input-group">
                            <textarea
                                id="message-input"
                                class="form-control"
                                placeholder="Type your message here..."
                                rows="1"
                                maxlength="1000"
                            ></textarea>
                            <button id="send-button" class="btn btn-primary" type="button">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                        <div class="input-footer">
                            <small class="text-muted">
                                Press Enter to send, Shift+Enter for new line
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.chat-container {
    height: 600px;
    display: flex;
    flex-direction: column;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.message-wrapper {
    display: flex;
    gap: 0.75rem;
    max-width: 80%;
}

.message-wrapper.user-message {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.message-wrapper.ai-message {
    align-self: flex-start;
}

.message-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #007bff;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.user-message .message-avatar {
    background: #28a745;
}

.message-content {
    background: #f8f9fa;
    border-radius: 18px;
    padding: 0.75rem 1rem;
    position: relative;
}

.user-message .message-content {
    background: #007bff;
    color: white;
}

.message-text {
    line-height: 1.4;
    word-wrap: break-word;
}

.message-time {
    font-size: 0.75rem;
    color: #6c757d;
    margin-top: 0.25rem;
    text-align: right;
}

.user-message .message-time {
    color: rgba(255, 255, 255, 0.7);
    text-align: left;
}

.chat-input-container {
    border-top: 1px solid #dee2e6;
    padding: 1rem;
    background: white;
}

.input-group .form-control {
    border-radius: 25px 0 0 25px;
    border-right: none;
    resize: none;
}

.input-group .btn {
    border-radius: 0 25px 25px 0;
    border-left: none;
}

.input-footer {
    text-align: center;
    margin-top: 0.5rem;
}

.typing-indicator {
    padding: 0 1rem 1rem;
}

.typing-dots {
    display: flex;
    gap: 4px;
    padding: 0.5rem 0;
}

.typing-dots span {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #007bff;
    animation: typing 1.4s infinite;
}

.typing-dots span:nth-child(2) {
    animation-delay: 0.2s;
}

.typing-dots span:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes typing {
    0%, 60%, 100% {
        transform: translateY(0);
        opacity: 0.4;
    }
    30% {
        transform: translateY(-10px);
        opacity: 1;
    }
}

.error-message {
    background: #f8d7da !important;
    color: #721c24 !important;
    border: 1px solid #f5c6cb;
}

.rate-limit-message {
    background: #fff3cd !important;
    color: #856404 !important;
    border: 1px solid #ffeaa7;
}

@media (max-width: 768px) {
    .message-wrapper {
        max-width: 90%;
    }

    .chat-container {
        height: 500px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const messageInput = document.getElementById('message-input');
    const sendButton = document.getElementById('send-button');
    const chatMessages = document.getElementById('chat-messages');
    const typingIndicator = document.getElementById('typing-indicator');
    const providerSelect = document.getElementById('provider-select');
    const clearChatButton = document.getElementById('clear-chat');

    let conversationId = 'default';
    let isTyping = false;

    // Auto-resize textarea
    messageInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });

    // Send message on Enter (but not Shift+Enter)
    messageInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // Send button click
    sendButton.addEventListener('click', sendMessage);

    // Clear chat
    clearChatButton.addEventListener('click', function() {
        if (confirm('Are you sure you want to clear the conversation?')) {
            clearConversation();
        }
    });

    function sendMessage() {
        const message = messageInput.value.trim();
        if (!message || isTyping) return;

        // Add user message to chat
        addMessage(message, 'user');
        messageInput.value = '';
        messageInput.style.height = 'auto';

        // Show typing indicator
        showTypingIndicator();

        // Send to server
        const provider = providerSelect.value;
        fetch('/accounts/ai-chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                message: message,
                conversation_id: conversationId,
                provider: provider
            })
        })
        .then(response => response.json())
        .then(data => {
            hideTypingIndicator();

            if (data.error) {
                addMessage(data.answer, 'ai', data.error === 'rate_limit' ? 'rate-limit-message' : 'error-message');
            } else {
                addMessage(data.answer, 'ai');
                if (data.conversation_id) {
                    conversationId = data.conversation_id;
                }
            }
        })
        .catch(error => {
            hideTypingIndicator();
            console.error('Error:', error);
            addMessage('Sorry, there was an error processing your request. Please try again.', 'ai', 'error-message');
        });
    }

    function formatMessage(text) {
        // Handle bold
        text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        // Handle new lines
        text = text.replace(/\n/g, '<br>');
        // Handle bullet points
        text = text.replace(/^- (.*)/gm, '• $1');
        return text;
    }

    function addMessage(text, sender, extraClass = '') {
        const messageWrapper = document.createElement('div');
        messageWrapper.className = `message-wrapper ${sender}-message`;

        const avatar = document.createElement('div');
        avatar.className = 'message-avatar';
        avatar.innerHTML = sender === 'user' ? '<i class="fas fa-user"></i>' : '<i class="fas fa-robot"></i>';

        const content = document.createElement('div');
        content.className = 'message-content';

        if (extraClass) {
            content.classList.add(extraClass);
        }

        const messageText = document.createElement('div');
        messageText.className = 'message-text';
        messageText.innerHTML = sender === 'ai' ? formatMessage(text) : text;

        const time = document.createElement('div');
        time.className = 'message-time';
        time.textContent = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

        content.appendChild(messageText);
        content.appendChild(time);

        messageWrapper.appendChild(avatar);
        messageWrapper.appendChild(content);

        chatMessages.appendChild(messageWrapper);
        scrollToBottom();
    }

    function showTypingIndicator() {
        isTyping = true;
        typingIndicator.classList.remove('d-none');
        sendButton.disabled = true;
        scrollToBottom();
    }

    function hideTypingIndicator() {
        isTyping = false;
        typingIndicator.classList.add('d-none');
        sendButton.disabled = false;
    }

    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function clearConversation() {
        // Clear messages except welcome message
        const messages = chatMessages.querySelectorAll('.message-wrapper');
        for (let i = 1; i < messages.length; i++) {
            messages[i].remove();
        }

        // Clear server-side conversation
        fetch('/accounts/ai-chat/clear', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                conversation_id: conversationId
            })
        });

        conversationId = 'default';
    }

    // Load available providers
    fetch('/accounts/ai-chat/providers')
        .then(response => response.json())
        .then(data => {
            providerSelect.innerHTML = '';
            Object.entries(data).forEach(([key, provider]) => {
                const option = document.createElement('option');
                option.value = key;
                option.textContent = provider.name + (provider.available ? '' : ' (Not Configured)');
                option.disabled = !provider.available;
                providerSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading providers:', error);
        });
});
</script>
@endsection
