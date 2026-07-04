<?php
// Mengambil koneksi database yang benar dari file koneksi.php
include 'koneksi.php';

// ============================================================
//  SESSION VISITOR — catat kunjungan & simpan data sementara
// ============================================================
if (!isset($_SESSION['visitor_start'])) {
    $_SESSION['visitor_start'] = time();
    $_SESSION['page_views']    = 1;
} else {
    $_SESSION['page_views'] = ($_SESSION['page_views'] ?? 0) + 1;
}

// Jam sekarang untuk timestamp balon chat
$jam_chat = date('H:i');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sakila Store</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="style.css">
  <script src="no-forward.js"></script>
</head>
<body>
<!-- ICON KERANJANG -->
<div class="cart-icon" id="cart-icon">
    <i class="fas fa-shopping-cart"></i>
    <span id="cart-count">0</span>
</div>
  <header class="hero">
    <div class="hero-text">
      <h1>Toko Kila</h1>
      <p>
        Hijab premium untuk muslimah modern.
        Sakila Store hadir untuk memberikan hijab berkualitas tinggi dengan desain elegan, nyaman dipakai, dan mudah diatur untuk setiap aktivitasmu.
      </p>
      <a href="#product-section"><button>Belanja Sekarang!</button></a>
      <a href="https://wa.me/6285723163866" target="_blank"
       style="background: #22c55e; color: white; padding: 8px 15px; border-radius: 8px; text-decoration: none; font-size: 0.85rem; font-weight: 600;">
        hubungi kami
    </a>
    </div>
<div class="hero-image">
    <img src="admin/upload/banner.jpeg" alt="Hijab">
</div>
  </header>

 <div style="display: flex; gap: 20px;">
  <!--sisi kiri--></div>
 <section class="product">
      <div style="flex: 1;">
    <h2>Tentang kami</h2>
    <p>
      Sakila Store adalah brand hijab lokal yang berdedikasi menghadirkan produk hijab premium dengan kualitas terbaik.
      Kami berkomitmen untuk memenuhi kebutuhan muslimah modern yang menginginkan hijab lembut, nyaman, mudah dibentuk, serta tetap stylish dalam berbagai kesempatan.<br><br>
      Dipilih dengan teliti dari bahan terbaik dan diproses dengan standar tinggi untuk memastikan kepuasan setiap pelanggan.
    </p>
    </div>
  </section>

 <section class="product" id="product-section">
    <h2>Product</h2>

    <div class="product-list">

    <?php
    $query = mysqli_query($conn, "SELECT * FROM products");

    while($row = mysqli_fetch_assoc($query)){
    ?>

        <div class="card">

            <img src="<?php echo $row['image']; ?>" alt="">

            <h4><?php echo $row['product_name']; ?></h4>

            <p>
                Rp <?php echo number_format($row['price'],0,',','.'); ?>
            </p>

            <p>
                Stok: <?php echo $row['stock']; ?>
            </p>

            <?php if($row['stock'] > 0){ ?>

                <button
                    class="add-to-cart"
                    data-id="<?php echo $row['id']; ?>"
                    data-name="<?php echo $row['product_name']; ?>"
                    data-price="<?php echo $row['price']; ?>"
                    data-stock="<?php echo $row['stock']; ?>">
                    Tambah ke Keranjang
                </button>

            <?php } else { ?>

                <button disabled
                    style="
                    background:#ccc;
                    color:#666;
                    cursor:not-allowed;">
                    Stok Habis
                </button>

            <?php } ?>

        </div>

    <?php } ?>

    </div>
</section>
<section id="cart-section" class="cart-sidebar">
    
    <button id="close-cart" style="background: #dc3545; margin-bottom: 20px; width: auto; padding: 5px 15px;">
   <i class="fas fa-times"></i>
</button>

    <h2>Keranjang Belanja</h2>
    <ul id="cart-items" style="list-style: none; padding: 0; margin-bottom: 30px;"></ul>
    <p style="margin-bottom: 30px;">
    <strong>Total: <span id="cart-total">Rp 0</span></strong>
    </p>

  <div style="margin-bottom:30px;">

    <input type="text" id="nama" placeholder="Nama Lengkap" required>
    <input type="email" id="email" placeholder="Email" required>
    <input type="text"
       id="no_hp"
       placeholder="No HP"
       maxlength="13"
       required
       oninput="this.value=this.value.replace(/[^0-9]/g,'')">
    <textarea id="alamat" placeholder="Alamat Lengkap"></textarea>

    <h3>Pilih Metode Pembayaran</h3>

    <label>
        <input type="radio" name="payment" value="QRIS" checked>
        QRIS
    </label>

    <br><br>

    <label>
        <input type="radio" name="payment" value="Transfer BCA">
        Transfer Bank BCA
    </label>

</div>
<div id="qris-info" style="
background:#f8f8f8;
padding:15px;
border-radius:10px;
margin-bottom:20px;
">

    <h4>Pembayaran QRIS</h4>
    <img src="admin/upload/qr.jpeg" width="200">
    <p>Silakan scan QRIS untuk melakukan pembayaran.</p>
      <p>Setelah melakukan pembayaran,
mohon kirimkan bukti transfer melalui WhatsApp Admin
untuk proses verifikasi pesanan.</p>

</div>
<div id="bca-info" style="
display:none;
background:#f8f8f8;
padding:15px;
border-radius:10px;
margin-bottom:20px;
">

    <h4>Transfer Bank BCA</h4>
    <p>
        No Rekening :
        <strong>6821700148</strong>
    </p>
    <p>
        Atas Nama :
        <strong>Sakila</strong>
    </p>
      <p>Setelah melakukan pembayaran,
mohon kirimkan bukti transfer melalui WhatsApp Admin
untuk proses verifikasi pesanan.</p>

</div>

    <button id="checkout-wa" style="background: #25d366;">Checkout ke WhatsApp</button>
  </section>

  <section class="fade-in">
    <div class="container">
      <h2 class="section-title">Rating</h2>
      <p class="section-subtitle">Prinsip yang menjadi fondasi Sakila Store dalam melayani setiap muslimah.</p>
      <div class="values-grid">
        <div class="value-card">
          <div class="value-icon">
            <i class="fas fa-award"></i>
          </div>
          <h3>Kualitas</h3>
          <p>Kami hanya menyediakan produk dengan standar kualitas tertinggi</p>
        </div>
        <div class="value-card">
          <div class="value-icon">
            <i class="fas fa-hand-holding-heart"></i>
          </div>
          <h3>Amanah</h3>
          <p>Keberkahan dalam setiap produk dan pelayanan yang kami berikan</p>
        </div>
        <div class="value-card">
          <div class="value-icon">
            <i class="fas fa-users"></i>
          </div>
          <h3>Pelanggan</h3>
          <p>Kepuasan pelanggan adalah prioritas utama kami</p>
        </div>
        <div class="value-card">
          <div class="value-icon">
            <i class="fas fa-lightbulb"></i>
          </div>
          <h3>Inovasi</h3>
          <p>Selalu berinovasi dalam desain dan kualitas produk</p>
        </div>
      </div>
    </div>
  </section>

 <section class="visi">
    <div class="box">
      <h3>Visi</h3>
      <p><br>
        Menjadi brand hijab pilihan utama muslimah Indonesia yang dikenal karena kualitas, desain, dan pelayanan terbaik.
      </p>
    </div>

    <div class="box">
      <h3>Misi</h3>
      <ul><br>
        <li>Menghadirkan hijab berkualitas premium dengan harga terbaik</li>
        <li>Mengikuti perkembangan tren hijab muslimah modern</li>
        <li>Member給kan pelayanan terbaik dan amanah bagi pelanggan</li>
        <li>Memberdayakan muslimah melalui produk berkualitas</li>
      </ul>
    </div>
  </section>

    
  <section class="fade-in" style="background: #faf9fb;">
    <div class="container">
      <h2 class="section-title">Mengapa Memilih Toko kila?</h2>
      <p class="section-subtitle">Alasan muslimah mempercayakan kebutuhan hijabnya kepada Sakila Store.</p>
      <div class="why-choose-grid">
        <div class="why-card">
          <div class="why-icon">
            <i class="fas fa-shirt"></i>
          </div>
          <div>
            <h4>Bahan Premium</h4>
            <p>Seleksi bahan terbaik yang lembut, adem, dan tahan lama</p>
          </div>
        </div>
        <div class="why-card">
          <div class="why-icon">
            <i class="fas fa-smile-beam"></i>
          </div>
          <div>
            <h4>Nyaman Dipakai</h4>
            <p>Desain yang nyaman digunakan sepanjang hari tanpa menyiksa</p>
          </div>
        </div>
        <div class="why-card">
          <div class="why-icon">
            <i class="fas fa-palette"></i>
          </div>
          <div>
            <h4>Desain Kekinian</h4>
            <p>Trendy dan up to date mengikuti fashion muslim terkini</p>
          </div>
        </div>
        <div class="why-card">
          <div class="why-icon">
            <i class="fas fa-gift"></i>
          </div>
          <div>
            <h4>Packing Exclusive</h4>
            <p>Kemasan premium yang cantik dan siap dijadikan hadiah</p>
          </div>
        </div>
        <div class="why-card">
          <div class="why-icon">
            <i class="fas fa-truck-fast"></i>
          </div>
          <div>
            <h4>Pengiriman Cepat</h4>
            <p>Proses pengiriman cepat dan tracking real-time</p>
          </div>
        </div>
        <div class="why-card">
          <div class="why-icon">
            <i class="fas fa-headset"></i>
          </div>
          <div>
            <h4>Pelayanan Terbaik</h4>
            <p>Customer service responsif dan ramah 24/7</p>
          </div>
        </div>
      </div>
    </div>
  
  </section>

  <section class="contact">
    <h2>Informasi Kontak</h2>
<div style="display: flex; justify-content: center; gap: 10px; margin-top: 10px;">
    
</div>
    <p>📧 sakilananda5@gmail.com</p>
    <p>📍 Bogor</p>
    <p>📷 @sakila.store</p>
  </section>

  <footer>
    <p>© 2026 Sakila Store</p>
  </footer>


  <!-- ============================================================
       WHATSAPP CHAT WIDGET — "Tanya Kila"
       ============================================================ -->

  <!-- Overlay gelap (mobile) -->
  <div class="wa-overlay" id="wa-overlay"></div>

  <!-- Popup chat window -->
  <div class="wa-popup" id="wa-popup" role="dialog" aria-label="Chat WhatsApp Tanya Kila">
    <!-- Header -->
    <div class="wa-popup-header">
      <div class="wa-logo">
        <i class="fab fa-whatsapp"></i>
      </div>
      <div class="wa-info">
        <div class="wa-name">Tanya Kila 💬</div>
        <div class="wa-status">
          <span class="dot"></span>
          Online sekarang
        </div>
      </div>
      <button class="wa-popup-close" id="wa-popup-close" aria-label="Tutup chat">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <!-- Balon pesan otomatis -->
    <div class="wa-popup-body">
      <div class="wa-bubble">
        Halo! 👋 Selamat datang di <strong>Sakila Store</strong>.<br>
        Ada yang bisa Kila bantu hari ini?<br>
        Silakan ketik pertanyaanmu, kami siap membantu! 😊
        <div class="wa-time"><?= $jam_chat ?></div>
      </div>
    </div>

    <!-- Footer input pesan -->
    <div class="wa-popup-footer">
      <input type="text" id="wa-msg-input"
             placeholder="Ketik pesan..."
             aria-label="Pesan WhatsApp"
             maxlength="300">
      <button class="wa-send-btn" id="wa-send-btn" aria-label="Kirim ke WhatsApp">
        <i class="fab fa-whatsapp"></i>
      </button>
    </div>
  </div>

  <!-- Tombol floating "Tanya Kila" -->
  <div class="wa-chat-btn" id="wa-chat-btn" role="button"
       tabindex="0" aria-label="Tanya Kila via WhatsApp">
    <div class="wa-circle">
      <i class="fab fa-whatsapp"></i>
    </div>
    <div class="wa-label">Tanya Kila</div>
  </div>

  <script src="script.js"></script>

</body>
</html>