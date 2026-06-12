<?php
session_start();
include 'koneksi.php';

// Logika Keranjang Belanja PHP
if (isset($_GET['action'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action == 'add') {
        $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + 1;
    } elseif ($action == 'kurang') {
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]--;
            if ($_SESSION['cart'][$id] <= 0) {
                unset($_SESSION['cart'][$id]);
            }
        }
    } elseif ($action == 'hapus') {
        unset($_SESSION['cart'][$id]);
    }

    // Redirect kembali agar URL bersih dan menyimpan state open_cart
    $redirect_url = 'index.php';
    if (isset($_GET['open_cart'])) {
        $redirect_url .= '?open_cart=1';
    }
    header('Location: ' . $redirect_url);
    exit;
}

$is_open = isset($_GET['open_cart']) || isset($_GET['action']);
$cart_toggle_url = $is_open ? 'index.php' : 'index.php?open_cart=1';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sakila Store</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<!-- ICON KERANJANG -->
<a href="<?php echo $cart_toggle_url; ?>" style="text-decoration: none; color: inherit;">
    <div class="cart-icon">
        <i class="fas fa-shopping-cart"></i>
        <span id="cart-count"><?php 
            $cart_count = 0;
            if (!empty($_SESSION['cart'])) {
                $cart_count = array_sum($_SESSION['cart']);
            }
            echo $cart_count;
        ?></span>
    </div>
</a>
  <header class="hero">
    <div class="hero-text">
      <h1>Toko Kila</h1>
      <p>
        Hijab premium untuk muslimah modern.
        Sakila Store hadir untuk memberikan hijab berkualitas tinggi dengan desain elegan, nyaman dipakai, dan mudah diatur untuk setiap aktivitasmu.
      </p>
      <a href="#product-section"><button>Belanja Sekarang!</button></a>
    </div>

    <div class="hero-image">
      <img src="admin/upload/sakila.jpeg" alt="Hijab">
    </div>
  </header>

  <section class="about-section">
    <h2>Tentang kami</h2>
    <p>
      Sakila Store adalah brand hijab lokal yang berdedikasi menghadirkan produk hijab premium dengan kualitas terbaik.
      Kami berkomitmen untuk memenuhi kebutuhan muslimah modern yang menginginkan hijab lembut, nyaman, mudah dibentuk, serta tetap stylish dalam berbagai kesempatan.<br><br>
      Dipilih dengan teliti dari bahan terbaik dan diproses dengan standar tinggi untuk memastikan kepuasan setiap pelanggan.
    </p>
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

                <a href="index.php?action=add&id=<?php echo $row['id']; ?>&open_cart=1" style="text-decoration: none;">
                    <button class="add-to-cart">
                        Tambah ke Keranjang
                    </button>
                </a>

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
<?php
$cart_active = (isset($_GET['open_cart']) || isset($_GET['action'])) ? 'active' : '';
?>
  <section id="cart-section" class="cart-sidebar <?= $cart_active ?>">
    <h2>Keranjang Belanja <a href="index.php" style="float: right; text-decoration: none; color: #b04d6d; font-size: 1.2em; line-height: 1;">&times;</a></h2>
    <form action="proses_checkout.php" method="POST">
      <ul id="cart-items" style="list-style: none; padding: 0; margin-bottom: 30px;">
          <?php
          $total = 0;
          if (!empty($_SESSION['cart'])) {
              foreach ($_SESSION['cart'] as $id => $qty) {
                  $id = intval($id);
                  $p_query = mysqli_query($conn, "SELECT * FROM products WHERE id='$id'");
                  $row = mysqli_fetch_assoc($p_query);
                  if ($row) {
                      $subtotal = $row['price'] * $qty;
                      $total += $subtotal;
                      ?>
                      <li style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                          <div style="flex-grow: 1; text-align: left;">
                              <b><?= htmlspecialchars($row['product_name']) ?></b><br>
                              Rp <?= number_format($subtotal, 0, ',', '.') ?>
                              <br><br>
                              <a href="index.php?action=kurang&id=<?= $id ?>&open_cart=1" style="text-decoration: none;"><button type="button" class="qty-btn">-</button></a>
                              <span style="margin: 0 10px; font-weight: bold;"><?= $qty ?></span>
                              <a href="index.php?action=add&id=<?= $id ?>&open_cart=1" style="text-decoration: none;"><button type="button" class="qty-btn">+</button></a>
                          </div>
                          <a href="index.php?action=hapus&id=<?= $id ?>&open_cart=1" style="color: #ff4d4d; font-size: 1.1em; text-decoration: none; margin-left: 10px;">
                              <i class="fas fa-trash-alt"></i>
                          </a>
                      </li>
                      <?php
                  }
              }
          } else {
              echo "<li style='color: #888;'>Keranjang masih kosong</li>";
          }
          ?>
      </ul>
      <p style="margin-bottom: 30px;">
          <strong>Total: <span id="cart-total">Rp <?= number_format($total, 0, ',', '.') ?></span></strong>
      </p>
      <div style="margin-bottom: 30px;" class="payment-group">
          <input type="text" name="nama" id="nama" placeholder="Nama Lengkap" required>
          <input type="email" name="email" id="email" placeholder="Email" required>
          <input type="text" name="no_hp" id="no_hp" placeholder="No HP" required>
          <textarea name="alamat" id="alamat" placeholder="Alamat Lengkap" required></textarea>

          <h3>Pilih Metode Pembayaran</h3>
          
          <input type="radio" name="payment" id="payment-qris" value="QRIS" checked style="width: auto; display: inline-block;">
          <label for="payment-qris" style="display: inline-block; margin-right: 15px; cursor: pointer; font-weight: bold;">QRIS</label>
          
          <input type="radio" name="payment" id="payment-bca" value="Transfer BCA" style="width: auto; display: inline-block;">
          <label for="payment-bca" style="display: inline-block; cursor: pointer; font-weight: bold;">Transfer Bank BCA</label>
          
          <br><br>

          <div id="qris-info" style="background: #f8f8f8; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
              <h4>Pembayaran QRIS</h4>
              <img src="admin/upload/qr.jpeg" width="200">
              <p>Silakan scan QRIS untuk melakukan pembayaran.</p>
          </div>

          <div id="bca-info" style="background: #f8f8f8; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
              <h4>Transfer Bank BCA</h4>
              <p>No Rekening: <strong>6821700148</strong></p>
              <p>Atas Nama: <strong>Sakila</strong></p>
          </div>
      </div>

      <?php if (!empty($_SESSION['cart'])): ?>
          <button type="submit" name="checkout" id="checkout-wa" style="background: #25d366; width: 100%; border: none; padding: 12px; color: white; font-weight: bold; border-radius: 8px; cursor: pointer;">Checkout ke WhatsApp</button>
      <?php else: ?>
          <button type="button" onclick="alert('Keranjang masih kosong')" style="background: #ccc; width: 100%; border: none; padding: 12px; color: #666; font-weight: bold; border-radius: 8px; cursor: not-allowed;">Checkout ke WhatsApp</button>
      <?php endif; ?>
    </form>
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
        <li>Memberikan pelayanan terbaik dan amanah bagi pelanggan</li>
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
    <p>📞 085723163866</p>
    <p>📧 sakilananda5@gmail.com</p>
    <p>📍 Bogor</p>
    <p>📷 @sakila.store</p>
  </section>
</section>
  <footer>
    <p>© 2026 Sakila Store</p>
  </footer>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        const qrisRadio = document.getElementById('payment-qris');
        const bcaRadio = document.getElementById('payment-bca');
        const qrisInfo = document.getElementById('qris-info');
        const bcaInfo = document.getElementById('bca-info');

        function togglePaymentInfo() {
            if (qrisRadio.checked) {
                qrisInfo.style.display = 'block';
                bcaInfo.style.display = 'none';
            } else if (bcaRadio.checked) {
                qrisInfo.style.display = 'none';
                bcaInfo.style.display = 'block';
            }
        }

        if (qrisRadio && bcaRadio && qrisInfo && bcaInfo) {
            qrisRadio.addEventListener('change', togglePaymentInfo);
            bcaRadio.addEventListener('change', togglePaymentInfo);
            togglePaymentInfo(); // Run once at load
        }
    });
  </script>
</body>
</html>