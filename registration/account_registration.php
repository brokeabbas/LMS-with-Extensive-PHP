<?php
session_start(); // Start or resume a session at the very beginning

require_once '../connections/db.php';  // Adjust the path as needed to your database connection settings

$schoolName = '';

// Handle the POST request for school code verification
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['checkCode'])) {
    $schoolCode = mysqli_real_escape_string($userInfoConn, $_POST['schoolCode']);

    // Check if school code has already been registered
    $checkRegistered = $userInfoConn->prepare("SELECT id FROM registered_school_code WHERE school_code = ?");
    if ($checkRegistered) {
        $checkRegistered->bind_param("s", $schoolCode);
        $checkRegistered->execute();
        $checkRegistered->store_result();
        if ($checkRegistered->num_rows > 0) {
            $_SESSION['error'] = "School Already Registered.";
            $checkRegistered->close();
        } else {
            // Proceed to fetch school details if not registered
            $stmt = $userInfoConn->prepare("SELECT school_id, school_name FROM schools WHERE school_code = ?");
            if ($stmt) {
                $stmt->bind_param("s", $schoolCode);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $schoolName = $row['school_name'];
                    $_SESSION['schoolCode'] = $schoolCode;  // Store school code in the session
                    $_SESSION['schoolId'] = $row['school_id'];  // Store school ID in the session
                } else {
                    $_SESSION['error'] = "No school found with that code.";
                }
                $stmt->close();
            } else {
                $_SESSION['error'] = "Database query error: " . $userInfoConn->error;
            }
        }
    } else {
        $_SESSION['error'] = "Database query error: " . $userInfoConn->error;
    }
}

// Handle the POST request for user registration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['registerUser']) && isset($_SESSION['schoolCode']) && isset($_SESSION['schoolId'])) {
    $username = mysqli_real_escape_string($userInfoConn, $_POST['username']);
    $password = mysqli_real_escape_string($userInfoConn, $_POST['password']);
    $confirmPassword = mysqli_real_escape_string($userInfoConn, $_POST['confirmPassword']);

    if ($password !== $confirmPassword) {
        $_SESSION['error'] = "Passwords do not match.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $schoolCode = $_SESSION['schoolCode'];
        $schoolId = $_SESSION['schoolId'];

        // Insert new user record into school_users
        $stmt = $userInfoConn->prepare("INSERT INTO school_users (school_id, username, password) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("iss", $schoolId, $username, $hashedPassword);
            if ($stmt->execute()) {
                // Log the used school code
                $stmtLog = $userInfoConn->prepare("INSERT INTO registered_school_code (school_id, school_code) VALUES (?, ?)");
                if ($stmtLog) {
                    $stmtLog->bind_param("is", $schoolId, $schoolCode);
                    $stmtLog->execute();
                    $stmtLog->close();
                }

                header('Location: registration_success.php'); // Redirect on successful registration
                exit;
            } else {
                $_SESSION['error'] = "Failed to register user: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $_SESSION['error'] = "Database insert error: " . $userInfoConn->error;
        }
    }
}

$userInfoConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Registration - School Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="icon" href="../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }
        .container {
            animation: fadeIn 1s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .input-group {
            position: relative;
        }
        .input-icon {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            pointer-events: none;
        }
        .input-field {
            padding-right: 2.5rem;
            background-color: #ffffff;
            color: #1e3a8a;
        }
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #1e3a8a;
        }
        .checkbox-label input {
            accent-color: #1e3a8a;
        }
        .submit-button:disabled {
            background-color: #a1a1aa;
            cursor: not-allowed;
        }
        /* Popup styles */
        .popup {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            width: 80%;
            max-width: 600px;
            background: white;
            color: black;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
            max-height: 80vh;
        }
        .popup h2 {
            margin-top: 0;
        }
        .popup .close-btn {
            display: block;
            text-align: right;
            margin-top: 10px;
        }
        .popup .close-btn button {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        .popup .close-btn button:hover {
            background: #764ba2;
        }
    </style>
    <script>
        // JavaScript to handle the popup
        document.addEventListener('DOMContentLoaded', function() {
            var termsLink = document.getElementById('termsLink');
            var privacyLink = document.getElementById('privacyLink');
            var popup = document.getElementById('popup');
            var closeBtn = document.getElementById('closePopup');

            termsLink.addEventListener('click', function(event) {
                event.preventDefault();
                document.getElementById('popupContent').innerHTML = getTermsAndConditions();
                popup.style.display = 'block';
            });

            privacyLink.addEventListener('click', function(event) {
                event.preventDefault();
                document.getElementById('popupContent').innerHTML = getPrivacyPolicy();
                popup.style.display = 'block';
            });

            closeBtn.addEventListener('click', function() {
                popup.style.display = 'none';
            });

            function getTermsAndConditions() {
                return `
                    <h2>Terms and Conditions</h2>
                    <p>These terms and conditions outline the rules and regulations for the use of School Hub.</p>
                    <h3>1. Terms</h3>
                    <p>By accessing this system, you are agreeing to be bound by these terms and conditions of use, all applicable laws, and regulations, and agree that you are responsible for compliance with any applicable local laws.</p>
                    <h3>2. Use License</h3>
                    <p>Permission is granted to temporarily download one copy of the materials (information or software) on the School Management System's website for personal, non-commercial transitory viewing only.</p>
                    <h3>3. Disclaimer</h3>
                    <p>The materials on the School Hub's website are provided "as is". School Hub makes no warranties, expressed or implied, and hereby disclaims and negates all other warranties.</p>
                    <h3>4. Limitations</h3>
                    <p>In no event shall School Hub or its suppliers be liable for any damages (including, without limitation, damages for loss of data or profit, or due to business interruption).</p>
                    <h3>5. Revisions and Errata</h3>
                    <p>The materials appearing on School Hub's website could include technical, typographical, or photographic errors.</p>
                    <h3>6. Links</h3>
                    <p>School Hub has not reviewed all of the sites linked to its Internet website and is not responsible for the contents of any such linked site.</p>
                    <h3>7. Site Terms of Use Modifications</h3>
                    <p>School Hub may revise these terms of use for its website at any time without notice.</p>
                    <h3>8. Governing Law</h3>
                    <p>Any claim relating to School Hub's website shall be governed by the laws of the State without regard to its conflict of law provisions.</p>
                `;
            }

            function getPrivacyPolicy() {
                return `
                    <h2>Privacy Policy</h2>
                    <p>Your privacy is important to us. It is School Hub's policy to respect your privacy regarding any information we may collect while operating our website.</p>
                    <h3>1. Information We Collect</h3>
                    <p>We collect information you provide directly to us when you create or modify your account, request on-demand services, contact customer support, or otherwise communicate with us.</p>
                    <h3>2. Use of Information</h3>
                    <p>We use the information we collect to provide, maintain, and improve our services, including to process transactions, manage accounts, and provide customer support.</p>
                    <h3>3. Sharing of Information</h3>
                    <p>We do not share your personal information with third parties except as necessary to comply with legal obligations, prevent fraud, resolve disputes, and enforce our agreements.</p>
                    <h3>4. Data Security</h3>
                    <p>We implement appropriate technical and organizational measures to protect the security of your personal data.</p>
                    <h3>5. Changes to This Policy</h3>
                    <p>We may update this Privacy Policy from time to time. If we make significant changes, we will notify you of the changes through the services or through others means, such as email.</p>
                    <h3>6. Contact Information</h3>
                    <p>If you have any questions about this Privacy Policy, please contact us at support@schoolhub.ng</p>
                `;
            }
        });
    </script>
</head>
<body>
    <div class="container max-w-3xl bg-white rounded-lg shadow-lg p-8">
        <h2 class="text-4xl font-semibold text-gray-800 text-center mb-6">Account Registration</h2>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-500 text-white p-4 rounded-lg mb-6"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <?php if ($schoolName): ?>
            <p class="text-green-500 mb-4">School Name: <?= htmlspecialchars($schoolName) ?></p>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                <input type="hidden" name="registerUser" value="1">
                <div class="input-group mb-4">
                    <label for="username" class="block text-lg font-medium text-gray-700">Username:</label>
                    <input type="text" id="username" name="username" required class="input-field mt-1 block w-full px-4 py-3 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="input-group mb-4">
                    <label for="password" class="block text-lg font-medium text-gray-700">Password:</label>
                    <input type="password" id="password" name="password" required class="input-field mt-1 block w-full px-4 py-3 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="input-group mb-4">
                    <label for="confirmPassword" class="block text-lg font-medium text-gray-700">Confirm Password:</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required class="input-field mt-1 block w-full px-4 py-3 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="flex items-center mb-4">
                    <input type="checkbox" id="terms" name="terms" required class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="terms" class="ml-2 block text-sm text-gray-900">I agree to the <a href="#" id="termsLink" class="text-blue-600 hover:text-blue-800">Terms and Conditions</a></label>
                </div>
                <div class="flex items-center mb-4">
                    <input type="checkbox" id="privacy" name="privacy" required class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="privacy" class="ml-2 block text-sm text-gray-900">I agree to the <a href="#" id="privacyLink" class="text-blue-600 hover:text-blue-800">Privacy Policy</a></label>
                </div>
                <div class="flex justify-center">
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Register
                    </button>
                </div>
            </form>
        <?php else: ?>
            <p class="text-blue-500 mb-4">Note: The school code will be sent to your registered email.</p>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                <div class="input-group mb-4">
                    <label for="schoolCode" class="block text-lg font-medium text-gray-700">School Code:</label>
                    <input type="text" id="schoolCode" name="schoolCode" required class="input-field mt-1 block w-full px-4 py-3 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="flex justify-center">
                    <button type="submit" name="checkCode" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Check Code
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
    <!-- Popup Modal -->
    <div id="popup" class="popup">
        <div id="popupContent"></div>
        <div class="close-btn">
            <button id="closePopup">Close</button>
        </div>
    </div>
</body>
</html>
