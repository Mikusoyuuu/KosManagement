<?php
include '../includes/auth.php';
redirectIfNotUser();

include '../includes/db.php';

$user_id = $_SESSION['user_id'];

// Mark notification as read
if (isset($_GET['baca'])) {
    $id = mysqli_real_escape_string($conn, $_GET['baca']);
    $query = "UPDATE notifikasi SET status = 'dibaca' WHERE id = '$id'";
    mysqli_query($conn, $query);
}

// Get notifications
$notifikasi_query = "
    SELECT * FROM notifikasi 
    WHERE id_user = '$user_id' 
    ORDER BY created_at DESC
";
$notifikasi_result = mysqli_query($conn, $notifikasi_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi - User</title>
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
                        <a href="review.php" class="block py-2 px-4 hover:bg-gray-100 rounded">
                            Review Kamar
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Notifikasi</h1>

            <!-- Notifications List -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="space-y-4">
                    <?php
                    if (mysqli_num_rows($notifikasi_result) > 0) {
                        while ($notif = mysqli_fetch_assoc($notifikasi_result)) {
                            $status_class = $notif['status'] == 'belum_dibaca' ? 'bg-blue-50 border-l-4 border-blue-500' : 'bg-gray-50';
                            $status_icon = $notif['status'] == 'belum_dibaca' ? '🔵' : '⚪';
                            
                            echo "
                            <div class='p-4 rounded $status_class'>
                                <div class='flex justify-between items-start'>
                                    <div class='flex-1'>
                                        <p class='text-gray-800'>{$notif['pesan']}</p>
                                        <p class='text-sm text-gray-500 mt-1'>
                                            " . date('d M Y H:i', strtotime($notif['created_at'])) . "
                                        </p>
                                    </div>
                                    <div class='ml-4'>";
                            
                            if ($notif['status'] == 'belum_dibaca') {
                                echo "
                                <a href='?baca={$notif['id']}' 
                                   class='text-blue-600 hover:text-blue-800 text-sm'>
                                    Tandai sudah dibaca
                                </a>";
                            }
                            
                            echo "</div>
                                </div>
                            </div>";
                        }
                    } else {
                        echo "<p class='text-gray-500 text-center py-8'>Tidak ada notifikasi</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>