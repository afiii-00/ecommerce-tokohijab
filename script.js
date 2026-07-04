let cart = [];

const cartSection      = document.getElementById('cart-section');
const cartItemsList    = document.getElementById('cart-items');
const cartTotalDisplay = document.getElementById('cart-total');

/* ==========================
   TAMBAH KE KERANJANG
========================== */
document.querySelectorAll('.add-to-cart').forEach(button => {
    button.addEventListener('click', () => {
        const name  = button.getAttribute('data-name');
        const price = parseInt(button.getAttribute('data-price'));
        const stock = parseInt(button.getAttribute('data-stock'));

        const existingItem = cart.find(item => item.name === name);

        if (existingItem) {
            if (existingItem.qty >= existingItem.stok) {
                alert("Stok produk sudah habis!");
                return;
            }
            existingItem.qty++;
        } else {
            cart.push({ name, price, qty: 1, stok: stock });
        }

        updateCart();
    });
});

/* ==========================
   UPDATE TAMPILAN KERANJANG
   Perbaikan: gunakan data-action + data-index
   BUKAN inline onclick= (diblokir CSP)
========================== */
function updateCart() {
    cartItemsList.innerHTML = '';
    let total = 0;

    cart.forEach((item, index) => {
        const li = document.createElement('li');

        li.innerHTML = `
<div>
    <b>${escHtml(item.name)}</b><br>
    Rp ${(item.price * item.qty).toLocaleString('id-ID')}
    <div class="qty-control">
        <button class="qty-btn" data-action="kurang" data-index="${index}">&#8722;</button>
        <span class="qty-number">${item.qty}</span>
        <button class="qty-btn" data-action="tambah" data-index="${index}" ${item.qty >= item.stok ? 'disabled' : ''}>+</button>
    </div>
</div>
<button class="delete-btn" data-action="hapus" data-index="${index}">
    <i class="fas fa-trash-alt"></i>
</button>`;
        cartItemsList.appendChild(li);
        total += item.price * item.qty;
    });

    cartTotalDisplay.innerText = `Rp ${total.toLocaleString('id-ID')}`;
    document.getElementById('cart-count').innerText = cart.reduce((sum, item) => sum + item.qty, 0);
}

/* ==========================
   EVENT DELEGATION — satu listener di <ul>
   menangani semua klik tombol keranjang.
   Cara ini tidak diblokir CSP karena tidak pakai inline onclick
========================== */
cartItemsList.addEventListener('click', function(e) {
    const btn = e.target.closest('[data-action]');
    if (!btn) return;

    const action = btn.getAttribute('data-action');
    const index  = parseInt(btn.getAttribute('data-index'));

    if (action === 'hapus')  removeItem(index);
    if (action === 'tambah') tambahQty(index);
    if (action === 'kurang') kurangQty(index);
});

/* ==========================
   ESCAPE HTML (cegah XSS di innerHTML)
========================== */
function escHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ==========================
   HAPUS & QTY
========================== */
function removeItem(index) { cart.splice(index, 1); updateCart(); }

function tambahQty(index) {
    if (cart[index].qty >= cart[index].stok) { alert("Stok produk sudah habis!"); return; }
    cart[index].qty++;
    updateCart();
}

function kurangQty(index) {
    cart[index].qty--;
    if (cart[index].qty <= 0) cart.splice(index, 1);
    updateCart();
}

function toggleCart() { cartSection.classList.toggle('active'); }

/* ==========================
   AMBIL CSRF TOKEN dari meta tag
========================== */
function getCSRFToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

/* ==========================
   CHECKOUT WHATSAPP
========================== */
document.getElementById('checkout-wa').addEventListener('click', async () => {
    if (cart.length === 0) { alert('Keranjang masih kosong'); return; }

    const nama   = document.getElementById('nama').value.trim();
    const email  = document.getElementById('email').value.trim();
    const no_hp  = document.getElementById('no_hp').value.trim();
    const alamat = document.getElementById('alamat').value.trim();

    if (!nama || !email || !no_hp || !alamat) {
        alert('Lengkapi data terlebih dahulu');
        return;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) { alert('Format email tidak valid'); return; }

    const paymentMethod = document.querySelector('input[name="payment"]:checked').value;
    const phoneNumber   = "6285723163866";

    // Susun pesan WhatsApp
    let message = "Halo Sakila Store, saya ingin memesan:\n\n";
    cart.forEach((item, i) => {
        message += `${i + 1}. ${item.name} x${item.qty} - Rp ${(item.price * item.qty).toLocaleString('id-ID')}\n`;
    });
    const totalClient = cart.reduce((sum, item) => sum + item.price * item.qty, 0);
    message += `\n*Total: Rp ${totalClient.toLocaleString('id-ID')}*`;
    message += `\nMetode Pembayaran: ${paymentMethod}`;
    if (paymentMethod === 'Transfer BCA') {
        message += `\nBank BCA | No Rek: 6821700148 | A.N: Sakila`;
    }
    message += `\n\nData Pemesan:\nNama   : ${nama}\nEmail  : ${email}\nNo HP  : ${no_hp}\nAlamat : ${alamat}\n\nMohon diproses, terima kasih!`;

    // Kirim ke database dengan CSRF token
    const items = JSON.stringify(cart);
    const body  = new URLSearchParams({
        csrf_token: getCSRFToken(),
        nama, email, no_hp, alamat, items,
        payment: paymentMethod
    });

    try {
        await fetch('proses_checkout.php', { method: 'POST', body });
    } catch (e) {
        console.warn('Checkout background gagal:', e);
    }

    bukaWA(phoneNumber, message);

    // Reset keranjang
    cart = [];
    updateCart();
    document.getElementById('nama').value   = '';
    document.getElementById('email').value  = '';
    document.getElementById('no_hp').value  = '';
    document.getElementById('alamat').value = '';
    cartSection.classList.remove('active');
});

/* ==========================
   METODE PEMBAYARAN
========================== */
document.querySelectorAll('input[name="payment"]').forEach(radio => {
    radio.addEventListener('change', () => {
        document.getElementById('qris-info').style.display = radio.value === 'QRIS' ? 'block' : 'none';
        document.getElementById('bca-info').style.display  = radio.value === 'Transfer BCA' ? 'block' : 'none';
    });
});

/* ==========================
   FUNGSI BUKA WHATSAPP
========================== */
function bukaWA(nomor, pesan) {
    const encoded = pesan ? encodeURIComponent(pesan) : '';
    const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);

    if (isMobile) {
        window.location.href = 'whatsapp://send?phone=' + nomor + (encoded ? '&text=' + encoded : '');
    } else {
        const waDesktop = 'whatsapp://send?phone=' + nomor + (encoded ? '&text=' + encoded : '');
        const waWeb    = 'https://web.whatsapp.com/send?phone=' + nomor + (encoded ? '&text=' + encoded : '');
        const iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        document.body.appendChild(iframe);
        let appOpened = false;
        window.addEventListener('blur', function onBlur() {
            appOpened = true;
            window.removeEventListener('blur', onBlur);
        });
        iframe.src = waDesktop;
        setTimeout(function() {
            document.body.removeChild(iframe);
            if (!appOpened) window.open(waWeb, '_blank');
        }, 1500);
    }
}

document.getElementById("cart-icon").addEventListener("click", toggleCart);

document.getElementById("close-cart").addEventListener("click", toggleCart);

/* ==========================
   WHATSAPP CHAT POPUP — "Tanya Kila"
   Dipindah dari inline script agar tidak diblokir CSP
========================== */
(function () {
    const WA_NUMBER = '6285723163866';

    const btn    = document.getElementById('wa-chat-btn');
    const popup  = document.getElementById('wa-popup');
    const closeB = document.getElementById('wa-popup-close');
    const overlay= document.getElementById('wa-overlay');
    const input  = document.getElementById('wa-msg-input');
    const sendB  = document.getElementById('wa-send-btn');

    // Elemen tidak ada di halaman lain, cukup return
    if (!btn || !popup) return;

    function openPopup() {
        popup.classList.add('open');
        if (overlay) overlay.classList.add('show');
        setTimeout(function () { if (input) input.focus(); }, 350);
    }

    function closePopup() {
        popup.classList.remove('open');
        if (overlay) overlay.classList.remove('show');
    }

    btn.addEventListener('click', function () {
        popup.classList.contains('open') ? closePopup() : openPopup();
    });

    btn.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') openPopup();
    });

    if (closeB)  closeB.addEventListener('click', closePopup);
    if (overlay) overlay.addEventListener('click', closePopup);

    function kirimPesan() {
        var teks  = input ? input.value.trim() : '';
        var pesan = teks
            ? 'Halo Sakila Store, saya ingin bertanya: ' + teks
            : 'Halo Sakila Store, saya ingin bertanya tentang produk Anda.';
        bukaWA(WA_NUMBER, pesan);
        if (input) input.value = '';
    }

    if (sendB) sendB.addEventListener('click', kirimPesan);
    if (input) input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') kirimPesan();
    });
})();