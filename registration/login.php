<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Educational Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" href="../IMAGES/3.png" type="image/x-icon">
    <style>
        @keyframes gradientBG {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }
        body {
            background: linear-gradient(45deg, #3b82f6, #9333ea, #f59e0b, #10b981);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            font-family: 'Nunito', sans-serif;
        }
        .btn-login {
            transition: background-color 0.3s ease, transform 0.3s ease;
        }
        .btn-login:hover {
            background-color: #2b6cb0;
            transform: translateY(-2px);
        }
        .info-text {
            color: #4a5568; /* Tailwind's gray-600 */
        }
        .btn-icon {
            display: inline-block;
            margin-right: 8px;
        }
    </style>
</head>
<body>

<div class="min-h-screen flex flex-col items-center justify-center py-12 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <h1 class="text-4xl font-extrabold text-gray-900">Welcome Back!</h1>
            <p class="info-text mt-2 mb-8">Contact your institution for login information if your school is registered.</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-4">
            <a href="../mystudents/login_student.php" class="btn-login bg-gradient-to-r from-blue-500 to-blue-700 text-white font-bold py-2 px-4 rounded-full w-full text-center flex items-center justify-center shadow-lg hover:shadow-xl">
                <i class="fas fa-user-graduate btn-icon"></i>
                Login as Student
            </a>
            <a href="../myteachers/login_teacher.php" class="btn-login bg-gradient-to-r from-blue-500 to-blue-700 text-white font-bold py-2 px-4 rounded-full w-full text-center flex items-center justify-center shadow-lg hover:shadow-xl">
                <i class="fas fa-chalkboard-teacher btn-icon"></i>
                Login as Teacher
            </a>
        </div>
        <div class="text-center mt-6">
            <p class="info-text">
                Is your school not yet registered? <a href="subscription/subscribe.php" class="text-blue-600 hover:text-blue-800 transition-colors">Register School</a>
            </p>
        </div>
    </div>
</div>

</body>
</html>
