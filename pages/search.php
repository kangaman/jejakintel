<?php
// Bagian PHP di atas untuk menampilkan history
$query_result = null;
$error_message = ''; // Modifikasi untuk error history
$search_query = '';

// Logika untuk menampilkan hasil dari history
if (isset($_GET['history_id']) && is_numeric($_GET['history_id'])) {
    $history_id = $_GET['history_id'];
    
    if (user_role() === 'admin') {
        $stmt = $pdo->prepare("SELECT query_text, result_json FROM search_history WHERE id = ?");
        $stmt->execute([$history_id]);
    } else {
        $user_id = $_SESSION['user_id'];
        $stmt = $pdo->prepare("SELECT query_text, result_json FROM search_history WHERE id = ? AND user_id = ?");
        $stmt->execute([$history_id, $user_id]);
    }
    
    $history_data = $stmt->fetch();

    if ($history_data) {
        $search_query = $history_data['query_text'];
        $query_result = json_decode($history_data['result_json'], true);
    } else {
        // Ini adalah error jika history_id tidak ditemukan, BUKAN flash message
        $error_message = "History tidak ditemukan atau Anda tidak memiliki hak akses.";
    }
}
?>

<div class="container mx-auto">
    <h1 class="text-3xl font-bold mb-6">Pencarian OSINT</h1>

    <?php if (!empty($error_message)): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p class="font-bold">Gagal!</p>
            <p><?= e($error_message) ?></p>
        </div>
    <?php endif; ?>


    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <form action="pages/submit_search.php" method="POST">
             <label for="query-input" class="block text-gray-700 text-sm font-bold mb-2">Masukkan Query Baru:</label>
            <div class="flex">
                <input type="text" name="query" id="query-input" class="shadow appearance-none border rounded-l w-full py-2 px-3 text-gray-700" placeholder="contoh: chiepoel@gmail.com" required>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-r w-40 transition-colors duration-300">
                    <i class="fas fa-search"></i> Kirim ke Antrean
                </button>
            </div>
        </form>
    </div>

    <?php if ($query_result !== null): ?>
        <div id="hasilPencarianContainer" class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-4">Hasil History untuk "<?= e($search_query) ?>"</h2>
            
            <?php if (isset($query_result['List']) && !empty($query_result['List'])): 
                $result_count = count($query_result['List']);
            ?>
                <div class="mb-6 p-4 bg-gray-50 border rounded-lg">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div class="mb-4 sm:mb-0">
                            <p class="font-bold text-gray-700">Menampilkan <?= $result_count ?> hasil.</p>
                            <div class="flex flex-wrap gap-2 mt-2">
                                <button id="expandAll" class="bg-green-500 text-white px-3 py-1 text-sm rounded hover:bg-green-600"><i class="fas fa-plus-square mr-1"></i> Buka Semua</button>
                                <button id="collapseAll" class="bg-red-500 text-white px-3 py-1 text-sm rounded hover:bg-red-600"><i class="fas fa-minus-square mr-1"></i> Tutup Semua</button>
                                
                                <?php if (user_role() === 'admin' || user_role() === 'premium'): ?>
                                <button id="exportPdfBtn" class="bg-gray-700 text-white px-3 py-1 text-sm rounded hover:bg-gray-800"><i class="fas fa-file-pdf mr-1"></i> Export PDF</button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="w-full sm:w-1/2">
                            <label for="filterHasil" class="block text-gray-700 text-sm font-bold mb-2"><i class="fas fa-filter mr-1"></i>Filter Hasil:</label>
                            <input type="text" id="filterHasil" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" placeholder="Ketik untuk menyaring hasil...">
                        </div>
                    </div>
                </div>
                
                <div id="accordion-container" class="space-y-2">
                    <?php foreach ($query_result['List'] as $db_name => $leak_info): ?>
                        <div class="accordion-item border rounded-lg overflow-hidden bg-gray-50">
                            <button class="accordion-header w-full text-left p-4 font-bold text-lg hover:bg-gray-200 focus:outline-none flex justify-between items-center transition-colors duration-300">
                                <span><?= e($db_name) ?></span>
                                <i class="fas fa-chevron-down transform transition-transform duration-300"></i>
                            </button>
                            <div class="accordion-content hidden p-4 border-t border-gray-200 bg-white">
                                <p class='text-sm text-gray-500 mb-4 italic'><?= e($leak_info['InfoLeak'] ?? 'Tidak ada informasi tambahan.') ?></p>
                                
                                <?php if (isset($leak_info['Data']) && is_array($leak_info['Data'])): ?>
                                    <?php foreach($leak_info['Data'] as $data_item): ?>
                                        <div class="border-t border-dashed pt-4 mt-4 first:mt-0 first:pt-0 first:border-0">
                                            <div class="space-y-2">
                                            <?php foreach($data_item as $key => $value): ?>
                                                <div class="grid grid-cols-3 gap-4 text-sm">
                                                    <div class="col-span-1 text-gray-600 font-semibold"><?= e($key) ?></div>
                                                    <div class="col-span-2 text-gray-800 break-words font-mono bg-gray-100 px-2 py-1 rounded"><?= e($value) ?></div>
                                                </div>
                                            <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                    <p class="font-bold">Tidak Ada Data</p>
                    <p>Pencarian ini telah selesai diproses namun tidak menghasilkan data apa pun.</p>
                </div>
            <?php endif; ?>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const accordionContainer = document.getElementById('accordion-container');
            if (!accordionContainer) return;

            const accordionItems = accordionContainer.querySelectorAll('.accordion-item');
            
            function toggleAllAccordions(expand = true) {
                accordionItems.forEach(item => {
                    if (item.style.display !== 'none') {
                        const content = item.querySelector('.accordion-content');
                        const icon = item.querySelector('i.fa-chevron-down');
                        if (expand) {
                            content.classList.remove('hidden');
                            icon.classList.add('rotate-180');
                        } else {
                            content.classList.add('hidden');
                            icon.classList.remove('rotate-180');
                        }
                    }
                });
            }

            const expandAllBtn = document.getElementById('expandAll');
            const collapseAllBtn = document.getElementById('collapseAll');
            if(expandAllBtn) expandAllBtn.addEventListener('click', () => toggleAllAccordions(true));
            if(collapseAllBtn) collapseAllBtn.addEventListener('click', () => toggleAllAccordions(false));

            accordionItems.forEach((item, index) => {
                const header = item.querySelector('.accordion-header');
                header.addEventListener('click', () => {
                    const content = header.nextElementSibling;
                    const icon = header.querySelector('i.fa-chevron-down');
                    content.classList.toggle('hidden');
                    icon.classList.toggle('rotate-180');
                });
                if (index === 0) {
                    header.click();
                }
            });

            const filterInput = document.getElementById('filterHasil');
            if (filterInput) {
                filterInput.addEventListener('keyup', function() {
                    const filterText = this.value.toLowerCase();
                    accordionItems.forEach(item => {
                        const itemText = item.textContent.toLowerCase();
                        if (itemText.includes(filterText)) {
                            item.style.display = '';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            }

            const exportPdfBtn = document.getElementById('exportPdfBtn');
            if (exportPdfBtn) {
                exportPdfBtn.addEventListener('click', function () {
                    const elementToPrint = document.getElementById('hasilPencarianContainer');
                    const searchQueryValue = "<?= e($search_query) ?>" || 'tanpa_judul';
                    const filename = `Hasil_JejakIntel_${searchQueryValue}.pdf`;
                    const opt = {
                        margin: 0.5,
                        filename: filename,
                        image: { type: 'jpeg', quality: 0.98 },
                        html2canvas: { scale: 2, useCORS: true },
                        jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
                    };
                    toggleAllAccordions(true);
                    html2pdf().set(opt).from(elementToPrint).save();
                });
            }
        });
        </script>
    <?php endif; ?>
</div>