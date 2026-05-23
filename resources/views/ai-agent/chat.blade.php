@extends('layouts.app')

@section('title', '🤖 MAGIA AI Agent')

@push('styles')
<style>
    :root{
        --bg0:#070A12;
        --bg1:#0B1223;
        --card: rgba(255,255,255,.06);
        --card2: rgba(255,255,255,.08);
        --stroke: rgba(255,255,255,.12);
        --stroke2: rgba(255,255,255,.18);
        --text:#EAF2FF;
        --muted:#9AA7BD;
        --muted2:#74839E;
        --shadow: 0 20px 60px rgba(0,0,0,.45);
        --radius: 18px;

        /* neon accents (teal/cyan + gold) */
        --neon:#2BF7D3;
        --neon2:#12D8FF; /* keep subtle, not purple/blue gradient */
        --gold:#F5B301;
        --good:#1FE08A;
        --danger:#FF4D6D;

        --focus: 0 0 0 4px rgba(43,247,211,.18);
    }

    *{ box-sizing:border-box; }
    .ai-chat-page{
        min-height:100vh;
        background:
            radial-gradient(900px 500px at 15% 10%, rgba(18,216,255,.12), transparent 50%),
            radial-gradient(800px 500px at 70% 0%, rgba(245,179,1,.10), transparent 55%),
            linear-gradient(180deg, var(--bg0), var(--bg1));
        color: var(--text);
        position: relative;
        overflow:hidden;
    }

    /* Hologram HUD frame */
    .ai-chat-page::marker{ content:''; }
    .ai-chat-page .hud-frame{
        position: fixed;
        inset: 14px 14px 14px 14px;
        border-radius: 22px;
        pointer-events:none;
        border: 1px solid rgba(43,247,211,.22);
        box-shadow: 0 0 0 1px rgba(245,179,1,.08), 0 0 40px rgba(43,247,211,.10);
        background: linear-gradient(180deg, rgba(255,255,255,.03), rgba(255,255,255,0));
        opacity: .55;
        transform: translateZ(0);
        animation: hudFlicker 4.2s ease-in-out infinite;
    }

    @keyframes hudFlicker{
        0%,100%{ opacity:.45; filter: saturate(1); }
        50%{ opacity:.62; filter: saturate(1.2) brightness(1.08); }
    }

    .hud-frame::before{
        content:'';
        position:absolute;
        inset:-2px;
        border-radius: 26px;
        background:
            radial-gradient(600px 120px at 20% 0%, rgba(43,247,211,.22), transparent 60%),
            radial-gradient(480px 120px at 80% 0%, rgba(245,179,1,.16), transparent 60%);
        opacity:.9;
        filter: blur(6px);
        animation: hudSweep 3.8s ease-in-out infinite;
    }

    @keyframes hudSweep{
        0%{ transform: translateY(-4px); opacity:.6; }
        50%{ transform: translateY(6px); opacity:1; }
        100%{ transform: translateY(-4px); opacity:.6; }
    }

    /* Processing shimmer bar (shown while isLoading) */
    .processing-hud{
        position: fixed;
        top: 14px;
        left: 50%;
        transform: translateX(-50%);
        width: min(880px, calc(100vw - 28px));
        height: 10px;
        border-radius: 999px;
        pointer-events:none;
        overflow:hidden;
        border: 1px solid rgba(43,247,211,.25);
        background: rgba(255,255,255,.02);
        box-shadow: 0 0 30px rgba(43,247,211,.12);
        opacity: 0;
    }

    .processing-hud--on{ opacity: 1; }

    .processing-hud__fill{
        height: 100%;
        width: 100%;
        background: linear-gradient(90deg, rgba(43,247,211,0), rgba(43,247,211,.75), rgba(245,179,1,.55), rgba(43,247,211,0));
        transform: translateX(-40%);
        animation: hudShimmer 1.15s linear infinite;
        filter: saturate(1.2);
    }

    @keyframes hudShimmer{
        0%{ transform: translateX(-60%); }
        100%{ transform: translateX(60%); }
    }

    /* Futuristic overlays: scanlines + subtle grid */
    .ai-chat-page::after{
        content:'';
        position: absolute;
        inset: 0;
        pointer-events: none;
        background:
            repeating-linear-gradient(
                to bottom,
                rgba(43,247,211,.06) 0px,
                rgba(43,247,211,.00) 2px,
                rgba(43,247,211,.00) 6px
            );
        opacity: .55;
        mix-blend-mode: screen;
        animation: scanlines 5.5s linear infinite;
    }

    .ai-chat-page::before{
        content:'';
        position: absolute;
        inset: -20%;
        pointer-events: none;
        background:
            linear-gradient(transparent 0%, rgba(43,247,211,.10) 50%, transparent 100%);
        transform: rotate(-12deg) translateX(-25%);
        opacity: .28;
        animation: lightSweep 6.5s ease-in-out infinite;
    }

    @keyframes scanlines{
        0%{ transform: translateY(0); }
        100%{ transform: translateY(18px); }
    }

    @keyframes lightSweep{
        0%{ transform: rotate(-12deg) translateX(-35%); opacity: .14; }
        45%{ opacity: .30; }
        100%{ transform: rotate(-12deg) translateX(35%); opacity: .12; }
    }

    /* Layout */
    .chat-container{
        display:flex;
        height: calc(100vh - 60px);
        overflow:hidden;
        gap:0;
        border-radius: 0;
    }

    .chat-sidebar{
        width: 340px;
        padding: 14px;
        background: rgba(255,255,255,.03);
        border-right: 1px solid var(--stroke);
        overflow:hidden;
        display:flex;
        flex-direction:column;
        flex-shrink:0;
    }

    .sidebar-panel{
        background: var(--card);
        border: 1px solid var(--stroke);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow:hidden;
    }

    .chat-sidebar-header{
        padding: 14px 14px 10px 14px;
        border-bottom: 1px solid var(--stroke);
        background: rgba(255,255,255,.02);
    }

    .chat-sidebar-header h4{
        margin:0;
        font-size: 16px;
        font-weight: 900;
        letter-spacing: .3px;
        display:flex;
        align-items:center;
        gap:10px;
    }

    .header-chip{
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background: var(--neon);
        box-shadow: 0 0 0 3px rgba(43,247,211,.18);
    }

    .chat-sidebar-body{
        flex:1;
        overflow-y:auto;
        padding: 12px 0;
    }

    /* Conversation list */
    .conversation-item{
        margin: 0 10px 10px 10px;
        padding: 12px 12px;
        border-radius: 14px;
        border: 1px solid transparent;
        cursor:pointer;
        background: rgba(255,255,255,.03);
        transition: transform .12s ease, border-color .12s ease, background .12s ease;
    }

    .conversation-item:hover{
        transform: translateY(-1px);
        border-color: rgba(255,255,255,.14);
        background: rgba(255,255,255,.05);
    }

    .conversation-item.active{
        border-color: rgba(43,247,211,.55);
        background: rgba(43,247,211,.12);
    }

    .conversation-top{
        display:flex;
        justify-content: space-between;
        align-items:center;
        gap: 10px;
        margin-bottom: 8px;
    }

    .conversation-item strong{
        font-weight: 900;
        font-size: 12px;
        color: var(--text);
        opacity: .95;
    }

    .conv-preview{
        font-size: 12.5px;
        color: var(--muted);
        white-space: nowrap;
        overflow:hidden;
        text-overflow: ellipsis;
        max-width: 260px;
    }

    .conversation-item.active .conv-preview{
        color: rgba(234,242,255,.88);
    }

    .conv-time{
        font-size: 11px;
        color: var(--muted2);
        font-weight: 800;
        letter-spacing:.2px;
    }

    /* Main */
    .chat-main{
        flex:1;
        display:flex;
        flex-direction:column;
        background: rgba(255,255,255,.02);
    }

    .chat-header{
        padding: 12px 16px;
        border-bottom: 1px solid var(--stroke);
        display:flex;
        align-items:center;
        justify-content:space-between;
        background: rgba(255,255,255,.03);
    }

    .chat-header-left{
        display:flex;
        align-items:center;
        gap: 12px;
        min-width: 0;
    }

    .ai-avatar{
        width: 44px;
        height: 44px;
        border-radius: 16px;
        background: rgba(255,255,255,.06);
        border: 1px solid rgba(255,255,255,.14);
        display:flex;
        align-items:center;
        justify-content:center;
        font-weight: 1000;
        box-shadow: 0 18px 40px rgba(0,0,0,.35);
        position:relative;
        overflow:hidden;
    }

    .ai-avatar::after{
        content:'';
        position:absolute;
        inset:-2px;
        background: radial-gradient(circle at 30% 20%, rgba(43,247,211,.45), transparent 55%);
        opacity:.9;
        pointer-events:none;
    }

    .ai-avatar span{
        position:relative;
        z-index:1;
    }

    .ai-meta{
        display:flex;
        flex-direction:column;
        gap: 4px;
        min-width: 0;
    }

    .ai-title{
        font-weight: 950;
        letter-spacing:.2px;
        font-size: 14px;
        white-space:nowrap;
        overflow:hidden;
        text-overflow: ellipsis;
    }

    .ai-sub{
        display:flex;
        align-items:center;
        gap:10px;
        color: var(--muted);
        font-size: 12px;
        font-weight: 900;
        letter-spacing:.2px;
    }

    .status-dot{
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background: var(--good);
        box-shadow: 0 0 0 3px rgba(31,224,138,.18);
        border: 1px solid rgba(255,255,255,.16);
        animation: pulse 2s infinite;
    }
    @keyframes pulse{
        0%,100%{ transform: scale(1); opacity:1; }
        50%{ transform: scale(.85); opacity:.8; }
    }

    .provider-badge{
        display:inline-flex;
        align-items:center;
        gap:8px;
        padding: 6px 10px;
        border-radius: 999px;
        border: 1px solid var(--stroke);
        background: rgba(255,255,255,.04);
        color: var(--text);
        font-size: 12px;
        font-weight: 950;
        white-space:nowrap;
    }
    .provider-badge .gold-dot{
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background: var(--gold);
        box-shadow: 0 0 0 3px rgba(245,179,1,.15);
        border: 1px solid rgba(255,255,255,.18);
    }

    /* Header buttons */
    .action-btn{
        background: rgba(255,255,255,.03) !important;
        border: 1px solid var(--stroke) !important;
        color: var(--text) !important;
        font-weight: 900;
        border-radius: 14px !important;
        padding: 8px 10px !important;
        transition: transform .1s ease, border-color .12s ease, background .12s ease;
    }
    .action-btn:hover{
        transform: translateY(-1px);
        border-color: rgba(43,247,211,.45) !important;
        background: rgba(43,247,211,.10) !important;
    }

    /* Messages */
    .chat-messages{
        flex:1;
        overflow-y:auto;
        padding: 18px 16px;
        display:flex;
        flex-direction:column;
        gap: 12px;
    }

    .message{
        display:flex;
        gap: 10px;
        max-width: 78%;
    }

    .message.user{
        align-self:flex-end;
        flex-direction: row-reverse;
    }

    .message-avatar{
        width: 34px;
        height: 34px;
        border-radius: 14px;
        flex-shrink:0;
        display:flex;
        align-items:center;
        justify-content:center;
        font-weight: 1000;
        border: 1px solid var(--stroke);
        background: rgba(255,255,255,.04);
    }

    .message.user .message-avatar{
        background: rgba(43,247,211,.10);
        border-color: rgba(43,247,211,.45);
        color: var(--text);
    }

    .message-bubble{
        padding: 12px 14px;
        border-radius: 16px;
        line-height: 1.6;
        font-size: 14px;
        border: 1px solid var(--stroke);
        background: rgba(255,255,255,.04);
        box-shadow: 0 10px 30px rgba(0,0,0,.22);
        word-break: break-word;
        position:relative;
        overflow:hidden;
    }

    .message.user .message-bubble{
        background: rgba(43,247,211,.12);
        border-color: rgba(43,247,211,.55);
    }

    .message-bubble.error{
        border-color: rgba(255,77,109,.55);
        background: rgba(255,77,109,.10);
        color: #FFD1DA;
    }

    .message-time{
        font-size: 11px;
        margin-top: 7px;
        color: var(--muted2);
        font-weight: 900;
        letter-spacing: .2px;
    }
    .message.user .message-time{
        text-align:right;
        color: rgba(234,242,255,.72);
    }

    /* Typing indicator */
    .typing-indicator{
        border: 1px solid rgba(43,247,211,.45);
        background: rgba(43,247,211,.10);
        border-radius: 16px;
        padding: 10px 12px;
        display:flex;
        align-items:center;
        gap: 8px;
        width: fit-content;
        box-shadow: 0 14px 34px rgba(0,0,0,.25);
    }

    .typing-dot{
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background: var(--neon);
        border: 1px solid rgba(255,255,255,.18);
        animation: dot 1.15s infinite;
        opacity:.55;
    }
    .typing-dot:nth-child(2){ animation-delay:.15s; }
    .typing-dot:nth-child(3){ animation-delay:.30s; }
    @keyframes dot{
        0%, 100% { transform: translateY(0); opacity:.45; }
        50% { transform: translateY(-7px); opacity:1; }
    }

    /* Input */
    .chat-input-area{
        padding: 12px 16px 16px 16px;
        border-top: 1px solid var(--stroke);
        background: rgba(255,255,255,.02);
    }

    .quick-actions{
        display:flex;
        gap: 8px;
        margin-bottom: 10px;
        flex-wrap: wrap;
    }

    .quick-action-btn{
        padding: 8px 12px;
        font-size: 12px;
        border-radius: 999px;
        border: 1px solid var(--stroke);
        background: rgba(255,255,255,.03);
        color: var(--text);
        font-weight: 950;
        cursor:pointer;
        transition: transform .1s ease, border-color .12s ease, background .12s ease;
    }
    .quick-action-btn:hover{
        transform: translateY(-1px);
        border-color: rgba(43,247,211,.45);
        background: rgba(43,247,211,.10);
    }

    .chat-input-wrapper{
        display:flex;
        gap: 10px;
        align-items:flex-end;
    }

    .chat-input{
        flex:1;
        border: 1px solid var(--stroke);
        border-radius: 16px;
        padding: 12px 14px;
        font-size: 14px;
        resize:none;
        outline:none;
        line-height: 1.5;
        min-height: 48px;
        max-height: 130px;
        background: rgba(255,255,255,.04);
        color: var(--text);
        font-weight: 700;
    }

    .chat-input::placeholder{
        color: rgba(154,167,189,.85);
        font-weight: 800;
    }

    .chat-input:focus{
        border-color: rgba(43,247,211,.65);
        box-shadow: var(--focus);
    }

    .send-btn{
        width: 52px;
        height: 52px;
        border-radius: 18px;
        border: 1px solid rgba(43,247,211,.45);
        background: rgba(43,247,211,.14);
        color: var(--text);
        cursor:pointer;
        flex-shrink:0;
        display:flex;
        align-items:center;
        justify-content:center;
        transition: transform .1s ease, background .12s ease, border-color .12s ease;
        box-shadow: 0 16px 40px rgba(0,0,0,.35);
    }

    .send-btn:hover{
        transform: translateY(-1px);
        border-color: rgba(43,247,211,.75);
        background: rgba(43,247,211,.22);
    }
    .send-btn:disabled{
        opacity:.55;
        cursor:not-allowed;
        transform:none;
    }

    /* Markdown rendering */
    .message-bubble pre{
        background: rgba(255,255,255,.06);
        padding: 10px;
        border-radius: 12px;
        overflow-x:auto;
        margin: 10px 0 0 0;
        font-size: 12px;
        border: 1px solid var(--stroke);
        color: #EAF2FF;
    }

    .message-bubble code{
        background: rgba(255,255,255,.06);
        padding: 2px 6px;
        border-radius: 8px;
        font-size: 12px;
        border: 1px solid var(--stroke);
        color: #EAF2FF;
    }

    .message-bubble ul{ padding-left: 18px; margin: 8px 0; }
    .message-bubble li{ margin: 6px 0; }
    .message-bubble li::marker{ color: var(--neon); }

    /* Tool receipt UI (futuristic animations) */
    .tool-receipt{
        width: 100%;
        max-width: 720px;
        margin-top: 4px;
        border-radius: 16px;
        border: 1px solid rgba(43,247,211,.35);
        background: rgba(255,255,255,.03);
        box-shadow: 0 18px 46px rgba(0,0,0,.35);
        overflow:hidden;

        opacity: 0;
        transform: translateY(10px) scale(.99);
        animation: receiptIn .28s ease-out forwards;
        position: relative;
    }

    .tool-receipt::before{
        content:'';
        position:absolute;
        inset:-2px;
        background:
            radial-gradient(500px 60px at 20% 0%, rgba(43,247,211,.28), transparent 55%),
            radial-gradient(400px 60px at 80% 0%, rgba(245,179,1,.18), transparent 55%);
        pointer-events:none;
        opacity:.9;
    }

    .tool-receipt--dismiss{
        animation: receiptOut .32s ease-in forwards;
    }

    @keyframes receiptIn{
        0%{ opacity:0; transform: translateY(10px) scale(.99); }
        100%{ opacity:1; transform: translateY(0) scale(1); }
    }

    @keyframes receiptOut{
        0%{ opacity:1; transform: translateY(0) scale(1); }
        100%{ opacity:0; transform: translateY(-8px) scale(.99); }
    }

    .tool-receipt__bar{
        height: 3px;
        background: linear-gradient(90deg, rgba(43,247,211,.95), rgba(245,179,1,.65));
        box-shadow: 0 0 22px rgba(43,247,211,.35);
        animation: receiptBarMove 1.6s linear infinite;
        position: relative;
        z-index: 1;
    }

    @keyframes receiptBarMove{
        0% { filter: brightness(1); transform: translateX(-30%); }
        50% { filter: brightness(1.25); transform: translateX(30%); }
        100% { filter: brightness(1); transform: translateX(-30%); }
    }

    .tool-receipt__main{
        position: relative;
        z-index: 1;
        display:flex;
        gap: 12px;
        padding: 12px 14px;
        align-items:flex-start;
    }

    .tool-receipt__icon{
        width: 36px;
        height: 36px;
        border-radius: 12px;
        display:flex;
        align-items:center;
        justify-content:center;
        background: rgba(43,247,211,.10);
        border: 1px solid rgba(43,247,211,.35);
        flex-shrink: 0;
        font-size: 16px;
        font-weight: 1000;
    }

    .tool-receipt__title{
        font-weight: 1000;
        letter-spacing: .25px;
        color: var(--text);
        text-transform: uppercase;
        font-size: 12px;
        margin-bottom: 6px;
        opacity:.95;

        text-shadow: 0 0 18px rgba(43,247,211,.20);
    }

    .tool-receipt__payload{
        padding: 10px 12px;
        border-radius: 14px;
        border: 1px solid rgba(255,255,255,.10);
        background: rgba(255,255,255,.02);
        max-height: 240px;
        overflow:auto;
    }

    .tool-receipt__payload pre{
        margin:0;
        font-size: 12px;
        color: var(--text);
        font-weight: 800;
        opacity: .92;
        white-space: pre-wrap;
        word-break: break-word;
    }

    .tool-receipt--error{
        border-color: rgba(255,77,109,.55);
    }

    .tool-receipt--error .tool-receipt__bar{
        background: linear-gradient(90deg, rgba(255,77,109,.95), rgba(245,179,1,.35));
    }

    /* Responsive */
    @media (max-width: 820px){
        .chat-container{ flex-direction: column; }
        .chat-sidebar{ width: 100%; border-right: none; border-bottom: 1px solid var(--stroke); }
        .chat-sidebar-body{ max-height: 220px; }
        .message{ max-width: 95%; }
        .tool-receipt{ max-width: 95%; }
    }
</style>
@endpush

@section('content')
<div class="ai-chat-page">
    <div class="chat-container">
        <!-- Sidebar -->
        <div class="chat-sidebar">
            <div class="sidebar-panel">
                <div class="chat-sidebar-header">
                    <h4>
                        <span class="header-chip"></span>
                        💬 Conversations
                    </h4>

                    <div class="d-flex align-items-center justify-content-between mt-3">
                        <div style="color: var(--muted); font-weight: 950; font-size: 12px;">
                            Glass-neon UI
                        </div>

                        <button class="btn action-btn btn-sm" onclick="startNewConversation()" title="New conversation">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>

                    <div class="lang-selector mt-3 d-flex gap-2 flex-wrap">
                        <button class="lang-btn btn btn-sm" style="border:1px solid var(--stroke);background:rgba(255,255,255,.03);color:var(--text);font-weight:950;" data-lang="en" onclick="setLanguage('en', this)">🇬🇧 EN</button>
                        <button class="lang-btn btn btn-sm" style="border:1px solid var(--stroke);background:rgba(255,255,255,.03);color:var(--text);font-weight:950;" data-lang="ur" onclick="setLanguage('ur', this)">🇵🇰 اردو</button>
                        <button class="lang-btn btn btn-sm" style="border:1px solid var(--stroke);background:rgba(255,255,255,.03);color:var(--text);font-weight:950;" data-lang="hi" onclick="setLanguage('hi', this)">🇮🇳 हिन</button>
                    </div>

                    <!-- Mini Analytics -->
                    <div class="mt-3" style="padding:12px;border-top:1px solid var(--stroke);">
                        <div style="display:flex;align-items:center;gap:8px;font-weight:1000;color:var(--text);font-size:12px;">
                            <i class="fas fa-chart-line" style="color:var(--neon);"></i>
                            Quick Stats
                        </div>
                        <div id="analyticsContent" class="mt-2" style="color:var(--muted);font-weight:900;font-size:13px;">
                            <div class="spinner-border spinner-border-sm" role="status"></div> Loading...
                        </div>
                    </div>
                </div>

                <div class="chat-sidebar-body" id="conversationList">
                    <div class="text-muted text-center py-4" style="color:var(--muted)!important;">
                        <i class="fas fa-inbox fa-2x mb-2 opacity-50 d-block" style="opacity:.5;"></i>
                        <small style="font-weight:1000;">No conversations yet</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="chat-main">
            <div class="chat-header">
                <div class="chat-header-left">
                    <div class="ai-avatar"><span>M</span></div>
                    <div class="ai-meta">
                        <div class="ai-title">MAGIA AI Agent</div>
                        <div class="ai-sub">
                            <span class="status-dot"></span>
                            <span id="agentStatusText">Online</span>
                            <span class="provider-badge ms-2" id="providerBadge">
                                <span class="gold-dot"></span>
                                <span>GPT-4o</span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button class="action-btn btn btn-sm" onclick="clearCurrentConversation()" title="Clear conversation">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                    <button class="action-btn btn btn-sm" onclick="toggleFullscreen()" title="Fullscreen">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
            </div>

            <div id="chatMessages" class="chat-messages">
                <div class="text-center" style="margin:30px 0 10px 0;">
                    <div class="ai-avatar mx-auto mb-3" style="width:72px;height:72px;border-radius:22px;">
                        <span style="font-size:30px;">🤖</span>
                    </div>
                    <h5 style="font-weight:1000;color:var(--text);">Welcome to MAGIA AI Agent</h5>
                    <p style="margin:0;color:var(--muted);font-weight:900;">
                        Your intelligent jewellery business assistant
                    </p>
                    <small style="display:block;margin-top:10px;color:var(--muted2);font-weight:950;">
                        Ask about employees, products, customers, sales, analytics, or anything else!
                    </small>
                </div>
            </div>

            <div class="chat-input-area">
                <div class="quick-actions" id="quickActions">
                    <button class="quick-action-btn" onclick="quickMessage('Show all employees')">👥 Employees</button>
                    <button class="quick-action-btn" onclick="quickMessage('Show all products in stock')">📦 Inventory</button>
                    <button class="quick-action-btn" onclick="quickMessage('Show today sales')">💰 Today Sales</button>
                    <button class="quick-action-btn" onclick="quickMessage('What is the gold rate today?')">💎 Gold Rate</button>
                    <button class="quick-action-btn" onclick="quickMessage('Give me analytics')">📊 Analytics</button>
                    <button class="quick-action-btn" onclick="quickMessage('Add employee Ali Khan, salary 50000, designer')">➕ Add Employee</button>
                    <button class="quick-action-btn" onclick="quickMessage('Add product gold ring, weight 10, price 50000')">➕ Add Product</button>
                </div>

                <div class="chat-input-wrapper">
                    <textarea
                        id="messageInput"
                        class="chat-input"
                        placeholder="Type your message... (Enter to send, Shift+Enter for new line)"
                        rows="1"
                    ></textarea>

                    <button class="send-btn" id="sendBtn" onclick="sendMessage()" title="Send">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<div class="modal fade" id="newConversationModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:rgba(255,255,255,.06);border:1px solid var(--stroke);color:var(--text);">
            <div class="modal-header" style="border-bottom:1px solid var(--stroke);">
                <h5 class="modal-title">🆕 New Conversation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="color:var(--muted);font-weight:900;">
                Start a fresh conversation. Current conversation history will be saved.
            </div>
            <div class="modal-footer" style="border-top:1px solid var(--stroke);">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" onclick="confirmNewConversation()">Start New</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // ==================== STATE ====================
    let currentConversationId = null;
    let isLoading = false;
    let eventSource = null;
    let messageHistory = [];
    const config = { USE_STREAMING: false };

    // ==================== INIT ====================
    document.addEventListener('DOMContentLoaded', function() {
        loadConversations();
        loadAnalytics();
        loadAgentStatus();

        const input = document.getElementById('messageInput');
        input?.focus();

        input?.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        input?.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 130) + 'px';
        });

        setInterval(loadAnalytics, 60000);
        setInterval(loadAgentStatus, 30000);
    });

    // ==================== API HELPERS ====================
    function api(endpoint, method = 'GET', body = null) {
        const headers = {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            'Accept': 'application/json'
        };

        const options = { method, headers };
        if (body) options.body = JSON.stringify(body);
        return fetch(endpoint, options).then(r => r.json());
    }

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content;
    }

    // ==================== CONVERSATIONS ====================
    async function loadConversations() {
        try {
            const data = await api('/ai-agent/conversations');
            if (data.status === 'success') {
                renderConversations(data.data.conversations || []);
            }
        } catch (e) {
            console.error('Load conversations error:', e);
        }
    }

    function renderConversations(conversations) {
        const container = document.getElementById('conversationList');
        if (!container) return;

        if (conversations.length === 0) {
            container.innerHTML = `
                <div class="text-muted text-center py-4" style="color:var(--muted)!important;">
                    <i class="fas fa-inbox fa-2x mb-2 opacity-50 d-block" style="opacity:.5;"></i>
                    <small style="font-weight:1000;">No conversations yet</small>
                </div>`;
            return;
        }

        container.innerHTML = conversations.map(conv => {
            const isActive = conv.id === currentConversationId;
            const lastMsg = (conv.messages && conv.messages.length > 0)
                ? (conv.messages[conv.messages.length - 1].content || '')
                : 'New conversation';

            const time = conv.last_updated ? new Date(conv.last_updated).toLocaleTimeString() : '';

            return `
                <div class="conversation-item ${isActive ? 'active' : ''}" onclick="switchConversation('${conv.id}')">
                    <div class="conversation-top">
                        <strong>${isActive ? '● ' : ''}Conversation ${conv.messages.length > 0 ? '#' + conv.id.slice(-4) : '...'}</strong>
                        <span class="conv-time">${time}</span>
                    </div>
                    <div class="conv-preview">${escapeText(lastMsg.substring(0, 50))}</div>
                </div>`;
        }).join('');
    }

    async function switchConversation(conversationId) {
        if (isLoading) return;
        currentConversationId = conversationId;

        try {
            const data = await api(`/ai-agent/history/${conversationId}`);
            if (data.status === 'success') {
                renderMessages(data.data.messages || []);
            }
        } catch (e) {
            console.error('Load history error:', e);
        }

        loadConversations();
    }

    function startNewConversation() {
        currentConversationId = null;
        const chat = document.getElementById('chatMessages');
        if (chat) {
            chat.innerHTML = `
                <div class="text-center" style="margin:30px 0 10px 0;">
                    <div class="ai-avatar mx-auto mb-3" style="width:72px;height:72px;border-radius:22px;">
                        <span style="font-size:30px;">🤖</span>
                    </div>
                    <h5 style="font-weight:1000;color:var(--text);">New Conversation</h5>
                    <p style="margin:0;color:var(--muted);font-weight:900;">Start fresh!</p>
                </div>`;
        }
        loadConversations();
    }

    function confirmNewConversation() {
        startNewConversation();
        bootstrap.Modal.getInstance(document.getElementById('newConversationModal')).hide();
    }

    async function clearCurrentConversation() {
        if (!currentConversationId) return;
        if (!confirm('Clear this conversation?')) return;

        try {
            await api(`/ai-agent/conversations/${currentConversationId}`, 'DELETE');
            startNewConversation();
        } catch (e) {
            console.error('Clear conversation error:', e);
        }
    }

    // ==================== MESSAGES ====================
    function renderMessages(messages) {
        const container = document.getElementById('chatMessages');
        if (!container) return;

        container.innerHTML = '';
        messages.forEach(msg => {
            addMessageToDOM(msg.content, msg.role === 'user' ? 'user' : 'ai', false);
        });

        scrollToBottom();
    }

    async function sendMessage() {
        const input = document.getElementById('messageInput');
        const message = input?.value?.trim() || '';
        if (!message || isLoading) return;

        if (input) {
            input.value = '';
            input.style.height = 'auto';
        }

        addMessageToDOM(message, 'user');
        showTypingIndicator();

        isLoading = true;
        updateSendButton(true);

        const lang = document.querySelector('.lang-btn.active')?.dataset?.lang || 'en';

        try {
            if (config.USE_STREAMING) {
                await sendStreamMessage(message, lang);
            } else {
                await sendStandardMessage(message, lang);
            }
        } catch (error) {
            removeTypingIndicator();
            addMessageToDOM('Connection error. Please try again.', 'ai', true);
            console.error('Send error:', error);
        } finally {
            isLoading = false;
            updateSendButton(false);
            input?.focus();
            loadConversations();
        }
    }

    async function sendStandardMessage(message, lang) {
        try {
            const response = await fetch('/ai-agent/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify({
                    message: message,
                    language: lang,
                    conversation_id: currentConversationId
                })
            });

            const data = await response.json();
            removeTypingIndicator();

            if (data.status === 'success') {
                const answer = data.message || (data.data && data.data.answer) || 'Done';
                addMessageToDOM(answer, 'ai');

                // FUTURISTIC: show action receipt if backend executed a tool
                if (data.action || data.source === 'integrated_tools_auto' || data.action_taken === true) {
                    const actionName = data.action ? String(data.action) : 'action';
                    const payload = data.data && typeof data.data === 'object' ? data.data : null;
                    addActionReceipt(actionName, payload, false);
                } else if (data.source && String(data.source).includes('integrated_tools')) {
                    addActionReceipt(String(data.source), data.data, false);
                }

                if (data.conversation_id) currentConversationId = data.conversation_id;
                messageHistory.push({ text: message, response: answer });
            } else {
                addMessageToDOM(data.message || 'Error processing', 'ai', true);
                addActionReceipt('error', data, true);
            }
        } catch (error) {
            removeTypingIndicator();
            addMessageToDOM('Failed to connect to AI service.', 'ai', true);
            addActionReceipt('network_error', { error: String(error) }, true);
        }
    }

    async function sendStreamMessage(message, lang) {
        try {
            if (eventSource) eventSource.close();

            const response = await fetch('/api/chat/stream', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify({
                    message: message,
                    language: lang,
                    conversation_id: currentConversationId
                })
            });

            if (!response.ok) {
                await sendStandardMessage(message, lang);
                return;
            }

            const reader = response.body.getReader();
            const decoder = new TextDecoder();

            let fullResponse = '';
            let streamingWrap = null;

            while (true) {
                const { done, value } = await reader.read();
                if (done) break;

                const chunk = decoder.decode(value);
                const lines = chunk.split('\n');

                for (const line of lines) {
                    if (!line.startsWith('data: ')) continue;

                    try {
                        const data = JSON.parse(line.slice(6));

                        if (data.type === 'chunk') {
                            if (!streamingWrap) {
                                removeTypingIndicator();
                                streamingWrap = createStreamingMessage();
                            }
                            fullResponse += data.content;
                            updateStreamingMessage(streamingWrap, fullResponse);
                        }
                    } catch (e) {}
                }
            }

            if (fullResponse) messageHistory.push({ text: message, response: fullResponse });

        } catch (error) {
            removeTypingIndicator();
            await sendStandardMessage(message, lang);
        }
    }

    // ==================== UI HELPERS ====================
    function addMessageToDOM(text, sender, isError = false) {
        const container = document.getElementById('chatMessages');
        if (!container) return;

        const wrapper = document.createElement('div');
        wrapper.className = `message ${sender}`;

        const initials = sender === 'user' ? 'U' : 'M';
        const time = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

        const bubbleClass = isError ? 'error' : '';
        const safeHtml = formatMessage(text);

        wrapper.innerHTML = `
            <div class="message-avatar">${initials}</div>
            <div>
                <div class="message-bubble ${bubbleClass}">${safeHtml}</div>
                <div class="message-time">${time}</div>
            </div>`;

        container.appendChild(wrapper);
        scrollToBottom();

        if (sender === 'ai' && !isError) {
            messageHistory.push({ text: document.getElementById('messageInput')?.value || '', response: text });
        }
    }

    function showTypingIndicator() {
        removeTypingIndicator();
        const container = document.getElementById('chatMessages');
        if (!container) return;

        const wrapper = document.createElement('div');
        wrapper.className = 'message ai';
        wrapper.id = 'typingIndicator';
        wrapper.innerHTML = `
            <div class="message-avatar">M</div>
            <div class="typing-indicator">
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
            </div>`;

        container.appendChild(wrapper);
        scrollToBottom();
    }

    function removeTypingIndicator() {
        const indicator = document.getElementById('typingIndicator');
        indicator?.remove();
    }

    function createStreamingMessage() {
        removeTypingIndicator();
        const container = document.getElementById('chatMessages');
        if (!container) return null;

        const wrapper = document.createElement('div');
        wrapper.className = 'message ai';
        wrapper.innerHTML = `
            <div class="message-avatar">M</div>
            <div>
                <div class="message-bubble" id="streamingContent">
                    <span class="typing-dot" style="display:inline-block;width:10px;height:10px;border-radius:999px;background:var(--neon);vertical-align:middle;opacity:.8;"></span>
                </div>
                <div class="message-time"></div>
            </div>`;

        container.appendChild(wrapper);
        scrollToBottom();
        return wrapper;
    }

    function updateStreamingMessage(wrapper, text) {
        if (!wrapper) return;
        const bubble = wrapper.querySelector('#streamingContent');
        if (!bubble) return;

        bubble.innerHTML = formatMessage(text);
        scrollToBottom();
    }

    function scrollToBottom() {
        const container = document.getElementById('chatMessages');
        if (!container) return;

        setTimeout(() => {
            container.scrollTop = container.scrollHeight;
        }, 40);
    }

    function escapeText(text) {
        const div = document.createElement('div');
        div.textContent = text ?? '';
        return div.innerHTML;
    }

    function formatMessage(text) {
        if (text === null || text === undefined) return '';

        const safe = escapeText(String(text));

        // Basic markdown: **bold**, *italic*, `code`, newlines
        return safe
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/`([^`]+)`/g, '<code>$1</code>')
            .replace(/\n/g, '<br>');
    }

    function updateSendButton(disabled) {
        const btn = document.getElementById('sendBtn');
        if (!btn) return;
        btn.disabled = disabled;
    }

    // ==================== TOOL ACTION RECEIPTS ====================
    // Shows a futuristic animated receipt when backend executes a tool/action.
    function addActionReceipt(actionName, payload, isError) {
        const container = document.getElementById('chatMessages');
        if (!container) return;

        // Avoid huge payload dumps
        let payloadText = '';
        if (payload && typeof payload === 'object') {
            try {
                const safe = JSON.stringify(payload);
                payloadText = safe.length > 700 ? safe.slice(0, 700) + '…' : safe;
            } catch (e) {
                payloadText = String(payload);
            }
        } else if (payload) {
            payloadText = String(payload);
        }

        const wrapper = document.createElement('div');
        wrapper.className = 'tool-receipt' + (isError ? ' tool-receipt--error' : '');

        const title = actionName ? String(actionName) : 'action';

        wrapper.innerHTML = `
            <div class="tool-receipt__bar"></div>
            <div class="tool-receipt__main">
                <div class="tool-receipt__icon">⚙️</div>
                <div class="tool-receipt__text">
                    <div class="tool-receipt__title">${title}</div>
                    ${payloadText ? `<div class="tool-receipt__payload"><pre>${escapeHtmlForReceipt(payloadText)}</pre></div>` : ''}
                </div>
            </div>
        `;

        container.appendChild(wrapper);

        // Smooth scroll after receipt
        setTimeout(scrollToBottom, 60);

        // Auto-remove after a while (optional)
        setTimeout(() => wrapper.classList.add('tool-receipt--dismiss'), 5200);
        setTimeout(() => wrapper.remove(), 6400);
    }

    function escapeHtmlForReceipt(text) {
        const div = document.createElement('div');
        div.textContent = text ?? '';
        return div.innerHTML;
    }

    // ==================== SIDEBAR FEATURES ====================
    async function loadAnalytics() {
        try {
            const response = await fetch('/ai-agent/analytics');
            const data = await response.json();
            if (data.status === 'success') {
                const a = data.data || {};
                document.getElementById('analyticsContent').innerHTML = `
                    <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.08);">
                        <span style="color:var(--muted);font-weight:950;">Today Sales</span>
                        <span style="font-weight:1000;color:var(--text);">${'Rs. ' + (a.total_sales || 0).toLocaleString()}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.08);">
                        <span style="color:var(--muted);font-weight:950;">Products</span>
                        <span style="font-weight:1000;color:var(--text);">${a.total_products || 0}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.08);">
                        <span style="color:var(--muted);font-weight:950;">Employees</span>
                        <span style="font-weight:1000;color:var(--text);">${a.total_employees || 0}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:6px 0;">
                        <span style="color:var(--muted);font-weight:950;">Customers</span>
                        <span style="font-weight:1000;color:var(--text);">${a.total_customers || 0}</span>
                    </div>`;
            }
        } catch (e) {
            console.error('Analytics load error:', e);
        }
    }

    async function loadAgentStatus() {
        try {
            const response = await fetch('/ai-agent/status');
            const data = await response.json();
            if (data.status === 'success') {
                document.getElementById('agentStatusText').textContent = 'Online';
                if (data.data && data.data.provider) {
                    const providerMap = {
                        'openai': 'GPT-4o',
                        'groq': 'Mixtral 8x7B',
                        'gemini': 'Gemini 1.5 Pro'
                    };
                    const providerText = providerMap[data.data.provider] || data.data.provider;
                    const badge = document.getElementById('providerBadge');
                    badge.querySelector('span:not(.gold-dot)')?.remove();
                    badge.appendChild(document.createTextNode(' ' + providerText));
                }
            }
        } catch (e) {
            document.getElementById('agentStatusText').textContent = 'Offline';
        }
    }

    // ==================== QUICK ACTIONS ====================
    function quickMessage(text) {
        const input = document.getElementById('messageInput');
        if (!input) return;
        input.value = text;
        sendMessage();
    }

    function setLanguage(lang, btn) {
        document.querySelectorAll('.lang-btn').forEach(b => {
            b.classList.remove('active');
            b.style.background = 'rgba(255,255,255,.03)';
            b.style.color = 'var(--text)';
            b.style.borderColor = 'var(--stroke)';
        });

        if (btn) {
            btn.classList.add('active');
            btn.style.background = 'rgba(43,247,211,.12)';
            btn.style.color = 'var(--text)';
            btn.style.borderColor = 'rgba(43,247,211,.65)';
        }
    }

    // Set default active language based on $lang (server side)
    (function initLangFromServer(){
        const serverLang = @json($lang ?? 'en');
        const btn = document.querySelector(`.lang-btn[data-lang="${serverLang}"]`);
        if (btn) setLanguage(serverLang, btn);
    })();

    // ==================== FULLSCREEN ====================
    function toggleFullscreen() {
        const container = document.querySelector('.chat-container');
        if (!container) return;

        if (!document.fullscreenElement) {
            container.requestFullscreen().catch(err => console.error('Fullscreen error:', err));
        } else {
            document.exitFullscreen();
        }
    }
</script>
@endpush
@endsection
