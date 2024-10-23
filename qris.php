<?php
// Konfigurasi TokoPay
$merchantId = 'MERCHANT_ID'; // Ganti dengan Merchant ID Anda
$secret = 'SECRET_ANDA'; // Ganti dengan Secret Code Anda
$refId = 'TXO' . date('YmdHis'); // ID unik untuk transaksi ini, bisa gunakan timestamp
$nominal = INTEGER; // Jumlah pembayaran dalam rupiah
$metode = 'metode pembayaran'; // Metode pembayaran, bisa disesuaikan (contoh: 'QRIS', 'BRIVA', dll.)

// URL endpoint TokoPay
$apiUrl = "https://api.tokopay.id/v1/order?merchant=$merchantId&secret=$secret&ref_id=$refId&nominal=$nominal&metode=$metode";

// Menggunakan cURL untuk melakukan request ke TokoPay
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
curl_close($ch);

// Decode JSON response dari TokoPay
$responseData = json_decode($response, true);

// Debug: Tampilkan respon API untuk melihat detail
echo "<pre>";
print_r($responseData);
echo "</pre>";

// Periksa apakah status adalah Success
if (isset($responseData['status']) && $responseData['status'] === 'Success') {
    $qrCodeUrl = $responseData['data']['qr_link']; // Mengambil URL QR Code dari data
    $payUrl = $responseData['data']['pay_url']; // URL untuk melanjutkan pembayaran
    $expireTime = strtotime("+5 minutes"); // Anda dapat menyesuaikan ini jika diperlukan
} else {
    echo "Gagal membuat QRIS: " . ($responseData['message'] ?? 'Unknown error');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran QRIS</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; }
        .container { max-width: 400px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; }
        .qr-image { max-width: 100%; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Silahkan Melakukan Pembayaran</h2>
        <p>Untuk bisa mengakses link, Anda perlu melakukan pembayaran terlebih dahulu</p>
        <p>Jumlah yang harus dibayar: <strong>Rp <?= number_format($nominal, 0, ',', '.'); ?></strong></p>
        <p>Scan QR Code untuk Membayar:</p>
        <img src="<?= htmlspecialchars($qrCodeUrl); ?>" alt="QR Code" class="qr-image" />

        <p><small>Time left to complete payment: <span id="countdown"></span></small></p>
    </div>

    <script>
        // Countdown timer
        let expireTime = <?= json_encode($expireTime); ?>;
        let countdownElement = document.getElementById('countdown');

        function updateCountdown() {
            let now = new Date().getTime() / 1000; // Current time in seconds
            let timeLeft = expireTime - now;

            if (timeLeft <= 0) {
                countdownElement.innerHTML = "Expired";
            } else {
                let minutes = Math.floor(timeLeft / 60);
                let seconds = Math.floor(timeLeft % 60);
                countdownElement.innerHTML = minutes + ":" + (seconds < 10 ? '0' + seconds : seconds);
            }
        }

        setInterval(updateCountdown, 1000);
    </script>
</body>
</html>
