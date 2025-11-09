<?php
include '../includes/auth.php';
redirectIfNotUser();

include '../includes/db.php';

$user_id = $_SESSION['user_id'];

// Get available rooms
$kamar_query = "SELECT * FROM kamar WHERE status = 'tersedia' ORDER BY nama_kamar";
$kamar_result = mysqli_query($conn, $kamar_query);

// Book a room
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pesan_kamar'])) {
    $id_kamar = mysqli_real_escape_string($conn, $_POST['id_kamar']);
    $tanggal_masuk = mysqli_real_escape_string($conn, $_POST['tanggal_masuk']);
    $durasi = mysqli_real_escape_string($conn, $_POST['durasi']);
    
    // Hitung tanggal keluar otomatis berdasarkan durasi
    $tanggal_keluar = date('Y-m-d', strtotime("+$durasi months", strtotime($tanggal_masuk)));
    
    $query = "INSERT INTO pemesanan (id_user, id_kamar, tanggal_masuk, tanggal_keluar, durasi) 
              VALUES ('$user_id', '$id_kamar', '$tanggal_masuk', '$tanggal_keluar', '$durasi')";
    
    if (mysqli_query($conn, $query)) {
        $success = "Pemesanan berhasil diajukan! Menunggu persetujuan admin.";
        
        // Create notification for admin
        $user_nama = $_SESSION['user_nama'];
        $kamar_nama = mysqli_query($conn, "SELECT nama_kamar FROM kamar WHERE id = '$id_kamar'")->fetch_assoc()['nama_kamar'];
        $pesan = "Pemesanan baru dari $user_nama untuk kamar $kamar_nama (Durasi: $durasi bulan)";
        
        mysqli_query($conn, "INSERT INTO notifikasi (pesan) VALUES ('$pesan')");
    } else {
        $error = "Gagal memesan kamar: " . mysqli_error($conn);
    }
}

// Get user's bookings
$pemesanan_query = "
    SELECT p.*, k.nama_kamar, k.harga, k.foto 
    FROM pemesanan p 
    JOIN kamar k ON p.id_kamar = k.id 
    WHERE p.id_user = '$user_id' 
    ORDER BY p.created_at DESC
";
$pemesanan_result = mysqli_query($conn, $pemesanan_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemesanan - User</title>
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
                        <a href="pemesanan.php" class="block py-2 px-4 bg-green-100 text-green-600 rounded font-semibold">
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
            <h1 class="text-3xl font-bold text-gray-800 mb-8">Pemesanan Kamar</h1>

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

            <!-- Available Rooms -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-xl font-semibold mb-4">Kamar Tersedia</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php
                    mysqli_data_seek($kamar_result, 0); // Reset pointer
                    while ($kamar = mysqli_fetch_assoc($kamar_result)) {
                        $harga_format = 'Rp ' . number_format($kamar['harga'], 0, ',', '.');
                        $foto = $kamar['foto'] ? "../assets/img/{$kamar['foto']}" : "https://via.placeholder.com/300x200?text=Kamar";
                        
                        echo "
                        <div class='border rounded-lg overflow-hidden shadow-sm'>
                            <img src='$foto' alt='{$kamar['nama_kamar']}' class='w-full h-48 object-cover'>
                            <div class='p-4'>
                                <h3 class='font-semibold text-lg mb-2'>{$kamar['nama_kamar']}</h3>
                                <p class='text-gray-600 text-sm mb-3'>{$kamar['deskripsi']}</p>
                                <p class='text-green-600 font-bold text-lg mb-3'>$harga_format / bulan</p>
                                <button onclick='openModal({$kamar['id']}, \"{$kamar['nama_kamar']}\", {$kamar['harga']})' 
                                        class='w-full bg-green-600 text-white py-2 rounded hover:bg-green-700'>
                                    Pesan Sekarang
                                </button>
                            </div>
                        </div>";
                    }
                    ?>
                </div>
            </div>

            <!-- Booking History -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Riwayat Pemesanan</h2>
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-2 text-left">Kamar</th>
                                <th class="px-4 py-2 text-left">Tanggal Masuk</th>
                                <th class="px-4 py-2 text-left">Tanggal Keluar</th>
                                <th class="px-4 py-2 text-left">Durasi</th>
                                <th class="px-4 py-2 text-left">Total</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            mysqli_data_seek($pemesanan_result, 0); // Reset pointer
                            if (mysqli_num_rows($pemesanan_result) > 0) {
                                while ($pemesanan = mysqli_fetch_assoc($pemesanan_result)) {
                                    $tanggal_masuk = !empty($pemesanan['tanggal_masuk']) ? 
                                        date('d M Y', strtotime($pemesanan['tanggal_masuk'])) : 'Belum ditentukan';
                                    
                                    $tanggal_keluar = !empty($pemesanan['tanggal_keluar']) ? 
                                        date('d M Y', strtotime($pemesanan['tanggal_keluar'])) : 'Belum ditentukan';
                                    
                                    // Hitung durasi dari tanggal masuk dan keluar
                                    $durasi = '0 bulan';
                                    if (!empty($pemesanan['tanggal_masuk']) && !empty($pemesanan['tanggal_keluar'])) {
                                        $date1 = new DateTime($pemesanan['tanggal_masuk']);
                                        $date2 = new DateTime($pemesanan['tanggal_keluar']);
                                        $interval = $date1->diff($date2);
                                        $durasi_bulan = ($interval->y * 12) + $interval->m;
                                        $durasi_bulan = $durasi_bulan > 0 ? $durasi_bulan : 1;
                                        $durasi = $durasi_bulan . ' bulan';
                                    } elseif (isset($pemesanan['durasi']) && $pemesanan['durasi'] > 0) {
                                        $durasi = $pemesanan['durasi'] . ' bulan';
                                    }
                                    
                                    // Hitung total harga
                                    $harga_kamar = $pemesanan['harga'] ?? 0;
                                    $durasi_num = $pemesanan['durasi'] ?? 0;
                                    $total_harga = $harga_kamar * $durasi_num;
                                    $total_harga_format = 'Rp ' . number_format($total_harga, 0, ',', '.');
                                    
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
                                        <td class='px-4 py-2'>$tanggal_keluar</td>
                                        <td class='px-4 py-2'>$durasi</td>
                                        <td class='px-4 py-2 font-semibold'>$total_harga_format</td>
                                        <td class='px-4 py-2'>
                                            <span class='px-2 py-1 rounded text-sm $status_class'>{$pemesanan['status']}</span>
                                        </td>
                                        <td class='px-4 py-2'>";
                                    
                                    if ($pemesanan['status'] == 'diterima') {
                                        echo "<a href='pembayaran.php?pemesanan={$pemesanan['id']}' 
                                               class='bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700'>
                                                Bayar
                                              </a>";
                                    }
                                    
                                    echo "</td></tr>";
                                }
                            } else {
                                echo "
                                <tr>
                                    <td colspan='7' class='px-4 py-4 text-center text-gray-500'>
                                        Belum ada riwayat pemesanan
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

    <!-- Booking Modal -->
    <div id="bookingModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-xl font-semibold mb-4" id="modalTitle">Pesan Kamar</h3>
            <div class="mb-4">
                <p class="text-sm text-gray-600" id="modalHarga"></p>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="id_kamar" id="modalKamarId">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Masuk</label>
                    <input type="date" name="tanggal_masuk" required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                        min="<?php echo date('Y-m-d'); ?>" 
                        id="tanggalMasukInput"
                        onchange="updateTanggalKeluar()">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Durasi Sewa</label>
                    <select name="durasi" required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                        id="durasiSelect"
                        onchange="updateTanggalKeluar(); updateTotalHarga()">
                        <option value="">Pilih Durasi</option>
                        <option value="1">1 Bulan</option>
                        <option value="3">3 Bulan</option>
                        <option value="6">6 Bulan</option>
                        <option value="12">12 Bulan</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Keluar (Otomatis)</label>
                    <input type="text" id="tanggalKeluarDisplay" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100" 
                        readonly>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Total Biaya</label>
                    <input type="text" id="totalBiayaDisplay" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 font-semibold text-green-600" 
                        readonly>
                </div>
                <div class="flex space-x-3">
                    <button type="button" onclick="closeModal()" 
                        class="flex-1 bg-gray-300 text-gray-700 py-2 rounded hover:bg-gray-400">
                        Batal
                    </button>
                    <button type="submit" name="pesan_kamar" 
                        class="flex-1 bg-green-600 text-white py-2 rounded hover:bg-green-700">
                        Pesan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentHarga = 0;

        function openModal(kamarId, kamarNama, harga) {
            document.getElementById('modalTitle').textContent = 'Pesan Kamar: ' + kamarNama;
            document.getElementById('modalHarga').textContent = 'Harga: Rp ' + harga.toLocaleString('id-ID') + ' / bulan';
            document.getElementById('modalKamarId').value = kamarId;
            currentHarga = harga;
            
            // Reset form
            document.getElementById('tanggalMasukInput').value = '';
            document.getElementById('durasiSelect').value = '';
            document.getElementById('tanggalKeluarDisplay').value = '';
            document.getElementById('totalBiayaDisplay').value = '';
            
            document.getElementById('bookingModal').classList.remove('hidden');
            document.getElementById('bookingModal').classList.add('flex');
        }

        function closeModal() {
            document.getElementById('bookingModal').classList.add('hidden');
            document.getElementById('bookingModal').classList.remove('flex');
        }

        function updateTanggalKeluar() {
            const tanggalMasuk = document.getElementById('tanggalMasukInput').value;
            const durasi = document.getElementById('durasiSelect').value;
            
            if (tanggalMasuk && durasi) {
                const tanggalMasukObj = new Date(tanggalMasuk);
                const tanggalKeluarObj = new Date(tanggalMasukObj);
                tanggalKeluarObj.setMonth(tanggalKeluarObj.getMonth() + parseInt(durasi));
                
                const formattedDate = tanggalKeluarObj.toISOString().split('T')[0];
                document.getElementById('tanggalKeluarDisplay').value = formattedDate;
            } else {
                document.getElementById('tanggalKeluarDisplay').value = '';
            }
            
            updateTotalHarga();
        }

        function updateTotalHarga() {
            const durasi = document.getElementById('durasiSelect').value;
            
            if (durasi && currentHarga > 0) {
                const totalBiaya = currentHarga * parseInt(durasi);
                document.getElementById('totalBiayaDisplay').value = 'Rp ' + totalBiaya.toLocaleString('id-ID');
            } else {
                document.getElementById('totalBiayaDisplay').value = '';
            }
        }

        // Close modal when clicking outside
        document.getElementById('bookingModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>