<?php
include 'koneksi.php';

// ============================================================
//  Hanya terima POST
// ============================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Method Not Allowed");
}

// ============================================================
//  CSRF CHECK
// ============================================================
$token = $_POST['csrf_token'] ?? '';
if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Permintaan tidak valid.']);
    exit;
}

// ============================================================
//  RATE LIMITING — max 5 checkout per 10 menit
// ============================================================
if (!isset($_SESSION['rate_limit'])) $_SESSION['rate_limit'] = [];
$now = time();
$_SESSION['rate_limit'] = array_filter(
    $_SESSION['rate_limit'],
    fn($t) => ($now - $t) < 600
);
if (count($_SESSION['rate_limit']) >= 5) {
    http_response_code(429);
    echo json_encode(['status' => 'error', 'message' => 'Terlalu banyak permintaan. Coba lagi dalam beberapa menit.']);
    exit;
}
$_SESSION['rate_limit'][] = $now;

// ============================================================
//  SANITASI & VALIDASI INPUT
// ============================================================
// Untuk data yang DISIMPAN ke database: cukup strip_tags + trim
// htmlspecialchars() TIDAK dipakai di sini agar nama tidak berubah jadi HTML entity
// (misal: Nur'aini tidak jadi Nur&#039;aini)
// htmlspecialchars() hanya dipakai saat MENAMPILKAN data ke HTML
function bersih($val) {
    return strip_tags(trim($val));
}

$nama    = bersih($_POST['nama'] ?? '');
$email   = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$no_hp   = preg_replace('/[^0-9]/', '', $_POST['no_hp'] ?? '');
$alamat  = bersih($_POST['alamat'] ?? '');
$items   = $_POST['items'] ?? '';
$payment = bersih($_POST['payment'] ?? '');

if (empty($nama) || empty($email) || empty($no_hp) || empty($alamat) || empty($items)) {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Email tidak valid.']);
    exit;
}
if (strlen($nama) > 100 || strlen($alamat) > 500 || strlen($no_hp) > 15) {
    echo json_encode(['status' => 'error', 'message' => 'Data melebihi batas karakter.']);
    exit;
}
$allowed_payment = ['QRIS', 'Transfer BCA'];
if (!in_array($payment, $allowed_payment)) {
    echo json_encode(['status' => 'error', 'message' => 'Metode pembayaran tidak valid.']);
    exit;
}

$produk = json_decode($items, true);
if (!is_array($produk) || count($produk) === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Keranjang kosong atau tidak valid.']);
    exit;
}

foreach ($produk as $item) {
    if (!isset($item['name'], $item['qty'])) {
        echo json_encode(['status' => 'error', 'message' => 'Data produk tidak valid.']);
        exit;
    }
    if ((int)$item['qty'] <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Qty tidak valid.']);
        exit;
    }
}

// ============================================================
//  HITUNG TOTAL HARGA DARI DATABASE (bukan dari client!)
//  Ini mencegah pembeli memanipulasi harga.
// ============================================================
$total = 0;
$produk_verified = [];

foreach ($produk as $item) {
    $product_name = trim($item['name']);
    $qty = (int)$item['qty'];

    $stmt_price = mysqli_prepare($conn,
        "SELECT id, product_name, price, stock FROM products WHERE product_name = ? LIMIT 1"
    );
    mysqli_stmt_bind_param($stmt_price, "s", $product_name);
    mysqli_stmt_execute($stmt_price);
    $res = mysqli_stmt_get_result($stmt_price);
    $db_product = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt_price);

    if (!$db_product) {
        echo json_encode(['status' => 'error', 'message' => "Produk '$product_name' tidak ditemukan."]);
        exit;
    }
    if ($db_product['stock'] < $qty) {
        echo json_encode(['status' => 'error', 'message' => "Stok '$product_name' tidak mencukupi."]);
        exit;
    }

    // Pakai harga dari database
    $total += $db_product['price'] * $qty;
    $produk_verified[] = [
        'name'  => $db_product['product_name'],
        'price' => $db_product['price'],
        'qty'   => $qty
    ];
}

if ($total <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Total tidak valid.']);
    exit;
}

// ============================================================
//  SIMPAN CUSTOMER
// ============================================================
$stmt_cust = mysqli_prepare($conn,
    "INSERT INTO customers (nama, email, no_hp, alamat) VALUES (?, ?, ?, ?)"
);
mysqli_stmt_bind_param($stmt_cust, "ssss", $nama, $email, $no_hp, $alamat);
mysqli_stmt_execute($stmt_cust);
mysqli_stmt_close($stmt_cust);

// ============================================================
//  SIMPAN PESANAN
// ============================================================
$jumlah_produk = array_sum(array_column($produk_verified, 'qty'));
$items_json    = json_encode($produk_verified);

$stmt_order = mysqli_prepare($conn,
    "INSERT INTO orders (customer_name, alamat, items, jumlah_produk, total_price, payment_method, order_date)
     VALUES (?, ?, ?, ?, ?, ?, NOW())"
);
mysqli_stmt_bind_param($stmt_order, "sssiis",
    $nama, $alamat, $items_json, $jumlah_produk, $total, $payment
);
mysqli_stmt_execute($stmt_order);
mysqli_stmt_close($stmt_order);

// ============================================================
//  UPDATE STOK
// ============================================================
$stmt_stok = mysqli_prepare($conn,
    "UPDATE products SET stock = GREATEST(0, stock - ?) WHERE product_name = ?"
);
foreach ($produk_verified as $item) {
    $qty  = (int)$item['qty'];
    $name = $item['name'];
    mysqli_stmt_bind_param($stmt_stok, "is", $qty, $name);
    mysqli_stmt_execute($stmt_stok);
}
mysqli_stmt_close($stmt_stok);

echo json_encode(['status' => 'success', 'total_verified' => $total]);
