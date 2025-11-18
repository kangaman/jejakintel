<?php
// Ambil data paket yang aktif dari database untuk ditampilkan
$packages_stmt = $pdo->prepare("SELECT * FROM packages WHERE is_active = 1 ORDER BY sort_order ASC");
$packages_stmt->execute();
$packages = $packages_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="relative min-h-screen flex items-center justify-center text-center px-6 overflow-hidden">
    <div id="particles-js" class="absolute inset-0 z-0"></div>
    
    <div data-aos="fade-up" class="relative z-10">
        <h1 class="text-4xl md:text-6xl font-black text-white leading-tight mb-6">
            Lacak Kebocoran <br/> <span id="typing-effect" class="text-gradient"></span><span class="typing-cursor"></span> Anda.
        </h1>
        <p class="text-lg md:text-xl text-gray-400 max-w-3xl mx-auto mb-10">
            JejakIntel adalah platform OSINT terdepan untuk melacak dan memetakan jejak digital Anda. Ketahui di mana saja data Anda bocor, sebelum disalahgunakan.
        </p>
        <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
            <a href="index.php?page=register" class="bg-blue-600 text-white font-bold py-3 px-8 rounded-full text-lg hover:bg-blue-700 transition-transform transform hover:scale-105 active:scale-95">
                Mulai Gratis
            </a>
            <a href="#harga" class="border-2 border-slate-700 text-gray-300 font-bold py-3 px-8 rounded-full text-lg hover:bg-slate-800 hover:border-slate-600 transition">
                Lihat Paket
            </a>
        </div>
    </div>
</section>

<section id="fitur" class="py-24 bg-slate-800/50 px-6">
    <div class="container mx-auto text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-16">Platform Intelijen Jejak Digital Terlengkap</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="feature-card bg-slate-900 p-8 rounded-xl border border-slate-800" data-aos="fade-up" data-aos-delay="100">
                <div class="bg-blue-600/10 text-blue-400 w-16 h-16 rounded-xl flex items-center justify-center text-3xl mb-6 mx-auto"><i class="fas fa-database"></i></div>
                <h3 class="text-xl font-bold mb-2">Database Komprehensif</h3>
                <p class="text-gray-400">Akses ke miliaran data dari ribuan sumber kebocoran yang terverifikasi.</p>
            </div>
            <div class="feature-card bg-slate-900 p-8 rounded-xl border border-slate-800" data-aos="fade-up" data-aos-delay="200">
                <div class="bg-blue-600/10 text-blue-400 w-16 h-16 rounded-xl flex items-center justify-center text-3xl mb-6 mx-auto"><i class="fas fa-tasks"></i></div>
                <h3 class="text-xl font-bold mb-2">Sistem Antrean Andal</h3>
                <p class="text-gray-400">Pencarian Anda diproses secara stabil di latar belakang, memastikan hasil yang akurat.</p>
            </div>
            <div class="feature-card bg-slate-900 p-8 rounded-xl border border-slate-800" data-aos="fade-up" data-aos-delay="300">
                <div class="bg-blue-600/10 text-blue-400 w-16 h-16 rounded-xl flex items-center justify-center text-3xl mb-6 mx-auto"><i class="fas fa-file-pdf"></i></div>
                <h3 class="text-xl font-bold mb-2">Laporan PDF Profesional</h3>
                <p class="text-gray-400">Unduh hasil temuan Anda dalam format PDF yang rapi untuk analisis lebih lanjut.</p>
            </div>
            <div class="feature-card bg-slate-900 p-8 rounded-xl border border-slate-800" data-aos="fade-up" data-aos-delay="400">
                <div class="bg-blue-600/10 text-blue-400 w-16 h-16 rounded-xl flex items-center justify-center text-3xl mb-6 mx-auto"><i class="fas fa-user-shield"></i></div>
                <h3 class="text-xl font-bold mb-2">Privasi Terjamin</h3>
                <p class="text-gray-400">Pencarian Anda bersifat rahasia. Kami tidak menjual atau membagikan data Anda.</p>
            </div>
        </div>
    </div>
</section>

<section id="harga" class="py-24 bg-slate-900 px-6">
    <div class="container mx-auto text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">Investasi Terbaik untuk Keamanan Digital Anda</h2>
        <p class="text-gray-400 mb-16 max-w-2xl mx-auto">Mulai gratis untuk kebutuhan dasar, atau tingkatkan ke Premium untuk membuka kekuatan penuh JejakIntel.</p>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
            <div class="bg-slate-800 p-8 rounded-xl border border-slate-700 text-left flex flex-col" data-aos="fade-up">
                <h3 class="text-2xl font-bold text-white">Free</h3>
                <p class="text-gray-400 mt-2 mb-6">Cocok untuk penggunaan pribadi dan mencoba platform.</p>
                <div class="my-4"><span class="text-5xl font-bold text-white">Gratis</span><span class="text-gray-400">/ Selamanya</span></div>
                <ul class="space-y-4 text-gray-300 flex-grow">
                    <li class="flex items-start"><i class="fas fa-check-circle text-green-500 mr-3 mt-1"></i> <span><strong>10</strong> Pencarian / Hari</span></li>
                    <li class="flex items-start"><i class="fas fa-check-circle text-green-500 mr-3 mt-1"></i> <span>Akses Database Publik</span></li>
                    <li class="flex items-start"><i class="fas fa-times-circle text-red-500 mr-3 mt-1"></i> <span>Export Hasil ke PDF Profesional</span></li>
                    <li class="flex items-start"><i class="fas fa-times-circle text-red-500 mr-3 mt-1"></i> <span>Gunakan API Key Pribadi</span></li>
                </ul>
                <a href="index.php?page=register" class="mt-8 block w-full text-center bg-slate-700 text-white font-semibold py-3 rounded-lg hover:bg-slate-600 transition">Mulai Gratis</a>
            </div>
            <?php foreach ($packages as $pkg): ?>
            <div class="<?= $pkg['is_recommended'] ? 'bg-blue-600 text-white ring-4 ring-blue-500/50' : 'bg-slate-800 text-gray-300 border border-slate-700' ?> p-8 rounded-xl text-left flex flex-col" data-aos="fade-up" data-aos-delay="<?= $pkg['sort_order'] * 10 ?>">
                <div class="flex justify-between items-center">
                    <h3 class="text-2xl font-bold <?= $pkg['is_recommended'] ? 'text-white' : 'text-white' ?>"><?= e($pkg['name']) ?></h3>
                    <?php if($pkg['is_recommended']): ?><span class="bg-white/20 text-xs font-bold px-3 py-1 rounded-full">REKOMENDASI</span><?php endif; ?>
                </div>
                <p class="mt-2 mb-6 <?= $pkg['is_recommended'] ? 'opacity-80' : 'text-gray-400' ?>"><?= e($pkg['duration_text']) ?></p>
                <div class="my-4"><span class="text-5xl font-bold <?= $pkg['is_recommended'] ? 'text-white' : 'text-white' ?>"><?= e($pkg['price']) ?></span></div>
                <ul class="space-y-4 flex-grow <?= $pkg['is_recommended'] ? 'opacity-90' : '' ?>"><?php $features = json_decode($pkg['features'], true); if(is_array($features)): foreach ($features as $feature): if (strpos($feature, '!') === 0): ?><li class="flex items-start"><i class="fas fa-times-circle text-red-500 mr-3 mt-1"></i> <span><?= e(substr($feature, 1)) ?></span></li><?php else: ?><li class="flex items-start"><i class="fas fa-check-circle <?= $pkg['is_recommended'] ? 'text-white' : 'text-green-500' ?> mr-3 mt-1"></i> <span><?= e($feature) ?></span></li><?php endif; endforeach; endif; ?></ul>
                <a href="index.php?page=login" class="mt-8 block w-full text-center <?= $pkg['is_recommended'] ? 'bg-white text-blue-600' : 'bg-blue-600 text-white' ?> font-semibold py-3 rounded-lg hover:bg-gray-200 transition">Pilih Paket</a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="cara-kerja" class="py-24 bg-slate-800/50 px-6">
    <div class="container mx-auto text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-16">Mulai dalam Hitungan Detik</h2>
        <div class="relative max-w-4xl mx-auto">
            <div class="hidden md:block absolute top-1/2 left-0 w-full h-0.5 bg-slate-700 -translate-y-1/2"></div>
            <div class="hidden md:block absolute top-1/2 left-0 w-full h-0.5 bg-blue-600 -translate-y-1/2 animate-pulse"></div>
            <div class="relative flex flex-col md:flex-row justify-between items-center gap-8">
                <div class="text-center w-64" data-aos="fade-right"><div class="bg-slate-800 border-2 border-slate-700 text-blue-400 w-24 h-24 rounded-full flex items-center justify-center text-4xl font-bold mx-auto mb-4"><i class="fas fa-user-plus"></i></div><h3 class="text-2xl font-bold">1. Daftar</h3><p class="text-gray-400">Buat akun gratis Anda.</p></div>
                <div class="text-center w-64" data-aos="fade-up"><div class="bg-slate-800 border-2 border-slate-700 text-blue-400 w-24 h-24 rounded-full flex items-center justify-center text-4xl font-bold mx-auto mb-4"><i class="fas fa-keyboard"></i></div><h3 class="text-2xl font-bold">2. Lacak</h3><p class="text-gray-400">Masukkan query Anda.</p></div>
                <div class="text-center w-64" data-aos="fade-left"><div class="bg-slate-800 border-2 border-slate-700 text-blue-400 w-24 h-24 rounded-full flex items-center justify-center text-4xl font-bold mx-auto mb-4"><i class="fas fa-clipboard-check"></i></div><h3 class="text-2xl font-bold">3. Analisis</h3><p class="text-gray-400">Dapatkan laporannya.</p></div>
            </div>
        </div>
    </div>
</section>

<section class="py-24 bg-slate-900 px-6">
    <div class="container mx-auto text-center" data-aos="zoom-in-up">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">Jangan Menunggu Hingga Terlambat.</h2>
        <p class="text-gray-400 mb-8 max-w-2xl mx-auto">Setiap detik, data yang bocor dapat digunakan untuk penipuan, peretasan, atau pencurian identitas. Ambil kendali atas jejak digital Anda sekarang.</p>
        <a href="index.php?page=register" class="bg-blue-600 text-white font-bold py-4 px-10 rounded-full text-lg hover:bg-blue-700 transition-transform transform hover:scale-105 shadow-2xl shadow-blue-600/30">Buat Akun Gratis</a>
    </div>
</section>

<style>
.typing-cursor{animation:blink-caret .75s step-end infinite}
@keyframes blink-caret{from,to{border-color:transparent}50%{border-color:#3B82F6}}
.feature-card { position: relative; overflow: hidden; transition: transform 0.3s ease; }
.feature-card:hover { transform: translateY(-5px); }
.feature-card::before {
    content: ''; position: absolute; left: 0; top: 0; width: 100%; height: 100%;
    background: radial-gradient(circle 80px at var(--x, 50%) var(--y, 50%), rgba(255, 255, 255, 0.08), transparent 80%);
    opacity: 0; transition: opacity 0.3s ease;
}
.feature-card:hover::before { opacity: 1; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Efek Mengetik
        const typingElement = document.getElementById('typing-effect');
        if (typingElement) {
            const words = ["Email", "Nomor Telepon", "Password", "Data Pribadi"];
            let wordIndex = 0, charIndex = 0, isDeleting = false;
            function type() {
                const currentWord = words[wordIndex], typingSpeed = isDeleting ? 100 : 200;
                if (isDeleting) {
                    typingElement.textContent = currentWord.substring(0, charIndex - 1);
                    charIndex--;
                } else {
                    typingElement.textContent = currentWord.substring(0, charIndex + 1);
                    charIndex++;
                }
                if (!isDeleting && charIndex === currentWord.length) {
                    setTimeout(() => isDeleting = true, 2000);
                } else if (isDeleting && charIndex === 0) {
                    isDeleting = false;
                    wordIndex = (wordIndex + 1) % words.length;
                }
                setTimeout(type, typingSpeed);
            }
            type();
        }

        // Efek Sorot Kartu Fitur
        document.querySelectorAll('.feature-card').forEach(card => {
            card.addEventListener('mousemove', e => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                card.style.setProperty('--x', `${x}px`);
                card.style.setProperty('--y', `${y}px`);
            });
        });

        // Animasi Partikel Latar Belakang
        if (document.getElementById('particles-js')) {
            particlesJS('particles-js', {
                "particles": {"number": {"value": 80,"density": {"enable": true,"value_area": 800}},"color": {"value": "#2563eb"},"shape": {"type": "circle"},"opacity": {"value": 0.5,"random": true},"size": {"value": 3,"random": true},"line_linked": {"enable": true,"distance": 150,"color": "#2563eb","opacity": 0.4,"width": 1},"move": {"enable": true,"speed": 1,"direction": "none","out_mode": "out"}},
                "interactivity": {"events": {"onhover": {"enable": true,"mode": "grab"},"onclick": {"enable": false}},"modes": {"grab": {"distance": 140,"line_linked": {"opacity": 1}}}},
                "retina_detect": true
            });
        }
    });
</script>