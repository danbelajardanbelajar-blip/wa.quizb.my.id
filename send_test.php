<?php
// send_test.php
// Script sederhana untuk mengetes API Pengiriman WhatsApp

// URL endpoint API Anda
$url = "https://wa.quizb.my.id/api/send.php";

// API Key yang sudah di-setting di config/api_keys.php
$api_key = "WA-API-SECRET-KEY";

// Ubah nomor dan pesan ini sesuai keinginan Anda
$data = [
    "phone_number" => "6281234567890", // Ganti dengan nomor tujuan asli
    "message" => "Halo! Ini pesan tes dari send_test.php",
    
    // Jadwalkan 1 menit dari sekarang
    "scheduled_time" => date("Y-m-d\TH:i", strtotime("+1 minutes"))
];

echo "Mencoba mengirim request ke API...\n<br><br>";
echo "Data yang dikirim:\n<br>";
echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre><br>";

// Proses pengiriman menggunakan cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "x-api-key: " . $api_key
]);

// Untuk menghindari error SSL saat testing
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo "Error cURL: " . curl_error($ch) . "\n<br>";
} else {
    echo "HTTP Status Code: " . $http_code . "\n<br><br>";
    echo "Response dari API:\n<br>";
    echo "<pre>" . json_encode(json_decode($response), JSON_PRETTY_PRINT) . "</pre>";
}

curl_close($ch);
?>
