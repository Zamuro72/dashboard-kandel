<?php
$page_title = "Kelola Jadwal Kegiatan";
include 'includes/header.php';
include 'includes/sidebar.php';

// Handle bulk delete
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bulk_delete'])) {
    if (!empty($_POST['selected_ids'])) {
        $ids = array_map('intval', $_POST['selected_ids']);
        $ids_string = implode(',', $ids);
        
        $sql = "DELETE FROM jadwal_kegiatan WHERE id IN ($ids_string)";
        if ($conn->query($sql)) {
            $success = count($ids) . " kegiatan berhasil dihapus!";
        } else {
            $error = "Gagal menghapus kegiatan: " . $conn->error;
        }
    }
}

// Get search parameter
$search = isset($_GET['search']) ? $conn->real_escape_string(trim($_GET['search'])) : '';
$filter_jenis = isset($_GET['filter_jenis']) ? $conn->real_escape_string(trim($_GET['filter_jenis'])) : '';

// Build query with search
$sql = "SELECT * FROM jadwal_kegiatan WHERE 1=1";

if ($search) {
    $sql .= " AND (nama_program LIKE '%$search%' OR jenis_program LIKE '%$search%')";
}

if ($filter_jenis) {
    $sql .= " AND jenis_program = '$filter_jenis'";
}

$sql .= " ORDER BY tanggal_mulai DESC";
$result = $conn->query($sql);

// Get all jenis program for filter
$jenis_list = $conn->query("SELECT DISTINCT jenis_program FROM jadwal_kegiatan ORDER BY jenis_program ASC");
?>

<div class="content-box">
    <div class="content-box-header">
        <h2><i class="fas fa-calendar-alt"></i> Jadwal Kegiatan</h2>
        <a href="jadwal-kegiatan-add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Kegiatan
        </a>
    </div>
    
    <?php if (isset($success)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <!-- Search & Filter Section -->
    <div class="search-filter-box" style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; margin-bottom: 1.5rem;">
        <form method="GET" action="" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 250px;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">
                    <i class="fas fa-search"></i> Cari Kegiatan
                </label>
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Cari berdasarkan judul atau jenis program..."
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            
            <div style="flex: 0 0 250px;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333;">
                    <i class="fas fa-filter"></i> Filter Jenis Program
                </label>
                <select name="filter_jenis" class="form-control">
                    <option value="">Semua Jenis</option>
                    <?php while ($jenis = $jenis_list->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($jenis['jenis_program']); ?>" 
                                <?php echo $filter_jenis == $jenis['jenis_program'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($jenis['jenis_program']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div style="flex: 0 0 auto;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Cari
                </button>
                <a href="jadwal-kegiatan.php" class="btn btn-secondary">
                    <i class="fas fa-sync"></i> Reset
                </a>
            </div>
        </form>
    </div>
    
    <?php if ($result->num_rows > 0): ?>
        <form method="POST" id="bulkDeleteForm">
            <div style="margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <button type="button" class="btn btn-danger" onclick="confirmBulkDelete()" style="opacity: 0.5;" id="bulkDeleteBtn" disabled>
                        <i class="fas fa-trash"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
                    </button>
                </div>
                <div style="color: #666; font-size: 0.9rem;">
                    Total: <strong><?php echo $result->num_rows; ?></strong> kegiatan
                    <?php if ($search || $filter_jenis): ?>
                        | <a href="jadwal-kegiatan.php" style="color: var(--primary-blue);">Lihat Semua</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 3%;">
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                            </th>
                            <th style="width: 5%;">No</th>
                            <th style="width: 37%;">Judul Program/Kegiatan</th>
                            <th style="width: 25%;">Jenis Program</th>
                            <th style="width: 20%;">Jadwal</th>
                            <th style="width: 10%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while ($row = $result->fetch_assoc()): 
                            $start = new DateTime($row['tanggal_mulai']);
                            $end = new DateTime($row['tanggal_selesai']);
                            $date_range = $start->format('d M') . ' - ' . $end->format('d M Y');
                            
                            // Generate badge class
                            $jenis_clean = strtolower(preg_replace('/[^a-z0-9]/', '', $row['jenis_program']));
                            $badge_class = 'badge-' . $jenis_clean;
                        ?>
                        <tr>
                            <td style="text-align: center;">
                                <input type="checkbox" 
                                       name="selected_ids[]" 
                                       value="<?php echo $row['id']; ?>" 
                                       class="row-checkbox"
                                       onchange="updateBulkDeleteButton()">
                            </td>
                            <td style="text-align: center;"><?php echo $no++; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($row['nama_program']); ?></strong>
                            </td>
                            <td>
                                <span class="<?php echo $badge_class; ?>">
                                    <?php echo htmlspecialchars($row['jenis_program']); ?>
                                </span>
                            </td>
                            <td><?php echo $date_range; ?></td>
                            <td>
                                <div class="table-actions">
                                    <a href="jadwal-kegiatan-edit.php?id=<?php echo $row['id']; ?>" 
                                       class="btn btn-warning btn-sm" 
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="jadwal-kegiatan-delete.php?id=<?php echo $row['id']; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirmDelete('Yakin ingin menghapus kegiatan ini?')"
                                       title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </form>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <?php if ($search || $filter_jenis): ?>
                <p>Tidak ada kegiatan yang sesuai dengan pencarian.</p>
                <a href="jadwal-kegiatan.php" class="btn btn-primary" style="margin-top: 1rem;">
                    <i class="fas fa-arrow-left"></i> Kembali ke Semua Kegiatan
                </a>
            <?php else: ?>
                <p>Belum ada jadwal kegiatan. Klik tombol "Tambah Kegiatan" untuk menambahkan.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
// Toggle select all checkboxes
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
    updateBulkDeleteButton();
}

// Update bulk delete button state
function updateBulkDeleteButton() {
    const checkboxes = document.querySelectorAll('.row-checkbox:checked');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const selectedCount = document.getElementById('selectedCount');
    const selectAll = document.getElementById('selectAll');
    
    selectedCount.textContent = checkboxes.length;
    
    if (checkboxes.length > 0) {
        bulkDeleteBtn.disabled = false;
        bulkDeleteBtn.style.opacity = '1';
    } else {
        bulkDeleteBtn.disabled = true;
        bulkDeleteBtn.style.opacity = '0.5';
    }
    
    // Update select all checkbox
    const allCheckboxes = document.querySelectorAll('.row-checkbox');
    selectAll.checked = allCheckboxes.length > 0 && checkboxes.length === allCheckboxes.length;
}

// Confirm bulk delete
function confirmBulkDelete() {
    const checkboxes = document.querySelectorAll('.row-checkbox:checked');
    const count = checkboxes.length;
    
    if (count === 0) {
        alert('Pilih minimal 1 kegiatan untuk dihapus!');
        return false;
    }
    
    if (confirm(`Yakin ingin menghapus ${count} kegiatan yang dipilih? Tindakan ini tidak dapat dibatalkan!`)) {
        const form = document.getElementById('bulkDeleteForm');
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'bulk_delete';
        input.value = '1';
        form.appendChild(input);
        form.submit();
    }
}
</script>

<?php include 'includes/footer.php'; ?>