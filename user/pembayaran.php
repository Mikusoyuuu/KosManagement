<?php
include '../includes/auth.php';
redirectIfNotUser();

include '../includes/db.php';

$user_id = $_SESSION['user_id'];

// Get approved bookings for payment
$pemesanan_query = "
    SELECT p.*, k.nama_kamar, k.harga 
    FROM pemesanan p 
    JOIN kamar k ON p.id_kamar = k.id 
    WHERE p.id_user = '$user_id' AND p.status = 'diterima'
    ORDER BY p.created_at DESC
";
$pemesanan_result = mysqli_query($conn, $pemesanan_query);

// Process payment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bayar'])) {
    $id_pemesanan = mysqli_real_escape_string($conn, $_POST['id_pemesanan']);
    $jumlah = mysqli_real_escape_string($conn, $_POST['jumlah']);
    $metode = mysqli_real_escape_string($conn, $_POST['metode']);
    
    // Handle file upload
    $bukti_bayar = '';
    if (isset($_FILES['bukti_bayar']) && $_FILES['bukti_bayar']['error'] == 0) {
        $target_dir = "../assets/img/";
        $bukti_bayar = uniqid() . '_' . basename($_FILES['bukti_bayar']['name']);
        $target_file = $target_dir . $bukti_bayar;
        move_uploaded_file($_FILES['bukti_bayar']['tmp_name'], $target_file);
    }
    
    $query = "INSERT INTO pembayaran (id_pemesanan, jumlah, metode, bukti_bayar) 
              VALUES ('$id_pemesanan', '$jumlah', '$metode', '$bukti_bayar')";
    
    if (mysqli_query($conn, $query)) {
        $success = "Bukti pembayaran berhasil diupload! Menunggu verifikasi admin.";
        
        // Create notification for admin
        $user_nama = $_SESSION['user_nama'];
        $pesan = "Pembayaran baru dari $user_nama sebesar Rp " . number_format($jumlah, 0, ',', '.');
        mysqli_query($conn, "INSERT INTO notifikasi (pesan) VALUES ('$pesan')");
    } else {
        $error = "Gagal mengupload bukti pembayaran: " . mysqli_error($conn);
    }
}

// Get payment history
$pembayaran_query = "
    SELECT pb.*, p.id_kamar, k.nama_kamar 
    FROM pembayaran pb 
    JOIN pemesanan p ON pb.id_pemesanan = p.id 
    JOIN kamar k ON p.id_kamar = k.id 
    WHERE p.id_user = '$user_id' 
    ORDER BY pb.tanggal_bayar DESC
";
$pembayaran_result = mysqli_query($conn, $pembayaran_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - User</title>
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
                        <a href="pembayaran.php" class="block py-2 px-4 bg-green-100 text-green-600 rounded font-semibold">
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
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Pembayaran</h1>

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

            <!-- Approved Bookings for Payment -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-xl font-semibold mb-4">Pemesanan yang Perlu Dibayar</h2>
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-2 text-left">Kamar</th>
                                <th class="px-4 py-2 text-left">Harga</th>
                                <th class="px-4 py-2 text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($pemesanan_result) > 0) {
                                while ($pemesanan = mysqli_fetch_assoc($pemesanan_result)) {
                                    $harga_format = 'Rp ' . number_format($pemesanan['harga'], 0, ',', '.');
                                    
                                    echo "
                                    <tr class='border-b'>
                                        <td class='px-4 py-2'>{$pemesanan['nama_kamar']}</td>
                                        <td class='px-4 py-2'>$harga_format</td>
                                        <td class='px-4 py-2'>
                                            <button onclick='openPaymentModal({$pemesanan['id']}, {$pemesanan['harga']}, \"{$pemesanan['nama_kamar']}\")' 
                                                    class='bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700'>
                                                Bayar Sekarang
                                            </button>
                                        </td>
                                    </tr>";
                                }
                            } else {
                                echo "
                                <tr>
                                    <td colspan='3' class='px-4 py-4 text-center text-gray-500'>
                                        Tidak ada pemesanan yang perlu dibayar
                                    </td>
                                </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Payment History -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Riwayat Pembayaran</h2>
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-2 text-left">Kamar</th>
                                <th class="px-4 py-2 text-left">Jumlah</th>
                                <th class="px-4 py-2 text-left">Metode</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-left">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($pembayaran_result) > 0) {
                                while ($pembayaran = mysqli_fetch_assoc($pembayaran_result)) {
                                    $jumlah_format = 'Rp ' . number_format($pembayaran['jumlah'], 0, ',', '.');
                                    $tanggal = date('d M Y H:i', strtotime($pembayaran['tanggal_bayar']));
                                    $status_class = '';
                                    
                                    switch ($pembayaran['status']) {
                                        case 'menunggu':
                                            $status_class = 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'terverifikasi':
                                            $status_class = 'bg-green-100 text-green-800';
                                            break;
                                        case 'gagal':
                                            $status_class = 'bg-red-100 text-red-800';
                                            break;
                                    }
                                    
                                    echo "
                                    <tr class='border-b'>
                                        <td class='px-4 py-2'>{$pembayaran['nama_kamar']}</td>
                                        <td class='px-4 py-2'>$jumlah_format</td>
                                        <td class='px-4 py-2'>{$pembayaran['metode']}</td>
                                        <td class='px-4 py-2'>
                                            <span class='px-2 py-1 rounded text-sm $status_class'>{$pembayaran['status']}</span>
                                        </td>
                                        <td class='px-4 py-2'>$tanggal</td>
                                    </tr>";
                                }
                            } else {
                                echo "
                                <tr>
                                    <td colspan='5' class='px-4 py-4 text-center text-gray-500'>
                                        Belum ada riwayat pembayaran
                                    </td>
                                </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-xl font-semibold mb-4" id="paymentModalTitle">Konfirmasi Pembayaran</h3>
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="id_pemesanan" id="modalPemesananId">
                <input type="hidden" name="jumlah" id="modalJumlah">
                
                <div class="mb-4">
                    <p class="text-gray-600">Kamar: <span id="modalKamarNama" class="font-semibold"></span></p>
                    <p class="text-gray-600">Jumlah: <span id="modalJumlahDisplay" class="font-semibold"></span></p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Metode Pembayaran</label>
                    <select name="metode" required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="transfer">Transfer Bank</option>
                        <option value="cash">Cash</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bukti Pembayaran</label>
                    <input type="file" name="bukti_bayar" accept="image/*" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    <p class="text-sm text-gray-500 mt-1">Upload bukti transfer atau foto struk</p>
                </div>
                
                <div class="flex space-x-3">
                    <button type="button" onclick="closePaymentModal()" 
                        class="flex-1 bg-gray-300 text-gray-700 py-2 rounded hover:bg-gray-400">
                        Batal
                    </button>
                    <button type="submit" name="bayar" 
                        class="flex-1 bg-green-600 text-white py-2 rounded hover:bg-green-700">
                        Konfirmasi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openPaymentModal(pemesananId, jumlah, kamarNama) {
            document.getElementById('modalPemesananId').value = pemesananId;
            document.getElementById('modalJumlah').value = jumlah;
            document.getElementById('modalKamarNama').textContent = kamarNama;
            document.getElementById('modalJumlahDisplay').textContent = 'Rp ' + jumlah.toLocaleString('id-ID');
            document.getElementById('paymentModal').classList.remove('hidden');
            document.getElementById('paymentModal').classList.add('flex');
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').classList.add('hidden');
            document.getElementById('paymentModal').classList.remove('flex');
        }
    </script>
</body>
</html>