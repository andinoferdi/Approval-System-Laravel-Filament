<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Approval Pengajuan</title>
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- GSAP for animations -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    <!-- Three.js for 3D elements -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/0.155.0/three.min.js"></script>
    <!-- Lottie for vector animations -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js"></script>
    <!-- AOS (Animate On Scroll) -->
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}" type="image/x-icon">
</head>

<body class="bg-white text-accent-800 min-h-screen">
    <div class="progress-bar" id="progressBar"></div>

    <div class="hero-blob blob-1"></div>
    <div class="hero-blob blob-2"></div>
    <div id="scene-container"></div>

    <!-- Hero Section -->
    <section class="relative min-h-screen flex items-center pt-16 overflow-hidden" id="hero">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="text-center lg:text-left" data-aos="fade-right" data-aos-duration="1000">
                    <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold tracking-tight mb-6 leading-tight">
                        <span class="gradient-text">Sistem Approval</span> Pengajuan Modern
                    </h1>
                    <p class="mt-6 text-xl text-accent-600 max-w-2xl mx-auto lg:mx-0" data-aos="fade-right"
                        data-aos-delay="200" data-aos-duration="1000">
                        Transformasi digital untuk proses pengajuan dan persetujuan yang lebih efisien, transparan, dan
                        terukur.
                    </p>
                    <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start"
                        data-aos="fade-up" data-aos-delay="400" data-aos-duration="1000">
                        <a href="/dashboard"
                            class="group bg-gradient-to-r from-primary-500 to-secondary-500 text-white px-8 py-4 text-lg rounded-full transition-all duration-300 transform hover:scale-105 inline-flex items-center justify-center shadow-lg hover:shadow-xl">
                            Mulai Sekarang
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="ml-2 h-5 w-5 group-hover:translate-x-1 transition-transform" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="relative" data-aos="fade-left" data-aos-duration="1200">
                    <div class="lottie-container" id="hero-animation"></div>
                    <div
                        class="absolute -bottom-10 -right-10 w-40 h-40 bg-primary-100 rounded-full filter blur-3xl opacity-30 animate-pulse-slow">
                    </div>
                    <div class="absolute -top-10 -left-10 w-40 h-40 bg-secondary-100 rounded-full filter blur-3xl opacity-30 animate-pulse-slow"
                        style="animation-delay: 1s;"></div>
                </div>
            </div>
        </div>
        <div class="scroll-indicator">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-accent-400" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
            </svg>
        </div>
    </section>

    <!-- Workflow Section -->
    <section class="py-20 bg-accent-50" id="workflow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16" data-aos="fade-up" data-aos-duration="800">
                <h2 class="text-3xl sm:text-4xl font-bold mb-4">Alur Proses Pengajuan</h2>
                <p class="text-accent-600 max-w-2xl mx-auto">Sistem kami menyederhanakan proses pengajuan dengan alur
                    kerja yang terstruktur dan transparan.</p>
            </div>
            <div class="relative">
                <div
                    class="hidden md:block flow-line left-[calc(12.5%-30px)] right-[calc(12.5%-30px)] md:left-[calc(25%-40px)] md:right-[calc(25%-40px)]">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8 relative z-10">
                    <div class="flow-card glass-card rounded-2xl p-6 active" id="flow-card-1" data-aos="zoom-in"
                        data-aos-duration="800" data-aos-delay="100">
                        <div
                            class="w-12 h-12 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center font-bold text-xl mb-4">
                            1</div>
                        <h4 class="text-lg font-semibold mb-2">Pengajuan Pegawai</h4>
                        <p class="text-accent-600">Pegawai mengisi form pengajuan digital dan melampirkan dokumen
                            pendukung yang diperlukan.</p>
                    </div>
                    <div class="flow-card glass-card rounded-2xl p-6" id="flow-card-2" data-aos="zoom-in"
                        data-aos-duration="800" data-aos-delay="200">
                        <div
                            class="w-12 h-12 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center font-bold text-xl mb-4">
                            2</div>
                        <h4 class="text-lg font-semibold mb-2">Persetujuan Atasan Langsung</h4>
                        <p class="text-accent-600">Atasan menerima notifikasi dan meninjau pengajuan dengan detail
                            lengkap sebelum memberikan keputusan.</p>
                    </div>
                    <div class="flow-card glass-card rounded-2xl p-6" id="flow-card-3" data-aos="zoom-in"
                        data-aos-duration="800" data-aos-delay="300">
                        <div
                            class="w-12 h-12 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center font-bold text-xl mb-4">
                            3</div>
                        <h4 class="text-lg font-semibold mb-2">Persetujuan Kepala Departemen</h4>
                        <p class="text-accent-600">Pengajuan yang disetujui atasan langsung diteruskan ke kepala
                            departemen untuk ditinjau lebih lanjut.</p>
                    </div>
                    <div class="flow-card glass-card rounded-2xl p-6" id="flow-card-4" data-aos="zoom-in"
                        data-aos-duration="800" data-aos-delay="400">
                        <div
                            class="w-12 h-12 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center font-bold text-xl mb-4">
                            4</div>
                        <h4 class="text-lg font-semibold mb-2">Persetujuan Final HRD/Direksi</h4>
                        <p class="text-accent-600">Tahap akhir persetujuan yang menentukan status final pengajuan
                            dengan notifikasi otomatis ke semua pihak.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-12 border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
                <div data-aos="fade-up" data-aos-duration="800" data-aos-delay="100">
                    <div class="flex items-center space-x-2 mb-6">
                        <span class="font-bold text-xl">Sistem Approval Pengajuan</span>
                    </div>

                    <div class="flex space-x-4">
                        <a href="#" class="text-accent-600 hover:text-primary-600 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z" />
                            </svg>
                        </a>
                        <a href="#" class="text-accent-600 hover:text-primary-600 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                            </svg>
                        </a>
                        <a href="#" class="text-accent-600 hover:text-primary-600 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z" />
                            </svg>
                        </a>
                    </div>
                </div>
                <div data-aos="fade-up" data-aos-duration="800" data-aos-delay="200">
                    <h4 class="font-bold text-lg mb-6">Produk</h4>
                    <ul class="space-y-4">
                        <li><a href="#"
                                class="text-accent-600 hover:text-primary-600 transition-colors">Fitur</a></li>
                        <li><a href="#"
                                class="text-accent-600 hover:text-primary-600 transition-colors">Harga</a></li>
                        <li><a href="#"
                                class="text-accent-600 hover:text-primary-600 transition-colors">Integrasi</a></li>
                        <li><a href="#"
                                class="text-accent-600 hover:text-primary-600 transition-colors">Roadmap</a></li>
                    </ul>
                </div>
                <div data-aos="fade-up" data-aos-duration="800" data-aos-delay="300">
                    <h4 class="font-bold text-lg mb-6">Perusahaan</h4>
                    <ul class="space-y-4">
                        <li><a href="#" class="text-accent-600 hover:text-primary-600 transition-colors">Tentang
                                Kami</a></li>
                        <li><a href="#"
                                class="text-accent-600 hover:text-primary-600 transition-colors">Blog</a></li>
                        <li><a href="#"
                                class="text-accent-600 hover:text-primary-600 transition-colors">Karir</a></li>
                        <li><a href="#"
                                class="text-accent-600 hover:text-primary-600 transition-colors">Kontak</a></li>
                    </ul>
                </div>
                <div data-aos="fade-up" data-aos-duration="800" data-aos-delay="400">
                    <h4 class="font-bold text-lg mb-6">Dukungan</h4>
                    <ul class="space-y-4">
                        <li><a href="#"
                                class="text-accent-600 hover:text-primary-600 transition-colors">Dokumentasi</a></li>
                        <li><a href="#" class="text-accent-600 hover:text-primary-600 transition-colors">Pusat
                                Bantuan</a></li>
                        <li><a href="#" class="text-accent-600 hover:text-primary-600 transition-colors">Status
                                Sistem</a></li>
                        <li><a href="#"
                                class="text-accent-600 hover:text-primary-600 transition-colors">Kebijakan Privasi</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="pt-8 border-t border-gray-200 flex flex-col md:flex-row justify-between items-center">
                <div class="text-accent-600 mb-4 md:mb-0">
                    &copy; {{ date('Y') }} SistemApproval. Hak Cipta Dilindungi.
                </div>
                <div class="flex space-x-6">
                    <a href="#" class="text-accent-600 hover:text-primary-600 transition-colors">
                        Syarat Layanan
                    </a>
                    <a href="#" class="text-accent-600 hover:text-primary-600 transition-colors">
                        Kebijakan Privasi
                    </a>
                    <a href="#" class="text-accent-600 hover:text-primary-600 transition-colors">
                        Cookies
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Custom JS -->
    <script src="{{ asset('js/welcome.js') }}"></script>

    <!-- Initialize AOS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            AOS.init({
                once: false,
                mirror: true,
                anchorPlacement: 'top-bottom',
                offset: 120
            });
        });
    </script>
</body>

</html>
