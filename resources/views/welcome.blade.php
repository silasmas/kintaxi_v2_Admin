<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kintaxi Admin</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Figtree', sans-serif;
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #ffffff;
            color: #0f172a;
        }

        .card {
            background: #ffffff;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.10);
            padding: 2.5rem 3rem;
            border-radius: 1.5rem;
            text-align: center;
            max-width: 520px;
            border: 1px solid #e5e7eb;
        }

        .logo-wrapper {
            display: flex;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .logo-wrapper img {
            height: 72px;
            width: auto;
        }

        h1 {
            margin: 0 0 0.75rem;
            font-size: 2rem;
            letter-spacing: 0.04em;
            color: #0f172a;
        }

        .subtitle {
            margin: 0 0 1.5rem;
            font-size: 0.98rem;
            color: #4b5563;
        }

        .meta {
            margin-bottom: 2.1rem;
            font-size: 0.9rem;
            color: #9ca3af;
        }

        a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            padding: 0.8rem 1.9rem;
            border-radius: 9999px;
            background: linear-gradient(135deg, #facc15, #f97316);
            color: #1f2937;
            font-weight: 600;
            font-size: 0.96rem;
            text-decoration: none;
            box-shadow: 0 15px 35px rgba(250, 204, 21, 0.45);
            transition: transform 0.16s ease, box-shadow 0.16s ease, background 0.16s ease;
        }

        a:hover {
            transform: translateY(-1.5px);
            background: linear-gradient(135deg, #fde047, #facc15);
            box-shadow: 0 20px 45px rgba(250, 204, 21, 0.6);
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo-wrapper">
            <img src="{{ asset('assets/img/logo-text.png') }}" alt="Logo Kintaxi">
        </div>
        <h1>Kintaxi Admin</h1>
        <p class="subtitle">Console d'administration de la plateforme Kintaxi.</p>
        <p>
            <a href="{{ url('/admin') }}">
                Accéder au tableau de bord Kintaxi
            </a>
        </p>
    </div>
</body>
</html>
