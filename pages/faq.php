<div class="py-16 sm:py-24 px-6 bg-slate-900 text-gray-300">
    <div class="container mx-auto max-w-4xl">
        <div class="text-center mb-16" data-aos="fade-down">
            <i class="fas fa-question-circle text-blue-500 text-5xl mb-4"></i>
            <h1 class="text-4xl md:text-5xl font-extrabold text-white">Pertanyaan Umum (FAQ)</h1>
            <p class="text-lg text-gray-400 mt-2">Temukan jawaban atas pertanyaan yang paling sering diajukan.</p>
        </div>

        <div id="faq-accordion" class="space-y-4">
            <div class="accordion-item bg-slate-800 border border-slate-700 rounded-lg overflow-hidden" data-aos="fade-up">
                <button class="accordion-header w-full text-left p-5 font-semibold text-lg text-white hover:bg-slate-700/50 flex justify-between items-center transition-colors">
                    <span>Bagaimana kebocoran data terjadi?</span>
                    <i class="fas fa-chevron-down transform transition-transform duration-300"></i>
                </button>
                <div class="accordion-content hidden p-5 border-t border-slate-700">
                    <div class="prose prose-invert max-w-none text-gray-300">
                        <p>Kebocoran data paling sering terjadi karena peretasan. Penyebabnya bisa kerentanan dalam kode, kata sandi lemah, hingga keterlibatan karyawan. Selain itu, data juga bisa tersebar melalui <strong>Parsing</strong> (pengumpulan data publik otomatis) dan <strong>Stealer</strong> (virus pencuri password).</p>
                    </div>
                </div>
            </div>

            <div class="accordion-item bg-slate-800 border border-slate-700 rounded-lg overflow-hidden" data-aos="fade-up">
                <button class="accordion-header w-full text-left p-5 font-semibold text-lg text-white hover:bg-slate-700/50 flex justify-between items-center transition-colors">
                    <span>Mengapa beberapa kata sandi ditampilkan terenkripsi?</span>
                    <i class="fas fa-chevron-down transform transition-transform duration-300"></i>
                </button>
                <div class="accordion-content hidden p-5 border-t border-slate-700">
                    <div class="prose prose-invert max-w-none text-gray-300">
                        <p>Istilah yang lebih tepat adalah <strong>hashing</strong>, sebuah proses keamanan satu arah. Kata sandi Anda diubah menjadi kode acak (hash) yang tidak bisa dikembalikan ke bentuk aslinya. Situs menyimpan hash ini, bukan password Anda, untuk memverifikasi login Anda dengan aman.</p>
                    </div>
                </div>
            </div>

            <div class="accordion-item bg-slate-800 border border-slate-700 rounded-lg overflow-hidden" data-aos="fade-up">
                <button class="accordion-header w-full text-left p-5 font-semibold text-lg text-white hover:bg-slate-700/50 flex justify-between items-center transition-colors">
                    <span>Bagaimana cara melindungi diri dari kebocoran data?</span>
                    <i class="fas fa-chevron-down transform transition-transform duration-300"></i>
                </button>
                <div class="accordion-content hidden p-5 border-t border-slate-700">
                    <div class="prose prose-invert max-w-none text-gray-300">
                        <p>Anda tidak bisa 100% terlindung, tapi bisa meminimalkan risiko. Kuncinya: minimalkan koneksi antar data pribadi Anda. Gunakan email, nomor telepon, dan password yang berbeda untuk setiap layanan penting. Anggap setiap data yang Anda berikan online berpotensi menjadi publik.</p>
                    </div>
                </div>
            </div>
             <div class="accordion-item bg-slate-800 border border-slate-700 rounded-lg overflow-hidden" data-aos="fade-up">
                <button class="accordion-header w-full text-left p-5 font-semibold text-lg text-white hover:bg-slate-700/50 flex justify-between items-center transition-colors">
                    <span>Untuk apa kebocoran data digunakan?</span>
                    <i class="fas fa-chevron-down transform transition-transform duration-300"></i>
                </button>
                <div class="accordion-content hidden p-5 border-t border-slate-700">
                    <div class="prose prose-invert max-w-none text-gray-300">
                        <p>Data bocor digunakan untuk berbagai tujuan, mulai dari <strong>pemasaran</strong> (iklan tertarget, spam), <strong>penagihan utang</strong>, <strong>investigasi</strong> (jurnalistik atau doxing), hingga yang paling berbahaya: <strong>peretasan dan penipuan</strong> untuk mengambil alih akun media sosial, email, bahkan rekening bank.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const accordionHeaders = document.querySelectorAll('#faq-accordion .accordion-header');
    accordionHeaders.forEach(header => {
        header.addEventListener('click', () => {
            const content = header.nextElementSibling;
            const icon = header.querySelector('i.fa-chevron-down');
            content.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
        });
    });
});
</script>