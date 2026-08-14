<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WA Scheduler API Documentation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/atom-one-dark.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 900px; margin-top: 40px; margin-bottom: 50px; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 24px; }
        .card-header { background-color: #fff; border-bottom: 1px solid #edf2f7; font-weight: 600; padding: 16px 24px; border-radius: 12px 12px 0 0 !important; }
        pre { margin: 0; border-radius: 8px; }
        .endpoint-badge { background-color: #198754; color: white; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-family: monospace; }
        .endpoint-url { font-family: monospace; font-size: 1.1em; color: #333; margin-left: 10px; }
    </style>
</head>
<body>

<div class="container">
    <h1 class="mb-2">WA Scheduler API Documentation</h1>
    <p class="text-muted mb-4">Gunakan API ini untuk mengirim jadwal pesan WhatsApp langsung dari aplikasi atau website Anda.</p>

    <!-- INFO BOX TENTANG PENGIRIMAN -->
    <div class="alert alert-info d-flex align-items-center" role="alert">
        <div>
            <h4 class="alert-heading"><i class="fas fa-info-circle"></i> Informasi Penting Pengiriman</h4>
            <hr>
            <p class="mb-0">Perlu diketahui bahwa <strong>semua pesan yang dijadwalkan melalui API ini akan dikirimkan menggunakan Nomor WhatsApp Admin</strong> (nomor HP di mana aplikasi Android WA Scheduler terinstal).</p>
            <p class="mt-2 mb-0">Teman-teman atau klien yang menggunakan API ini hanya menitipkan jadwal pesan. Pengirim asli yang terlihat oleh penerima pesan nantinya adalah nomor Anda (Admin). Pastikan teman yang Anda berikan akses API Key mengerti akan hal ini untuk menghindari kesalahpahaman.</p>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header text-primary">
            1. Endpoint
        </div>
        <div class="card-body">
            <span class="endpoint-badge">POST</span>
            <span class="endpoint-url">https://wa.quizb.my.id/api/send.php</span>
        </div>
    </div>

    <div class="card">
        <div class="card-header text-primary">
            2. Header Authentication (Wajib)
        </div>
        <div class="card-body">
            <p>Setiap request wajib menyertakan header <code>x-api-key</code>.</p>
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Key</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>x-api-key</code></td>
                        <td><code>WA-API-SECRET-KEY</code> (Sesuai dengan yang ada di <code>config/api_keys.php</code>)</td>
                    </tr>
                    <tr>
                        <td><code>Content-Type</code></td>
                        <td><code>application/json</code></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header text-primary">
            3. Body Parameters (JSON)
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Parameter</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Example</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>phone_number</code></td>
                        <td>String</td>
                        <td>Nomor tujuan WhatsApp (gunakan awalan 62 atau kode negara lain).</td>
                        <td>"6281234567890"</td>
                    </tr>
                    <tr>
                        <td><code>message</code></td>
                        <td>String</td>
                        <td>Isi pesan WhatsApp yang akan dikirim.</td>
                        <td>"Halo, ini adalah pesan tes."</td>
                    </tr>
                    <tr>
                        <td><code>scheduled_time</code></td>
                        <td>String</td>
                        <td>Waktu pengiriman dalam format <code>YYYY-MM-DDTHH:MM</code></td>
                        <td>"2026-10-25T15:30"</td>
                    </tr>
                    <tr>
                        <td><code>is_loop</code> <span class="badge bg-secondary">Optional</span></td>
                        <td>Integer</td>
                        <td>Nilai <code>1</code> jika pesan ingin diulang, <code>0</code> jika tidak. Default: 0.</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td><code>loop_interval</code> <span class="badge bg-secondary">Optional</span></td>
                        <td>String</td>
                        <td>Interval pengulangan jika is_loop = 1. Opsi valid: <code>daily</code>, <code>weekly</code>, <code>monthly</code>.</td>
                        <td>"daily"</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header text-primary">
            4. Contoh Penggunaan (Code Snippets)
        </div>
        <div class="card-body">
            
            <h5 class="mt-2">PHP (cURL)</h5>
            <pre><code class="language-php">&lt;?php
$url = "https://wa.quizb.my.id/api/send.php";

$data = [
    "phone_number" => "6281234567890",
    "message" => "Pesan dari API!",
    "scheduled_time" => date("Y-m-d\TH:i", strtotime("+5 minutes")),
    "is_loop" => 1,
    "loop_interval" => "daily"
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "x-api-key: WA-API-SECRET-KEY"
]);

$response = curl_exec($ch);
curl_close($ch);

echo $response;
?&gt;</code></pre>

            <h5 class="mt-4">JavaScript (Fetch API)</h5>
            <pre><code class="language-javascript">const data = {
    phone_number: "6281234567890",
    message: "Pesan dari API!",
    scheduled_time: "2026-10-25T15:30",
    is_loop: 1,
    loop_interval: "daily"
};

fetch('https://wa.quizb.my.id/api/send.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'x-api-key': 'WA-API-SECRET-KEY'
    },
    body: JSON.stringify(data)
})
.then(response => response.json())
.then(data => console.log(data))
.catch(error => console.error('Error:', error));</code></pre>

            <h5 class="mt-4">cURL Command Line</h5>
            <pre><code class="language-bash">curl -X POST https://wa.quizb.my.id/api/send.php \
-H "Content-Type: application/json" \
-H "x-api-key: WA-API-SECRET-KEY" \
-d '{
    "phone_number": "6281234567890",
    "message": "Pesan dari API!",
    "scheduled_time": "2026-10-25T15:30",
    "is_loop": 1,
    "loop_interval": "daily"
}'</code></pre>

        </div>
    </div>
    
    <div class="card">
        <div class="card-header text-primary">
            5. Contoh Response
        </div>
        <div class="card-body">
            <p><strong>Sukses (200 OK)</strong></p>
            <pre><code class="language-json">{
  "status": "success",
  "message": "Schedule successfully created",
  "data": {
    "id": "145",
    "phone_number": "6281234567890",
    "scheduled_time": "2026-10-25T15:30"
  }
}</code></pre>

            <p class="mt-3"><strong>Gagal (401 Unauthorized - API Key Salah)</strong></p>
            <pre><code class="language-json">{
  "status": "error",
  "message": "Invalid API Key"
}</code></pre>
        </div>
    </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
<script>hljs.highlightAll();</script>
</body>
</html>
