<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Integrated Smart Road System') }} - @yield('title', 'Dashboard')</title>
    
    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine JS -->
    <script src="https://unpkg.com/alpinejs" defer></script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            200: '#a7f3d0',
                            300: '#6ee7b7',
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                            950: '#022c22',
                        },
                        dark: {
                            900: '#090814',
                            800: '#131127',
                            700: '#211d3e',
                            card: 'rgba(23, 21, 48, 0.4)',
                        }
                    },
                    animation: {
                        'fade-in-up': 'fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards',
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-glow': 'pulseGlow 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                    keyframes: {
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
                        },
                        pulseGlow: {
                            '0%, 100%': { opacity: '0.4' },
                            '50%': { opacity: '1' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            /* Sleek dark gradient background mimicking a digital space */
            background: radial-gradient(circle at top left, #131127, #090814 60%);
            background-color: #090814;
            color: #f8fafc;
            min-height: 100vh;
        }
        
        .glass-panel {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
        }

        /* Subtle grid overlay for tech aesthetic */
        .tech-grid {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(to right, rgba(255,255,255,0.02) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255,255,255,0.02) 1px, transparent 1px);
            background-size: 60px 60px;
            pointer-events: none;
            z-index: -1;
        }

        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #090814; 
        }
        ::-webkit-scrollbar-thumb {
            background: #211d3e; 
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #34d399; 
        }

        /* Stylized License Plate */
        .license-plate-card {
            background: #f8fafc;
            color: #0f172a;
            border: 3px solid #334155;
            border-radius: 12px;
            padding: 1rem 2rem;
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.4), 0 8px 10px -6px rgba(0, 0, 0, 0.4);
            min-width: 280px;
        }

        .license-plate-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 12px;
            background: linear-gradient(90deg, #1e40af, #3b82f6);
        }

        .license-plate-text {
            font-family: system-ui, -apple-system, sans-serif;
            font-size: 2.5rem;
            font-weight: 900;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            margin-top: 8px;
            text-shadow: 1px 1px 0px rgba(255,255,255,0.5);
        }

        .license-plate-label {
            font-size: 0.65rem;
            font-weight: 900;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.3em;
            margin-bottom: -4px;
        }
    </style>
</head>
<body class="antialiased selection:bg-primary-500/30 selection:text-primary-100 relative">
    <!-- Tech grid background -->
    <div class="tech-grid"></div>

    @include('components.navbar')

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 pt-24 animate-fade-in-up">
        @yield('content')
    </main>

    <!-- Initialize Lucide Icons -->
    <script>
        lucide.createIcons();
        document.addEventListener('alpine:initialized', () => {
            // Re-run lucide icons creation if Alpine modifies DOM
            lucide.createIcons();
        });
    </script>
</body>
</html>
