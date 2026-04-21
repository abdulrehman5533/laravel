{{-- resources/views/layouts/guest.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Login | MAGIA LUPOS')</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2250%22 cy=%2250%22 r=%2245%22 stroke=%22%23af9144%22 stroke-width=%222%22 fill=%22none%22/><path d=%22M50 20L35 45L50 80L65 45L50 20Z%22 fill=%22%23af9144%22/><path d=%22M42 35L45 30L50 33L55 30L58 35%22 stroke=%22white%22 stroke-width=%222%22 fill=%22none%22/></svg>">
    <link rel="shortcut icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2250%22 cy=%2250%22 r=%2245%22 stroke=%22%23af9144%22 stroke-width=%222%22 fill=%22none%22/><path d=%22M50 20L35 45L50 80L65 45L50 20Z%22 fill=%22%23af9144%22/><path d=%22M42 35L45 30L50 33L55 30L58 35%22 stroke=%22white%22 stroke-width=%222%22 fill=%22none%22/></svg>">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            cursor: default;
        }

        html, body {
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: url('/Img/login.jpg') center/cover no-repeat fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        /* Unique Premium Overlay */
        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(135deg, rgba(0,0,0,0.8) 0%, rgba(20,20,20,0.6) 100%);
            z-index: 0;
        }

        .auth-container {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 460px;
            padding: 20px;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 0px; /* Unique sharp modern look */
            border: 1px solid rgba(175, 145, 68, 0.2);
            box-shadow: 
                0 30px 70px rgba(0,0,0,0.5),
                inset 0 0 0 10px #fff; /* Double border effect */
            overflow: hidden;
            animation: cardSlideUp 1s cubic-bezier(0.19, 1, 0.22, 1);
            position: relative;
        }

        /* Subtle corner accents */
        .auth-card::before, .auth-card::after {
            content: '';
            position: absolute;
            width: 40px;
            height: 40px;
            border: 1px solid #af9144;
            z-index: 3;
            pointer-events: none;
        }
        .auth-card::before { top: 20px; left: 20px; border-right: 0; border-bottom: 0; }
        .auth-card::after { bottom: 20px; right: 20px; border-left: 0; border-top: 0; }

        @keyframes cardSlideUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .auth-header {
            padding: 70px 40px 30px;
            text-align: center;
            position: relative;
        }

        .auth-header-icon {
            font-size: 32px;
            margin-bottom: 25px;
            display: inline-block;
            color: #af9144;
            letter-spacing: 4px;
        }

        .auth-header h3 {
            font-family: 'Cinzel', serif;
            font-size: 34px;
            font-weight: 400;
            color: #111;
            letter-spacing: 8px;
            text-transform: uppercase;
            margin: 0;
        }

        .auth-header p {
            font-family: 'Inter', sans-serif;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 4px;
            color: #af9144;
            margin-top: 15px;
            font-weight: 600;
        }

        .auth-body {
            padding: 0 50px 70px;
        }

        @media (max-width: 576px) {
            .auth-container { padding: 15px; }
            .auth-body { padding: 30px 20px; }
        }
    </style>
</head>
<body>
    <div class="flare"></div>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-header-icon">
                    <svg width="64" height="64" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" class="mx-auto mb-3">
                        <path d="M50 95C74.8528 95 95 74.8528 95 50C95 25.1472 74.8528 5 50 5C25.1472 5 5 25.1472 5 50C5 74.8528 25.1472 95 50 95Z" stroke="#af9144" stroke-width="2"/>
                        <path d="M50 20L35 45L50 80L65 45L50 20Z" fill="#af9144"/>
                        <path d="M50 20L25 40L50 80L75 40L50 20Z" stroke="#af9144" stroke-width="1"/>
                        <path d="M42 35L45 30L50 33L55 30L58 35" stroke="white" stroke-width="2" fill="none"/>
                    </svg>
                </div>
                <h3>MAGIA LUPOS</h3>
                <p style="text-transform: uppercase; letter-spacing: 5px; font-size: 11px; color: #af9144; font-weight: 600; margin-top: 10px; opacity: 0.8;">Secured Jewellery Management Intelligence</p>
            </div>
            
            <div class="auth-body">
                @if(session('status') || request()->has('expired'))
                <div class="alert border-0 shadow-sm mb-4" role="alert" style="background: #1a1a1a; border-left: 4px solid #af9144 !important; border-radius: 0; padding: 20px;">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-info-circle" style="color: #af9144; font-size: 1.2rem;"></i>
                        </div>
                        <div>
                            <div style="font-size: 10px; text-transform: uppercase; letter-spacing: 2px; color: #af9144; font-weight: 700; margin-bottom: 4px;">Security Notification</div>
                            <div style="font-size: 13px; color: #ffffff; font-weight: 500; letter-spacing: 0.5px;">
                                @php
                                    $message = session('status');
                                    if (!$message && request()->has('expired')) {
                                        $reason = request()->query('reason');
                                        $message = match($reason) {
                                            'idle_timeout' => 'Session expired due to inactivity.',
                                            'heartbeat_lost' => 'Session closed for security.',
                                            'new_login_override' => 'Logged out because your account was accessed from another device.',
                                            'session_lost' => 'Security session lost. Please login again.',
                                            default => 'Session expired. Please login again.'
                                        };
                                    }
                                @endphp
                                {{ $message }}
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                
                @yield('content')
            </div>
        </div>
    </div>
    
    <script>
        // Clear active tab flag when on guest pages (like login)
        // This ensures Goal 2: http://127.0.0.1:8001/login ALWAYS behaves as a fresh entry
        sessionStorage.removeItem('magia_tab_active');

        document.addEventListener('mousemove', e => {
            const x = (e.clientX / window.innerWidth) * 100;
            const y = (e.clientY / window.innerHeight) * 100;
            document.querySelector('.flare').style.setProperty('--x', x + '%');
            document.querySelector('.flare').style.setProperty('--y', y + '%');
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>