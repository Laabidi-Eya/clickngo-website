<?php
header('Content-Type: application/json');
require_once(__DIR__ . "/../../vendor/autoload.php");
require_once(__DIR__ . "/../../controller/controller.php");

\Stripe\Stripe::setApiKey('sk_test_51RLtBORvSkgkxHMRdCHR0AarFMZFvjbKHb6lFEZquem1sM7PG7QmirYdsTLuGZ0N4hruAPlVu82S3QgjoXyOLheN00Pm2sGlPT');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['action']) || !isset($data['id_sponsor']) || !isset($data['payment_code'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$action = $data['action'];
$id_sponsor = (int)$data['id_sponsor'];
$payment_code = trim($data['payment_code']);

$controller = new sponsorController();
$sponsor = $controller->getSponsorById($id_sponsor);

if (!$sponsor) {
    echo json_encode(['success' => false, 'message' => 'Sponsor not found']);
    exit;
}

if (!isset($sponsor['payment_code']) || $sponsor['payment_code'] !== $payment_code) {
    echo json_encode(['success' => false, 'message' => 'Invalid payment code']);
    exit;
}

// Log errors to a file for debugging
function logError($message) {
    file_put_contents(__DIR__ . '/payment_errors.log', date('Y-m-d H:i:s') . ": $message\n", FILE_APPEND);
}

if ($action === 'create') {
    $amount = isset($sponsor['montant']) ? (int)($sponsor['montant'] * 100) : 0;

    if ($amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid amount']);
        exit;
    }

    try {
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $amount,
            'currency' => 'usd', // Changed to 'usd' as TND may be unsupported
            'metadata' => [
                'id_sponsor' => $id_sponsor,
                'payment_code' => $payment_code
            ]
        ]);

        echo json_encode([
            'success' => true,
            'clientSecret' => $paymentIntent->client_secret
        ]);
    } catch (\Stripe\Exception\ApiErrorException $e) {
        logError('Stripe Error (create): ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Payment processing error: ' . $e->getMessage()
        ]);
    } catch (Exception $e) {
        logError('General Error (create): ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Server error, please try again later'
        ]);
    }
} elseif ($action === 'verify') {
    if (!isset($data['payment_intent_id'])) {
        echo json_encode(['success' => false, 'message' => 'Missing payment intent ID']);
        exit;
    }

    $payment_intent_id = $data['payment_intent_id'];

    try {
        $paymentIntent = \Stripe\PaymentIntent::retrieve($payment_intent_id);

        if ($paymentIntent->status === 'succeeded') {
            // Update database to mark payment as completed
            // Uncomment and implement the following:
            // $controller->markPaymentCompleted($id_sponsor);
            echo json_encode(['success' => true, 'message' => 'Payment verified successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Payment not completed']);
        }
    } catch (\Stripe\Exception\ApiErrorException $e) {
        logError('Stripe Error (verify): ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Payment verification error: ' . $e->getMessage()
        ]);
    } catch (Exception $e) {
        logError('General Error (verify): ' . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Server error, please try again later'
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>