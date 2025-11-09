<?php
include '../includes/auth.php';
redirectIfNotUser();

include '../includes/db.php';

$user_id = $_SESSION['user_id'];

// Get rooms that user has stayed in
$kamar_query = "
    SELECT DISTINCT k.* 
    FROM kamar k 
    JOIN pemesanan p ON k.id = p.id_kamar 
    WHERE p.id_user = '$user_id' AND p.status = 'diterima'
";
$kamar_result = mysqli_query($conn, $kamar_query);

// Submit review
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    $id_kamar = mysqli_real_escape_string($conn, $_POST['id_kamar']);
    $rating = mysqli_real_escape_string($conn, $_POST['rating']);
    $komentar = mysqli_real_escape_string($conn, $_POST['komentar']);
    
    // Check if user already reviewed this room
    $check_query = "SELECT * FROM review WHERE id_user = '$user_id' AND id_kamar = '$id_kamar'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $error = "Anda sudah memberikan review untuk kamar ini!";
    } else {
        $query = "INSERT INTO review (id_user, id_kamar, rating, komentar) 
                  VALUES ('$user_id', '$id_kamar', '$rating', '$komentar')";
        
        if (mysqli_query($conn, $query)) {
            $success = "Review berhasil dikirim!";
        } else {
            $error = "Gagal mengirim review: " . mysqli_error($conn);
        }
    }
}

// Get user's reviews
$review_query = "
    SELECT r.*, k.nama_kamar 
    FROM review r 
    JOIN kamar k ON r.id_kamar = k.id 
    WHERE r.id_user = '$user_id' 
    ORDER BY r.created_at DESC
";
$review_result = mysqli_query($conn, $review_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Kamar - User</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-green-600 text-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="text-xl font-bold">User Dashboard</div>
                <div class="space-x-4">
                    <span>Halo, <?php echo $_SESSION['user_nama']; ?></span>
                    <a href="notifikasi.php" class="hover:text-green-200">Notifikasi</a>
                    <a href="../logout.php" class="hover:text-green-200">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg min-h-screen">
            <div class="p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="dashboard.php" class="block py-2 px-4 hover:bg-gray-100 rounded">
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="pemesanan.php" class="block py-2 px-4 hover:bg-gray-100 rounded">
                            Pemesanan Saya
                        </a>
                    </li>
                    <li>
                        <a href="pembayaran.php" class="block py-2 px-4 hover:bg-gray-100 rounded">
                            Pembayaran
                        </a>
                    </li>
                    <li>
                        <a href="review.php" class="block py-2 px-4 bg-green-100 text-green-600 rounded font-semibold">
                            Review Kamar
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Review Kamar</h1>

            <?php if (isset($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Submit Review -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-xl font-semibold mb-4">Beri Review Kamar</h2>
                <form method="POST" action="">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Kamar</label>
                            <select name="id_kamar" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                                <option value="">-- Pilih Kamar --</option>
                                <?php
                                while ($kamar = mysqli_fetch_assoc($kamar_result)) {
                                    echo "<option value='{$kamar['id']}'>{$kamar['nama_kamar']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                            <select name="rating" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                                <option value="">-- Pilih Rating --</option>
                                <option value="5">⭐⭐⭐⭐⭐ (5)</option>
                                <option value="4">⭐⭐⭐⭐ (4)</option>
                                <option value="3">⭐⭐⭐ (3)</option>
                                <option value="2">⭐⭐ (2)</option>
                                <option value="1">⭐ (1)</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Komentar</label>
                            <textarea name="komentar" rows="4" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                placeholder="Bagikan pengalaman Anda menginap di kamar ini..."></textarea>
                        </div>
                    </div>
                    <button type="submit" name="submit_review" 
                        class="mt-4 bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                        Kirim Review
                    </button>
                </form>
            </div>

            <!-- My Reviews -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Review Saya</h2>
                <div class="space-y-4">
                    <?php
                    if (mysqli_num_rows($review_result) > 0) {
                        while ($review = mysqli_fetch_assoc($review_result)) {
                            $stars = str_repeat('⭐', $review['rating']);
                            $tanggal = date('d M Y', strtotime($review['created_at']));
                            
                            echo "
                            <div class='border rounded-lg p-4'>
                                <div class='flex justify-between items-start mb-2'>
                                    <h3 class='font-semibold text-lg'>{$review['nama_kamar']}</h3>
                                    <span class='text-yellow-500'>$stars</span>
                                </div>
                                <p class='text-gray-600 mb-2'>{$review['komentar']}</p>
                                <p class='text-sm text-gray-500'>Ditulis pada: $tanggal</p>
                            </div>";
                        }
                    } else {
                        echo "<p class='text-gray-500 text-center py-8'>Belum ada review</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>