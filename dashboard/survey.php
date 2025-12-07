<?php
require '../config.php';
checkAccess('member');

if(isset($_POST['submit_survey'])) {
    $bid = $_POST['booking_id'];
    $rate = $_POST['rating'];
    $msg = $_POST['feedback'];
    
    // Simpan Survei
    $pdo->prepare("INSERT INTO surveys (booking_id, rating, feedback) VALUES (?,?,?)")->execute([$bid, $rate, $msg]);
    header("Location: member.php?msg=terimakasih");
}

$booking_id = $_GET['id'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survei Kepuasan - Beautybar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 0.5rem;
            margin: 1rem 0;
        }
        .star-rating input {
            display: none;
        }
        .star-rating label {
            font-size: 3rem;
            color: #d1d5db;
            cursor: pointer;
            transition: all 0.2s;
        }
        .star-rating label:hover,
        .star-rating label:hover ~ label,
        .star-rating input:checked ~ label {
            color: #fbbf24;
            transform: scale(1.1);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="member.php" class="inline-flex items-center text-purple-600 hover:text-purple-700 font-medium">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Dashboard
            </a>
        </div>

        <!-- Survey Card -->
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                <!-- Header -->
                <div class="bg-purple-600 px-6 py-8 text-white text-center">
                    <div class="mb-4">
                        <svg class="w-16 h-16 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-bold mb-2">Survei Kepuasan</h1>
                    <p class="text-purple-100">Bagaimana pengalaman treatment Anda?</p>
                </div>

                <!-- Form -->
                <form method="POST" class="p-6 md:p-8">
                    <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">

                    <!-- Rating Section -->
                    <div class="mb-8">
                        <label class="block text-lg font-semibold text-gray-700 mb-4">
                            Berikan Rating Anda
                        </label>
                        
                        <select name="rating" required class="w-full px-4 py-3 text-lg border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">-- Pilih Rating --</option>
                            <option value="5">⭐⭐⭐⭐⭐ - Sangat Puas</option>
                            <option value="4">⭐⭐⭐⭐ - Puas</option>
                            <option value="3">⭐⭐⭐ - Cukup</option>
                            <option value="2">⭐⭐ - Kurang</option>
                            <option value="1">⭐ - Kecewa</option>
                        </select>
                    </div>

                    <!-- Feedback Section -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            Kritik & Saran
                        </label>
                        <textarea name="feedback" rows="5" required 
                                  placeholder="Ceritakan pengalaman Anda dengan treatment kami..."
                                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-none"></textarea>
                        <p class="text-xs text-gray-500 mt-2">
                            Masukan Anda sangat berharga untuk meningkatkan kualitas layanan kami
                        </p>
                    </div>

                    <!-- Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="member.php" 
                           class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition text-center">
                            Batal
                        </a>
                        <button type="submit" name="submit_survey" 
                                class="flex-1 px-6 py-3 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition shadow-lg">
                            ⭐ Kirim Ulasan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Info Box -->
            <div class="mt-6 bg-white rounded-xl shadow-md p-6">
                <h3 class="font-bold text-gray-800 mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Mengapa Kami Meminta Ulasan?
                </h3>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-start">
                        <span class="text-purple-600 mr-2">✓</span>
                        <span>Membantu kami meningkatkan kualitas layanan</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-purple-600 mr-2">✓</span>
                        <span>Memberikan feedback untuk terapis kami</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-purple-600 mr-2">✓</span>
                        <span>Membantu pelanggan lain dalam memilih treatment</span>
                    </li>
                    <li class="flex items-start">
                        <span class="text-purple-600 mr-2">✓</span>
                        <span>Apresiasi kami terhadap kepercayaan Anda</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        // Update rating text description
        const ratingInputs = document.querySelectorAll('input[name="rating"]');
        const ratingText = document.getElementById('ratingText');
        
        const descriptions = {
            '5': '⭐⭐⭐⭐⭐ Sangat Puas - Luar biasa!',
            '4': '⭐⭐⭐⭐ Puas - Sangat baik',
            '3': '⭐⭐⭐ Cukup - Sesuai harapan',
            '2': '⭐⭐ Kurang - Perlu perbaikan',
            '1': '⭐ Kecewa - Tidak memuaskan'
        };
        
        ratingInputs.forEach(input => {
            input.addEventListener('change', function() {
                ratingText.textContent = descriptions[this.value];
                ratingText.classList.add('text-purple-600', 'font-bold');
            });
        });

        // Add hover effect description
        document.querySelectorAll('.star-rating label').forEach(label => {
            label.addEventListener('mouseenter', function() {
                const rating = this.getAttribute('for').replace('star', '');
                ratingText.textContent = descriptions[rating];
                ratingText.classList.add('text-gray-500');
            });
            
            label.addEventListener('mouseleave', function() {
                const checkedInput = document.querySelector('input[name="rating"]:checked');
                if (checkedInput) {
                    ratingText.textContent = descriptions[checkedInput.value];
                    ratingText.classList.add('text-purple-600', 'font-bold');
                } else {
                    ratingText.textContent = 'Pilih rating Anda';
                    ratingText.classList.remove('text-gray-500', 'text-purple-600', 'font-bold');
                }
            });
        });
    </script>
</body>
</html>