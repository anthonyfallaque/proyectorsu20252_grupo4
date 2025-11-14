<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal de Asistencia</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #74ebd5, #9face6);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background: white;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 420px;
        }

        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 1.5rem;
        }

        fieldset {
            border: none;
            padding: 0;
        }

        legend {
            font-size: 1.3rem;
            font-weight: 600;
            color: #34495e;
            margin-bottom: 1.2rem;
        }

        input {
            width: 100%;
            padding: 0.9rem;
            margin-bottom: 1rem;
            border: 1px solid #ccc;
            border-radius: 10px;
            font-size: 1rem;
            transition: 0.3s ease;
        }

        input:focus {
            border-color: #5dade2;
            outline: none;
            box-shadow: 0 0 5px rgba(93, 173, 226, 0.4);
        }

        button {
            width: 100%;
            padding: 0.9rem;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #2980b9;
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 10px;
            font-weight: 500;
            text-align: center;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 500px) {
            .container {
                margin: 1rem;
                padding: 2rem;
            }
        }
    </style>
</head>

<body>
    <div class="container">

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('attendances.storeAttendance') }}" method="post">
            @csrf
            <h1>Bienvenido</h1>
            <fieldset>
                <legend>Registro de Asistencia</legend>
                <input type="text" name="dni" placeholder="Documento de identidad" required>
                <input type="password" name="password" placeholder="Contraseña" required>
                <button type="submit">Registrar Asistencia</button>
            </fieldset>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: "{{ session('success') }}",
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Aceptar'
            });
            setTimeout(() => {
                window.location.href = '/attendances';
            }, 2000);
        </script>
    @endif
</body>

</html>
