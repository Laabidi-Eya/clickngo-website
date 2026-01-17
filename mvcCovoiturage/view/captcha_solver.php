<?php
// Manual inclusion of 2Captcha library
require_once '2captcha-php/src/TwoCaptcha.php';
use TwoCaptcha\TwoCaptcha;

// Your 2Captcha API key
$apiKey = '4d9bd7f7d24503b8fc94aa983a33588a';

// Initialize 2Captcha solver
$solver = new TwoCaptcha([
    'apiKey' => $apiKey,
    'defaultTimeout' => 120, // Timeout for most captchas (seconds)
    'pollingInterval' => 10  // Interval between result checks (seconds)
]);

// Handle form submission
$result = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['captcha_file'])) {
    try {
        // Check if file was uploaded without errors
        if ($_FILES['captcha_file']['error'] === UPLOAD_ERR_OK) {
            $filePath = $_FILES['captcha_file']['tmp_name'];

            // Solve the CAPTCHA
            $result = $solver->normal($filePath);
        } else {
            $error = "Error uploading file: " . $_FILES['captcha_file']['error'];
        }
    } catch (Exception $e) {
        $error = "Error solving CAPTCHA: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAPTCHA Solver with 2Captcha API</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background: #f4f4f4;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background: #45a049;
        }
        .result, .error {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
        }
        .result {
            background: #dff0d8;
            color: #3c763d;
        }
        .error {
            background: #f2dede;
            color: #a94442;
        }
    </style>
</head>
<body>
    <h1>CAPTCHA Solver with 2Captcha API</h1>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="captcha_file">Upload CAPTCHA Image:</label>
            <input type="file" name="captcha_file" id="captcha_file" accept="image/*" required>
        </div>
        <button type="submit">Solve CAPTCHA</button>
    </form>

    <?php if ($result): ?>
        <div class="result">
            CAPTCHA Solved! The text is: <strong><?php echo htmlspecialchars($result->code); ?></strong>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
</body>
</html>