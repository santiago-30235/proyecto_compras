<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión / Registrarse</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md border border-gray-200">
        <div class="text-center mb-8">
            <img src="{{ asset('backend/dist/img/image_login.jpeg') }}" 
                 alt="Logo de la Página" 
                 class="mx-auto h-24 w-auto mb-4 object-contain rounded-xl drop-shadow-md">
                 
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Bienvenido</h1>
            <p class="text-gray-600">Elige una opción para continuar</p>
        </div>
        
        <div class="space-y-4">
            <a href="{{ route('register') }}" 
               class="block w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-4 px-6 rounded-xl text-lg text-center transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                📝 Registrarse
            </a>
            
            <a href="{{ route('login') }}" 
               class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-4 px-6 rounded-xl text-lg text-center transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                🔑 Iniciar Sesión
            </a>
        </div>
        
        <div class="mt-8 text-center">
            <p class="text-sm text-gray-500">¿Problemas? <a href="mailto:soporte-compras@ufpso.edu.co?subject=Soporte%20Sistema%20de%20Compras&body=Hola,%20tengo%20problemas%20para%20ingresar%20al%20sistema." class="text-blue-600 hover:underline">Contáctanos</a></p>
        </div>
    </div>
</body>
</html>