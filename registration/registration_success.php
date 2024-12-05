<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="icon" href="../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Nunito', sans-serif;
        }
        .container {
            animation: fadeIn 1s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .btn {
            transition: transform 0.3s ease, background-color 0.3s ease;
        }
        .btn:hover {
            transform: translateY(-3px);
            background-color: #1d4ed8;
        }
    </style>
</head>
<body>
    <div class="container mx-auto p-8">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-3xl font-semibold text-gray-800 text-center mb-6">Registration Successful!</h2>
            <p class="text-lg text-center text-gray-700 mb-6">Your account has been successfully created.</p>
            <div class="text-center">
                <a href="myschool_login.php" class="btn inline-block bg-blue-500 text-white font-bold py-2 px-4 rounded hover:bg-blue-600">
                    Log In
                </a>
                <a href="../index.php" class="btn inline-block bg-blue-500 text-white font-bold py-2 px-4 rounded hover:bg-blue-600 ml-4">
                    Home
                </a>
            </div>
        </div>
    </div>
</body>
</html>
