/* ============================================================
 *  BLOKIR TOMBOL FORWARD BROWSER
 *  ------------------------------------------------------------
 *  Browser tidak mengizinkan JS meng-nonaktifkan ikon tombol
 *  Forward secara visual (itu murni bagian UI browser).
 *  Tapi kita bisa membuat aksi "maju" tidak melakukan apa-apa:
 *  setiap kali riwayat browser mencoba berpindah (via tombol
 *  Forward ataupun Back), skrip ini langsung mendorong halaman
 *  saat ini kembali ke posisi teratas riwayat. Hasilnya: user
 *  tetap "terkunci" di halaman yang sedang dibuka.
 * ============================================================ */
(function () {
    // Tambahkan duplikat entry riwayat untuk halaman saat ini.
    history.pushState(null, document.title, location.href);

    window.addEventListener('popstate', function () {
        // Setiap kali ada percobaan pindah riwayat (maju/mundur),
        // dorong lagi entry halaman ini ke atas -> tombol Forward
        // jadi tidak pernah benar-benar memindahkan halaman.
        history.pushState(null, document.title, location.href);
    });

    // ============================================================
    //  ANTI-BFCACHE — Setelah logout, kalau user pencet Back/Forward
    //  dan browser mencoba menampilkan halaman ini dari cache memori
    //  (bfcache) tanpa request baru ke server, paksa reload supaya
    //  PHP mengecek ulang session. Kalau session sudah tidak ada
    //  (sudah logout), PHP otomatis redirect ke login.php.
    // ============================================================
    window.addEventListener('pageshow', function (event) {
        if (event.persisted) {
            location.reload();
        }
    });
})();
