<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session; // WAJIB: Import Session facade
use Midtrans\Snap;
// use Spatie\FlareClient\View; // Jika tidak digunakan, bisa dihapus

class HomeController extends Controller
{
    public function index()
    {
        $produk = DB::table('produk')->Join('subkategori', 'produk.idsubkategori', '=', 'subkategori.idsubkategori')->Join('kategori', 'subkategori.idkategori', '=', 'kategori.idkategori')->where('ketersediaanproduk', 'Tersedia')->orderBy('idproduk', 'desc')->limit(4)->get();
        $data = [
            'produk' => $produk,
        ];

        return view('home/index', $data);
    }

    public function produk()
    {
        $produk = DB::table('produk')->Join('subkategori', 'produk.idsubkategori', '=', 'subkategori.idsubkategori')->Join('kategori', 'subkategori.idkategori', '=', 'kategori.idkategori')->orderBy('idproduk', 'desc')->get();
        $produkjenis = DB::table('produkjenis')->orderBy('namaprodukjenis', 'asc')->get();
        $data = [
            'produk' => $produk,
            'produkjenis' => $produkjenis,
        ];

        $data['kategori'] = DB::table('kategori')->orderBy('namakategori', 'asc')->get();
        return view('home/produk', $data);
    }

    public function produkdetail($id)
    {
        $produk = DB::table('produk')->where('idproduk', $id)->first();
        $produkjenis = DB::table('produkjenis')->where('idproduk', $id)->get();
        $data = [
            'produk' => $produk,
            'produkjenis' => $produkjenis,
        ];
        return view('home.produkdetail', $data);
    }

    public function meja()
    {
        $data['meja'] = DB::table('meja')->where('hargameja', '!=', null)->orderBy('nomeja', 'asc')->get();
        return view('home/meja', $data);
    }

    public function mejadetail($id)
    {
        $meja = DB::table('meja')->where('idmeja', $id)->first();
        $data = [
            'meja' => $meja,
        ];
        return view('home.mejadetail', $data);
    }

    public function cekmeja(Request $request)
    {
        $tanggal = $request->input('tanggal');
        $idmeja = $request->input('idmeja');
        $jam = 9;
        $options = '';

        while ($jam <= 22) {
            $time = sprintf("%02d:00", $jam);
            $result = DB::table('pembelianmejadetail')
                ->where('jam', $time)
                ->where('tanggal', $tanggal)
                ->where('idmeja', $idmeja)
                ->exists();

            if (!$result) {
                $options .= '<label class="btn btn-success m-1"><input type="radio" name="jam[]" value="' . $time . '"> ' . $time . '</label>';
            } else {
                $options .= '<label class="btn btn-danger m-1"><input type="radio" disabled name="jam[]" value="' . $time . '"> ' . $time . '</label>';
            }

            $jam++;
        }

        return $options;
    }

    public function login()
    {
        return view('home.login');
    }

    public function dologin(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');
        $akun = DB::table('pengguna')
            ->where('email', $email)
            ->where('password', $password)
            ->first();

        if ($akun) {
            if ($akun->level == "Pelanggan") {
                session(['pengguna' => $akun]);
                return redirect('home')->with('alert', 'Anda sukses login');
            } elseif ($akun->level == "Admin") {
                session(['admin' => $akun]);
                return redirect('admin')->with('alert', 'Anda sukses login');
            }
        } else {
            return redirect()->back()->with('alert', 'Anda gagal login, Cek akun anda');
        }
    }

    public function logout()
    {
        session()->flush();
        return redirect('home')->with('alert', 'Anda Telah Logout');
    }

    public function setNoHp(Request $request)
    {
        session(['nohp' => $request->nohp]);
        return redirect()->back()->with('success', 'No. HP berhasil disimpan!');
    }

    public function pesan(Request $request)
    {
        $idproduk = $request->input('idproduk');
        $idprodukjenis = $request->input('idprodukjenis');
        $jumlah = $request->input('jumlah');

        $keranjang = session()->get('keranjang', ['meja' => [], 'makanan' => []]);

        // Ambil data variasi jika ada
        $produkjenis = null;
        if ($idprodukjenis) {
            $produkjenis = DB::table('produkjenis')->where('idprodukjenis', $idprodukjenis)->first();
        }

        // --- Perubahan Penting: Pastikan kunci unik untuk item makanan ---
        // Ini adalah kunci yang akan digunakan untuk mengidentifikasi item di sesi dan untuk penghapusan
        $uniqueKey = $idproduk . '-' . ($idprodukjenis ?? '0'); // Contoh: "123-45", "124-0" (jika tanpa varian)

        // Simpan detail makanan dengan variasi di bawah kunci unik ini
        $keranjang['makanan'][$uniqueKey] = [
            'idproduk' => $idproduk,
            'idprodukjenis' => $idprodukjenis,
            'namaprodukjenis' => $produkjenis->namaprodukjenis ?? null,
            'hargaproduk' => $produkjenis->hargaproduk ?? DB::table('produk')->where('idproduk', $idproduk)->value('hargaproduk'),
            'jumlah' => $jumlah,
        ];
        // --- Akhir Perubahan Penting ---

        session(['keranjang' => $keranjang]);
        session()->flash('alert', 'Berhasil menambahkan makanan ke keranjang');
        return redirect('home/keranjang');
    }

    public function pesanmeja(Request $request)
    {
        $tanggal = $request->input('tanggal');
        $datajam = $request->input('jam');
        $idmeja = $request->input('idmeja');

        $keranjang = session()->get('keranjang', ['meja' => [], 'makanan' => []]);

        foreach ($datajam as $jam) {
            // --- Perubahan Penting: Pastikan kunci unik untuk reservasi meja ---
            // Ini adalah kunci yang akan digunakan untuk mengidentifikasi reservasi meja di sesi
            $uniqueTableKey = $idmeja . '-' . $tanggal . '-' . str_replace(':', '', $jam);
            $keranjang['meja'][$uniqueTableKey] = [
                'idmeja' => $idmeja,
                'tanggal' => $tanggal,
                'jam' => $jam
            ];
            // --- Akhir Perubahan Penting ---
        }

        session(['keranjang' => $keranjang]);
        session()->flash('alert', 'Berhasil menambahkan meja ke keranjang');
        return redirect('home/keranjang');
    }

    public function keranjang()
    {
        // --- Perubahan Penting: Cek apakah kedua kategori keranjang kosong ---
        if (empty(session('keranjang.makanan')) && empty(session('keranjang.meja'))) {
            session()->flash('alert', 'Keranjang kosong');
            return redirect('home');
        }
        // --- Akhir Perubahan Penting ---
        $keranjang = session()->get('keranjang', ['meja' => [], 'makanan' => []]);
        $data = [
            'keranjang' => $keranjang,
        ];

        return view('home.keranjang', $data);
    }

    public function updateKeranjang(Request $request)
    {
        // --- Perubahan Penting: Gunakan uniqueKey sebagai idproduk dari request ---
        $uniqueKey = $request->input('idproduk'); // Ini adalah kunci unik item di sesi
        $jumlah = $request->input('jumlah');

        $keranjang = Session::get('keranjang', ['meja' => [], 'makanan' => []]);

        if (isset($keranjang['makanan'][$uniqueKey])) {
            $keranjang['makanan'][$uniqueKey]['jumlah'] = $jumlah;
            Session::put('keranjang', $keranjang); // Simpan kembali keranjang yang diperbarui

            $item = $keranjang['makanan'][$uniqueKey];
            $totalharga = $item['hargaproduk'] * $item['jumlah'];

            // Return success response with updated total harga
            return response()->json(['success' => true, 'totalharga' => $totalharga, 'message' => 'Jumlah item berhasil diperbarui.']);
        }

        return response()->json(['success' => false, 'message' => 'Item tidak ditemukan di keranjang.'], 404);
        // --- Akhir Perubahan Penting ---
    }

    public function hapuskeranjang(Request $request)
    {
        // --- Perubahan Penting: Gunakan uniqueKey sebagai idproduk dari request ---
        $uniqueKey = $request->input('idproduk'); // Ini adalah kunci unik item di sesi
        $keranjang = Session::get('keranjang', ['meja' => [], 'makanan' => []]);

        if (isset($keranjang['makanan'][$uniqueKey])) {
            unset($keranjang['makanan'][$uniqueKey]); // Hapus item keranjang berdasarkan uniqueKey
            Session::put('keranjang', $keranjang); // Simpan kembali keranjang yang telah dihapus ke sesi
            return response()->json(['success' => true, 'message' => 'Item makanan berhasil dihapus.']);
        }
        return response()->json(['success' => false, 'message' => 'Item makanan tidak ditemukan di keranjang.'], 404);
        // --- Akhir Perubahan Penting ---
    }

    public function hapuskeranjangmeja()
    {
        $keranjang = Session::get('keranjang', ['meja' => [], 'makanan' => []]);

        // Hapus semua item keranjang meja jika ada
        if (isset($keranjang['meja'])) { // Menggunakan isset() lebih aman daripada !empty() untuk unset
            unset($keranjang['meja']);
            Session::put('keranjang', $keranjang);
        }

        return redirect()->to('home/keranjang')->with('alert', 'Semua reservasi meja berhasil dihapus.');
    }

    // --- FUNGSI BARU UNTUK HAPUS MEJA INDIVIDUAL ---
    public function hapusKeranjangMejaSingle(Request $request, $key)
    {
        $keranjang = Session::get('keranjang', ['meja' => [], 'makanan' => []]);
        
        if (isset($keranjang['meja'][$key])) {
            unset($keranjang['meja'][$key]);
            Session::put('keranjang', $keranjang); // Simpan perubahan ke sesi
            return response()->json(['success' => true, 'message' => 'Reservasi meja berhasil dihapus.']);
        }
        return response()->json(['success' => false, 'message' => 'Reservasi meja tidak ditemukan di keranjang.'], 404);
    }
    // --- AKHIR FUNGSI BARU ---

    public function checkout()
    {
        // --- Perubahan Penting: Cek apakah kedua kategori keranjang kosong ---
        if (empty(session('keranjang.makanan')) && empty(session('keranjang.meja'))) {
            session()->flash('alert', 'Anda belum melakukan pemesanan. Silakan pesan terlebih dahulu.');
            return redirect('home/produk');
        }
        // --- Akhir Perubahan Penting ---
        $keranjang = session()->get('keranjang');
        $data['keranjang'] = $keranjang;
        $meja = DB::table('meja')->where('hargameja', null)->orderBy('nomeja', 'asc')->get();
        $data['listmeja'] = $meja;
        return view('home.checkout', $data);
    }

    public function docheckout(Request $request)
    {
        // --- Perubahan Penting: Cek apakah kedua kategori keranjang kosong ---
        if (empty(session('keranjang.makanan')) && empty(session('keranjang.meja'))) {
            session()->flash('alert', 'Anda belum melakukan pemesanan. Silakan pesan terlebih dahulu.');
            return redirect('home/produk');
        }
        // --- Akhir Perubahan Penting ---

        $notransaksi = 'TP' . date("Ymdhis");
        $tanggalbeli = date("Y-m-d");
        $waktu = date("Y-m-d H:i:s");
        $grandtotal = $request->input('grandtotal');
        $nama = $request->input('nama');
        $meja = session('idmeja_qr') ?? $request->input('idmeja');
        $nohp = $request->input('nohp');
        $metodepembayaran = $request->input('metodepembayaran');
        
        session(['nohp' => $nohp]);
        DB::table('pembelian')->insert([
            'idmeja' => $meja,
            'notransaksi' => $notransaksi,
            'tanggalbeli' => $tanggalbeli,
            'totalbeli' => $grandtotal,
            'nama' => $nama,
            'nohp' => $nohp,
            'statusbeli' => 'Belum di Konfirmasi',
            'statusbayar' => 'Belum Bayar',
            'waktu' => $waktu,
            'metodepembayaran' => $metodepembayaran,
        ]);

        session(['pengguna' => [
            'nama' => $nama,
            'nohp' => $nohp,
        ]]);
        $keranjang = session()->get('keranjang');
        $idpembelian = DB::getPdo()->lastInsertId();

        $items = []; // Untuk Midtrans

        // Simpan detail pembelian makanan
        if (!empty($keranjang['makanan'])) {
            foreach ($keranjang['makanan'] as $item) {
                if (!is_array($item)) continue;
                $produk = DB::table('produk')->where('idproduk', $item['idproduk'])->first();
                
                // --- Perubahan: Nama item Midtrans lebih lengkap ---
                $items[] = [
                    'id' => $item['idproduk'] . '-' . ($item['idprodukjenis'] ?? '0'),
                    'price' => $item['hargaproduk'],
                    'quantity' => $item['jumlah'],
                    'name' => $produk->namaproduk . ($item['namaprodukjenis'] ? ' - ' . $item['namaprodukjenis'] : ''),
                ];
                // --- Akhir Perubahan ---

                DB::table('pembeliandetail')->insert([
                    'idpembelian' => $idpembelian,
                    'idproduk' => $item['idproduk'],
                    // --- Perubahan: Nama produk di detail pembelian juga lengkap ---
                    'nama' => $produk->namaproduk . ($item['namaprodukjenis'] ? ' - ' . $item['namaprodukjenis'] : ''),
                    // --- Akhir Perubahan ---
                    'harga' => $item['hargaproduk'],
                    'subharga' => $item['hargaproduk'] * $item['jumlah'],
                    'jumlah' => $item['jumlah'],
                ]);
            }
        }

        // Simpan detail pembelian meja
        if (!empty($keranjang['meja'])) {
            foreach ($keranjang['meja'] as $mejaDetail) {
                $meja = DB::table('meja')->where('idmeja', $mejaDetail['idmeja'])->first();

                // --- Perubahan: Nama item Midtrans lebih lengkap ---
                $items[] = [
                    'id' => $meja->idmeja,
                    'price' => $meja->hargameja,
                    'quantity' => 1,
                    'name' => 'Meja - ' . $meja->nomeja . ' (' . $mejaDetail['tanggal'] . ' ' . $mejaDetail['jam'] . ')',
                ];
                // --- Akhir Perubahan ---

                DB::table('pembelianmejadetail')->insert([
                    'idpembelian' => $idpembelian,
                    'idmeja' => $meja->idmeja,
                    'nomeja' => $meja->nomeja,
                    'harga' => $meja->hargameja,
                    'tanggal' => $mejaDetail['tanggal'],
                    'jam' => $mejaDetail['jam'],
                ]);
            }
        }
        
        // Initialize Midtrans Snap
        if ($metodepembayaran == 'QRIS / Transfer Virtual Account') {
            // --- Perubahan: Menggunakan env() untuk konfigurasi Midtrans (Best Practice) ---
            \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
            \Midtrans\Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
            // --- Akhir Perubahan ---
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;

            $transactionDetails = [
                'order_id' => $notransaksi,
                'gross_amount' => $grandtotal,
            ];

            $customerDetails = [
                'first_name' => $nama,
                'phone' => $nohp,
            ];

            $transactionData = [
                'transaction_details' => $transactionDetails,
                'customer_details' => $customerDetails,
                'item_details' => $items,
            ];

            try {
                $snapToken = Snap::getSnapToken($transactionData);
            } catch (\Exception $e) {
                // --- Perubahan: Penanganan error Midtrans yang lebih baik ---
                return redirect()->back()->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
                // --- Akhir Perubahan ---
            }
            $simpanupdate = [
                'snaptoken' => $snapToken,
            ];
            DB::table('pembelian')->where('idpembelian', $idpembelian)->update($simpanupdate);
        }

        session()->forget('keranjang');
        session(['nohp' => $nohp]); // Pastikan nohp tetap ada di sesi untuk riwayat
        session()->flash('alert', 'Berhasil Checkout');
        return redirect('home/riwayat');
    }

    public function ubahstatusmidtransinternal($id, $status)
    {
        $statusbayar = $status;
        // --- Perubahan: Menggabungkan kondisi status bayar ---
        if ($status == 'settlement' || $status == 'capture') {
            $statusbayar = 'Sudah Bayar';
        }
        // --- Akhir Perubahan ---

        $simpanupdate = [
            'statusbayar' => $statusbayar,
        ];
        DB::table('pembelian')->where('notransaksi', $id)->update($simpanupdate);

        return redirect('home/riwayat');
    }

    public function riwayat()
    {
        if (!session('nohp')) {
            session()->flash('alert', 'Anda belum melakukan pemesanan. Silakan pesan terlebih dahulu.');
            return redirect('home/produk');
        }
        $idpengguna = session('nohp');
        
        $databeli = DB::table('pembelian')
            ->select('*', 'pembelian.idpembelian as idpembelianreal')
            ->where('pembelian.nohp', $idpengguna)
            ->orderBy('pembelian.tanggalbeli', 'desc')
            ->orderBy('pembelian.idpembelian', 'desc')
            ->get();

        $dataproduk = [];
        foreach ($databeli as $row) {
            $idpembelian = $row->idpembelianreal;
            // --- Perubahan: Tidak perlu join 'produk' jika nama sudah di 'pembeliandetail' ---
            // Asumsi: kolom 'nama' di 'pembeliandetail' sudah menyimpan nama produk lengkap (termasuk varian)
            $produk = DB::table('pembeliandetail')
                ->where('idpembelian', $idpembelian)
                ->get();
            $dataproduk[$idpembelian] = $produk;
            // --- Akhir Perubahan ---
        }

        $datameja = [];
        foreach ($databeli as $row) {
            $idpembelian = $row->idpembelianreal;
            $meja = DB::table('pembelianmejadetail')
                ->join('meja', 'pembelianmejadetail.idmeja', '=', 'meja.idmeja')
                ->where('idpembelian', $idpembelian)
                ->get();
            $datameja[$idpembelian] = $meja;
        }

        $datamejabiasa = [];
        foreach ($databeli as $row) {
            $idpembelian = $row->idpembelianreal;
            // --- Perubahan Krusial untuk "nomeja on int" error ---
            // Gunakan leftJoin agar baris pembelian tidak hilang jika idmeja null
            // Gunakan select() untuk hanya mengambil kolom yang relevan dari meja
            // Gunakan first() untuk mengambil satu objek, bukan koleksi
            $meja = DB::table('pembelian')
                ->leftJoin('meja', 'pembelian.idmeja', '=', 'meja.idmeja')
                ->where('pembelian.idpembelian', $idpembelian)
                ->select('meja.nomeja', 'meja.hargameja')
                ->first();
            $datamejabiasa[$idpembelian] = $meja;
            // --- Akhir Perubahan Krusial ---
        }

        $data = [
            'databeli' => $databeli,
            'dataproduk' => $dataproduk,
            'datameja' => $datameja,
            'datamejabiasa' => $datamejabiasa,
        ];

        return view('home.riwayat', $data);
    }

    public function setMeja(Request $request)
    {
        $idmeja = $request->query('idmeja');
        if ($idmeja) {
            session(['idmeja_qr' => $idmeja]);
            session()->flash('alert', 'Meja berhasil dipilih. Silakan lanjutkan ke pemesanan.');
        }
        return redirect('home/produk');
    }

    public function transaksidetail($id)
    {
        $datapembelian = DB::table('pembelian')
            ->where('pembelian.idpembelian', $id)->first();
        $dataproduk = DB::table('pembeliandetail')
            // --- Perubahan: Hapus join 'produk' jika 'nama' sudah ada di 'pembeliandetail' ---
            ->where('idpembelian', $id)
            ->get();
        // --- Akhir Perubahan ---
        $datameja = DB::table('pembelianmejadetail')
            ->join('meja', 'pembelianmejadetail.idmeja', '=', 'meja.idmeja')
            ->where('idpembelian', $id)
            ->get();
        
        $mainMeja = null;
        if ($datapembelian && $datapembelian->idmeja) {
            $mainMeja = DB::table('meja')->where('idmeja', $datapembelian->idmeja)->first();
        }

        $data = [
            'id' => $id,
            'datapembelian' => $datapembelian,
            'dataproduk' => $dataproduk,
            'datameja' => $datameja,
            'meja' => $mainMeja
        ];

        return view('home.transaksidetail', $data);
    }
    
    public function notacetak($id)
    {
        $datapembelian = DB::table('pembelian')
            ->where('pembelian.idpembelian', $id)->first();
        $dataproduk = DB::table('pembeliandetail')
            ->join('produk', 'pembeliandetail.idproduk', '=', 'produk.idproduk')
            ->where('idpembelian', $id)
            ->get();
        $datameja = DB::table('pembelianmejadetail')
            ->join('meja', 'pembelianmejadetail.idmeja', '=', 'meja.idmeja')
            ->where('idpembelian', $id)
            ->get();
        $data = [
            'id' => $id,
            'datapembelian' => $datapembelian,
            'dataproduk' => $dataproduk,
            'datameja' => $datameja,
        ];

        return view('home.notacetak', $data);
    }

    public function pembayaransimpan(Request $request)
    {
        $namabukti = $request->file('bukti')->getClientOriginalName();
        $namafix = date("YmdHis") . $namabukti;
        $request->file('bukti')->move('foto', $namafix);

        $idpembelian = $request->input('idpembelian');
        $nama = $request->input('nama');
        $tanggaltransfer = $request->input('tanggaltransfer');
        $tanggal = date("Y-m-d");

        DB::table('pembayaran')->insert([
            'idpembelian' => $idpembelian,
            'nama' => $nama,
            'tanggaltransfer' => $tanggaltransfer,
            'tanggal' => $tanggal,
            'bukti' => $namafix,
        ]);

        DB::table('pembelian')->where('idpembelian', $idpembelian)->update([
            'statusbeli' => 'Sudah Upload Bukti Pembayaran',
        ]);

        return redirect('home/riwayat')->with('alert', 'Terima kasih');
    }

    public function selesai(Request $request)
    {
        $idpembelian = $request->input('idpembelian');
        DB::table('pembelian')->where('idpembelian', $idpembelian)->update([
            'statusbeli' => 'Selesai'
        ]);
        return redirect('home/riwayat');
    }

    public function faq()
    {
        return view('home.faq');
    }
    public function tentang()
    {
        return view('home.tentang');
    }
    public function kontak()
    {
        return view('home.kontak');
    }
}