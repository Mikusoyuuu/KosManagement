<?php
include '../includes/auth.php';
redirectIfNotUser();

include '../includes/db.php';

$user_id = $_SESSION['user_id'];

// Get user data
$user_query = "SELECT * FROM users WHERE id = '$user_id'";
$user = mysqli_query($conn, $user_query)->fetch_assoc();

// Get user's bookings
$pemesanan_query = "
    SELECT p.*, k.nama_kamar, k.harga 
    FROM pemesanan p 
    JOIN kamar k ON p.id_kamar = k.id 
    WHERE p.id_user = '$user_id' 
    ORDER BY p.created_at DESC 
    LIMIT 5
";
$pemesanan_result = mysqli_query($conn, $pemesanan_query);

// Get unread notifications count
$notif_count_query = "SELECT COUNT(*) as total FROM notifikasi WHERE id_user = '$user_id' AND status = 'belum_dibaca'";
$notif_count = mysqli_query($conn, $notif_count_query)->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Kos Management</title>
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
                    <a href="notifikasi.php" class="hover:text-green-200 relative">
                        Notifikasi
                        <?php if ($notif_count > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 text-xs flex items-center justify-center">
                                <?php echo $notif_count; ?>
                            </span>
                        <?php endif; ?>
                    </a>
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
                        <a href="dashboard.php" class="block py-2 px-4 bg-green-100 text-green-600 rounded font-semibold">
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
                        <a href="review.php" class="block py-2 px-4 hover:bg-gray-100 rounded">
                            Review Kamar
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Dashboard User</h1>

            <!-- User Info -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-xl font-semibold mb-4">Informasi Profil</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="font-semibold text-gray-600">Nama:</label>
                        <p class="text-gray-800"><?php echo $user['nama']; ?></p>
                    </div>
                    <div>
                        <label class="font-semibold text-gray-600">Email:</label>
                        <p class="text-gray-800"><?php echo $user['email']; ?></p>
                    </div>
                    <div>
                        <label class="font-semibold text-gray-600">No. HP:</label>
                        <p class="text-gray-800"><?php echo $user['no_hp'] ?: '-'; ?></p>
                    </div>
                    <div>
                        <label class="font-semibold text-gray-600">Alamat:</label>
                        <p class="text-gray-800"><?php echo $user['alamat'] ?: '-'; ?></p>
                    </div>
                </div>
            </div>

            <!-- Recent Bookings -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Pemesanan Terbaru</h2>
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-2 text-left">Kamar</th>
                                <th class="px-4 py-2 text-left">Tanggal Masuk</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-left">Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($pemesanan_result) > 0) {
                                while ($pemesanan = mysqli_fetch_assoc($pemesanan_result)) {
                                    $tanggal_masuk = date('d M Y', strtotime($pemesanan['tanggal_masuk']));
                                    $harga_format = 'Rp ' . number_format($pemesanan['harga'], 0, ',', '.');
                                    $status_class = '';
                                    
                                    switch ($pemesanan['status']) {
                                        case 'menunggu':
                                            $status_class = 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'diterima':
                                            $status_class = 'bg-green-100 text-green-800';
                                            break;
                                        case 'ditolak':
                                            $status_class = 'bg-red-100 text-red-800';
                                            break;
                                    }
                                    
                                    echo "
                                    <tr class='border-b'>
                                        <td class='px-4 py-2'>{$pemesanan['nama_kamar']}</td>
                                        <td class='px-4 py-2'>$tanggal_masuk</td>
                                        <td class='px-4 py-2'>
                                            <span class='px-2 py-1 rounded text-sm $status_class'>{$pemesanan['status']}</span>
                                        </td>
                                        <td class='px-4 py-2'>$harga_format</td>
                                    </tr>";
                                }
                            } else {
                                echo "
                                <tr>
                                    <td colspan='4' class='px-4 py-4 text-center text-gray-500'>
                                        Belum ada pemesanan
                                    </td>
                                </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 text-center">
                    <a href="pemesanan.php" class="text-green-600 hover:text-green-800 font-semibold">
                        Lihat Semua Pemesanan →
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>