<?php
require '../config.php';
checkAccess('member');

$booking_id = $_GET['id'];
$amount = $_GET['amount'];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - Beautybar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
    .payment-box {
        display: none;
        animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    </style>
</head>

<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="member.php" class="inline-flex items-center text-purple-600 hover:text-purple-700 font-medium">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Dashboard
            </a>
        </div>

        <!-- Payment Card -->
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                <!-- Header -->
                <div class="bg-purple-600 px-6 py-8 text-white text-center">
                    <div class="mb-4">
                        <svg class="w-16 h-16 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                            </path>
                        </svg>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-bold mb-2">Pilih Metode Pembayaran</h1>
                    <p class="text-purple-100">Total Tagihan:</p>
                    <p class="text-3xl md:text-4xl font-bold mt-2">Rp <?php echo number_format($amount, 0, ',', '.'); ?>
                    </p>
                </div>

                <!-- Form -->
                <form action="payment_process.php" method="POST" enctype="multipart/form-data" class="p-6 md:p-8">
                    <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                    <input type="hidden" name="amount" value="<?php echo $amount; ?>">

                    <!-- Payment Method Selection -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Pilih Bank / E-Wallet</label>
                        <select name="method" id="paymentMethod" onchange="showPaymentDetails()" required
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                            <option value="">-- Pilih Salah Satu --</option>
                            <option value="BCA">🏦 Bank BCA (Transfer)</option>
                            <option value="QRIS">📱 QRIS (Scan Barcode)</option>
                        </select>
                    </div>

                    <!-- BCA Payment Info -->
                    <div id="info_BCA" class="payment-box mb-6">
                        <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-6 text-center">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia.svg"
                                alt="BCA" class="h-12 mx-auto mb-4">
                            <p class="text-sm text-gray-600 mb-2">Nomor Rekening BCA:</p>
                            <div class="bg-white rounded-lg p-4 mb-3">
                                <p class="text-2xl md:text-3xl font-bold text-gray-800 tracking-wider" id="text_BCA">
                                    0201993198</p>
                            </div>
                            <p class="text-sm text-gray-600 mb-3">a.n Rifki Abdullah</p>
                            <button type="button" onclick="copyToClipboard('text_BCA')"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-semibold">
                                📋 Salin Nomor
                            </button>
                        </div>
                    </div>

                    <!-- QRIS Payment Info -->
                    <div id="info_QRIS" class="payment-box mb-6">
                        <div class="bg-purple-50 border-2 border-purple-200 rounded-xl p-6 text-center">
                            <p class="text-sm font-semibold text-gray-700 mb-4">Scan QRIS di bawah ini:</p>
                            <div class="bg-white rounded-lg p-4 inline-block mb-3">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/d/d0/QR_code_for_mobile_English_Wikipedia.svg/1200px-QR_code_for_mobile_English_Wikipedia.svg.png"
                                    alt="QRIS" class="w-48 h-48 mx-auto">
                            </div>
                            <p class="text-sm text-gray-600">NMID: ID10200300400</p>
                        </div>
                    </div>

                    <!-- Upload Proof -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Upload Bukti Transfer</label>
                        <div
                            class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-purple-400 transition">
                            <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                            <input type="file" name="proof" accept="image/*" required
                                class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer">
                            <p class="text-xs text-gray-500 mt-2">Format: JPG/PNG, Maksimal 2MB</p>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="member.php"
                            class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition text-center">
                            Batal
                        </a>
                        <button type="submit" name="upload"
                            class="flex-1 px-6 py-3 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition shadow-lg">
                            💳 Kirim Bukti Pembayaran
                        </button>
                    </div>
                </form>
            </div>

            <!-- Info Box -->
            <div class="mt-6 bg-white rounded-xl shadow-md p-6">
                <h3 class="font-bold text-gray-800 mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Petunjuk Pembayaran
                </h3>
                <ol class="list-decimal list-inside space-y-2 text-sm text-gray-600">
                    <li>Pilih metode pembayaran yang Anda inginkan</li>
                    <li>Transfer sesuai nominal yang tertera</li>
                    <li>Upload bukti transfer (screenshot/foto)</li>
                    <li>Tunggu konfirmasi dari admin (maksimal 1x24 jam)</li>
                    <li>Status pembayaran akan diperbarui di dashboard Anda</li>
                </ol>
            </div>
        </div>
    </div>

    <script>
    function showPaymentDetails() {
        const selectedBank = document.getElementById("paymentMethod").value;
        const allBoxes = document.getElementsByClassName("payment-box");

        // Hide all payment boxes
        for (let i = 0; i < allBoxes.length; i++) {
            allBoxes[i].style.display = "none";
        }

        // Show selected payment box
        if (selectedBank !== "") {
            const targetId = "info_" + selectedBank;
            const targetElement = document.getElementById(targetId);
            if (targetElement) {
                targetElement.style.display = "block";
            }
        }
    }

    function copyToClipboard(elementId) {
        const copyText = document.getElementById(elementId).innerText;
        navigator.clipboard.writeText(copyText).then(function() {
            // Show success message
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '✓ Tersalin!';
            btn.classList.add('bg-green-600', 'hover:bg-green-700');

            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.classList.remove('bg-green-600', 'hover:bg-green-700');
            }, 2000);
        }, function(err) {
            console.error('Gagal menyalin: ', err);
            alert('Gagal menyalin. Silakan salin manual.');
        });
    }
    </script>
</body>

</html>