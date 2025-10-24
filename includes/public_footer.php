</main>
    <footer class="bg-slate-900 py-8 border-t border-slate-800">
        <div class="container mx-auto text-center text-gray-500">
            <div class="space-x-6 mb-4">
                <a href="index.php?page=about" class="hover:text-gray-300 transition">Tentang Kami</a>
                <a href="index.php?page=faq" class="hover:text-gray-300 transition">FAQ</a>
                <a href="index.php?page=contact" class="hover:text-gray-300 transition">Kontak</a>
            </div>
            &copy; <?= date('Y') ?> JejakIntel. Dibuat untuk melindungi jejak digital Anda.
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true,
        });
    </script>
</body>
</html>