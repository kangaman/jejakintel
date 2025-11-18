<?php
// Ambil pengaturan harga dari database
$pricing_settings_query = $pdo->query("SELECT setting_name, setting_value FROM settings WHERE setting_name LIKE 'price_%' OR setting_name LIKE 'features_%'");
$pricing_settings = $pricing_settings_query->fetchAll(PDO::FETCH_KEY_PAIR);

$price_free = $pricing_settings['price_free'] ?? 'Gratis';
$price_premium = $pricing_settings['price_premium'] ?? 'Hubungi Admin';
$features_free = json_decode($pricing_settings['features_free'] ?? '[]', true);
$features_premium = json_decode($pricing_settings['features_premium'] ?? '[]', true);
?>
<div class="py-16 sm:py-24 px-6 bg-slate-900 text-gray-300">
    <div class="container mx-auto">
        <div class="text-center mb-16" data-aos="fade-down">
            <h1 class="text-4xl md:text-5xl font-extrabold text-white">Paket yang Tepat untuk Kebutuhan Anda</h1>
            <p class="text-lg text-gray-400 mt-4 max-w-2xl mx-auto">Mulai gratis selamanya, atau tingkatkan ke Premium untuk membuka kekuatan penuh JejakIntel.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 max-w-4xl mx-auto" data-aos="fade-up">
            <div class="bg-slate-800 p-8 rounded-xl border border-slate-700 text-left flex flex-col">
                <h3 class="text-2xl font-bold text-white">Free</h3>
                <p class="text-gray-400 mt-2 mb-6">Cocok untuk penggunaan pribadi dan mencoba platform kami.</p>
                <div class="my-4">
                    <span class="text-5xl font-bold text-white"><?= e($price_free) ?></span>
                    <span class="text-gray-400">/ Selamanya</span>
                </div>
                <ul class="space-y-4 text-gray-300 flex-grow">
                    <?php foreach ($features_free as $feature): ?>
                        <?php if (strpos($feature, '!') === 0): ?>
                            <li class="flex items-start"><i class="fas fa-times-circle text-red-500 mr-3 mt-1"></i> <span><?= e(substr($feature, 1)) ?></span></li>
                        <?php else: ?>
                            <li class="flex items-start"><i class="fas fa-check-circle text-green-500 mr-3 mt-1"></i> <span><?= e($feature) ?></span></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
                <a href="index.php?page=register" class="mt-8 block w-full text-center bg-slate-700 text-white font-semibold py-3 rounded-lg hover:bg-slate-600 transition">Mulai Gratis</a>
            </div>

            <div class="bg-blue-600 p-8 rounded-xl text-left text-white flex flex-col ring-4 ring-blue-500/50">
                 <div class="flex justify-between items-center">
                    <h3 class="text-2xl font-bold">Premium</h3>
                    <span class="bg-white/20 text-xs font-bold px-3 py-1 rounded-full">REKOMENDASI</span>
                </div>
                <p class="mt-2 mb-6 opacity-80">Untuk profesional, investigator, dan pengguna tingkat lanjut.</p>
                 <div class="my-4">
                    <span class="text-5xl font-bold"><?= e($price_premium) ?></span>
                </div>
                 <ul class="space-y-4 flex-grow">
                    <?php foreach ($features_premium as $feature): ?>
                        <li class="flex items-start"><i class="fas fa-check-circle mr-3 mt-1"></i> <span><?= e($feature) ?></span></li>
                    <?php endforeach; ?>
                 </ul>
                <p class="text-center mt-8 opacity-80 text-sm">Aktivasi dilakukan secara manual oleh admin.</p>
                <a href="index.php?page=login" class="mt-2 block w-full text-center bg-white text-blue-600 font-semibold py-3 rounded-lg hover:bg-gray-200 transition">Login untuk Upgrade</a>
            </div>
        </div>
    </div>
</section>