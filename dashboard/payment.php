<?php
require '../config.php';
checkAccess('member');

$booking_id = $_GET['id'];
$amount = $_GET['amount'];
?>

<!DOCTYPE html>
<html>

<head>
    <title>Pembayaran - Beautybar</title>
    <link rel="stylesheet" href="../style.css">
    <style>
    /* CSS Khusus untuk Tampilan Pembayaran */
    .payment-box {
        display: none;
        /* Sembunyikan semua info bank secara default */
        background: #f1f8ff;
        border: 1px dashed #007bff;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        text-align: center;
        animation: fadeIn 0.5s;
    }

    .payment-box h3 {
        color: var(--primary);
        margin: 5px 0;
    }

    .payment-box .va-number {
        font-size: 1.5rem;
        font-weight: bold;
        letter-spacing: 2px;
        color: #333;
    }

    .payment-box p {
        margin: 5px 0;
        color: #555;
    }

    .copy-btn {
        background: #ddd;
        color: #333;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 0.8rem;
        cursor: pointer;
        border: none;
        margin-top: 5px;
    }

    .copy-btn:hover {
        background: #ccc;
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

<body>
    <div class="container" style="max-width: 500px; margin-top: 50px;">
        <div class="card">
            <h2 style="text-align:center; color:var(--primary);">Pilih Metode Pembayaran</h2>
            <p style="text-align:center;">Total Tagihan: <b>Rp <?php echo number_format($amount); ?></b></p>
            <hr style="margin: 15px 0;">

            <form action="payment_process.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                <input type="hidden" name="amount" value="<?php echo $amount; ?>">

                <label>Pilih Bank / E-Wallet</label>
                <select name="method" id="paymentMethod" onchange="showPaymentDetails()" required>
                    <option value="">-- Pilih Salah Satu --</option>
                    <option value="BRI">Bank BRI (Virtual Account)</option>
                    <option value="BCA">Bank BCA (Transfer)</option>
                    <option value="Mandiri">Bank Mandiri</option>
                    <option value="DANA">E-Wallet DANA</option>
                    <option value="QRIS">QRIS (Scan Barcode)</option>
                </select>

                <div id="info_BRI" class="payment-box">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/6/68/BANK_BRI_logo.svg" width="100"
                        style="margin-bottom:10px;">
                    <p>Nomor Virtual Account (BRIVA):</p>
                    <div class="va-number" id="text_BRI">8801234567890</div>
                    <p>a.n Beautybar Official</p>
                    <button type="button" class="copy-btn" onclick="copyToClipboard('text_BRI')">Salin Nomor</button>
                </div>

                <div id="info_BCA" class="payment-box">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia.svg" width="100"
                        style="margin-bottom:10px;">
                    <p>Nomor Rekening BCA:</p>
                    <div class="va-number" id="text_BCA">123 456 7890</div>
                    <p>a.n Rifki Abdullah</p>
                    <button type="button" class="copy-btn" onclick="copyToClipboard('text_BCA')">Salin Nomor</button>
                </div>

                <div id="info_Mandiri" class="payment-box">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/a/ad/Bank_Mandiri_logo_2016.svg"
                        width="100" style="margin-bottom:10px;">
                    <p>Nomor Rekening Mandiri:</p>
                    <div class="va-number" id="text_Mandiri">111 000 456 789</div>
                    <p>a.n Beautybar Official</p>
                    <button type="button" class="copy-btn" onclick="copyToClipboard('text_Mandiri')">Salin
                        Nomor</button>
                </div>

                <div id="info_DANA" class="payment-box">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/7/72/Logo_dana_blue.svg" width="100"
                        style="margin-bottom:10px;">
                    <p>Nomor DANA:</p>
                    <div class="va-number" id="text_DANA">0812 3456 7890</div>
                    <p>a.n Beauty Admin</p>
                    <button type="button" class="copy-btn" onclick="copyToClipboard('text_DANA')">Salin Nomor</button>
                </div>

                <div id="info_QRIS" class="payment-box">
                    <p>Scan QRIS di bawah ini:</p>
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/d/d0/QR_code_for_mobile_English_Wikipedia.svg/1200px-QR_code_for_mobile_English_Wikipedia.svg.png"
                        width="200" style="border:1px solid #ddd; padding:5px;">
                    <p>NMID: ID10200300400</p>
                </div>

                <label>Upload Bukti Transfer</label>
                <input type="file" name="proof" accept="image/*" required>
                <small style="color:#777;">Format: JPG/PNG, Maks 2MB</small>
                <br><br>

                <button type="submit" name="upload" style="background:var(--primary);">Kirim Bukti Pembayaran</button>
                <a href="member.php" style="display:block; text-align:center; margin-top:15px; color:#555;">Batal</a>
            </form>
        </div>
    </div>

    <script>
    function showPaymentDetails() {
        // 1. Ambil nilai dropdown yang dipilih
        var selectedBank = document.getElementById("paymentMethod").value;

        // 2. Sembunyikan SEMUA kotak info terlebih dahulu
        var allBoxes = document.getElementsByClassName("payment-box");
        for (var i = 0; i < allBoxes.length; i++) {
            allBoxes[i].style.display = "none";
        }

        // 3. Tampilkan HANYA kotak yang sesuai dengan pilihan
        if (selectedBank !== "") {
            var targetId = "info_" + selectedBank;
            var targetElement = document.getElementById(targetId);
            if (targetElement) {
                targetElement.style.display = "block";
            }
        }
    }

    function copyToClipboard(elementId) {
        // Logika untuk menyalin teks nomor rekening
        var copyText = document.getElementById(elementId).innerText;
        navigator.clipboard.writeText(copyText).then(function() {
            alert("Nomor berhasil disalin: " + copyText);
        }, function(err) {
            console.error('Gagal menyalin: ', err);
        });
    }
    </script>
</body>

</html>