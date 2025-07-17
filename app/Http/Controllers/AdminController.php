<?php

namespace App\Http\Controllers;

use App\Models\KategoriModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function index()
    {
        $data['jumlahproduk'] = DB::table('produk')->count();
        // Untuk jumlah order di dashboard, asumsikan kita ingin melihat order yang sudah selesai atau yang belum
        // Jika ingin hanya yang sudah selesai (pendapatan), maka perlu penyesuaian di sini juga.
        // Untuk saat ini, saya biarkan sesuai aslinya (berdasarkan tanggal hari ini)
        $data['jumlahorder'] = DB::table('pembelian')->where('tanggalbeli', date('Y-m-d'))->count();
        $data['jumlahmeja'] = DB::table('meja')->count();

        $data['produk'] = DB::table('pembeliandetail')
            ->join('produk', 'pembeliandetail.idproduk', '=', 'produk.idproduk')
            ->select(DB::raw('produk.namaproduk, SUM(pembeliandetail.jumlah) as total'))
            ->groupBy('produk.namaproduk')
            ->orderBy('total', 'desc')
            ->get();

        $mejaFromPembelian = DB::table('meja')
            ->select('meja.idmeja', 'meja.nomeja', DB::raw('COUNT(*) as total'))
            ->join('pembelian', 'meja.idmeja', '=', 'pembelian.idmeja')
            ->whereNotNull('pembelian.idmeja')
            ->groupBy('meja.idmeja', 'meja.nomeja');

        $mejaFromDetail = DB::table('meja')
            ->select('meja.idmeja', 'meja.nomeja', DB::raw('COUNT(*) as total'))
            ->join('pembelianmejadetail', 'meja.idmeja', '=', 'pembelianmejadetail.idmeja')
            ->groupBy('meja.idmeja', 'meja.nomeja');

        $data['meja'] = DB::query()
            ->fromSub(
                $mejaFromPembelian->unionAll($mejaFromDetail),
                'gabungan'
            )
            ->select('idmeja', 'nomeja', DB::raw('SUM(total) as total'))
            ->groupBy('idmeja', 'nomeja')
            ->orderByDesc('total')
            ->get();

        $currentYear = Carbon::now()->year;

        // --- START PERUBAHAN UNTUK GRAFIK TRANSAKSI BULANAN (PENDAPATAN SAJA) ---
        $data['pembelian'] = DB::table('pembelian')
            ->select(DB::raw('SUM(totalbeli) as total_beli'), DB::raw('MONTH(tanggalbeli) as month'))
            ->whereYear('tanggalbeli', $currentYear)
            ->whereIn('statusbayar', ['Sudah Di Bayar', 'Lunas']) // Filter berdasarkan status pembayaran yang sudah selesai
            ->groupBy(DB::raw('MONTH(tanggalbeli)'))
            ->get();
        // --- END PERUBAHAN ---

        return view('admin.dashboard', $data);
    }

    public function financial(Request $request)
    {
        $filter_date = $request->input('filter_date');
        $filter_month = $request->input('filter_month');

        // Logic untuk Total Pendapatan dan Total Pesanan Keseluruhan
        $queryOverall = DB::table('pembelian')
            ->whereIn('statusbayar', ['Sudah Di Bayar', 'Lunas']); // Memastikan status 'Lunas' juga terhitung

        // Apply filters to overall statistics
        if ($filter_date) {
            $queryOverall->whereDate('tanggalbeli', $filter_date);
        } elseif ($filter_month) {
            $year = Carbon::parse($filter_month)->year;
            $month = Carbon::parse($filter_month)->month;
            $queryOverall->whereYear('tanggalbeli', $year)
                         ->whereMonth('tanggalbeli', $month);
        }

        $totalRevenueOverall = $queryOverall->sum('totalbeli');
        $totalOrdersOverall = $queryOverall->count();

        // Laporan Harian
        $dailyReportsQuery = DB::table('pembelian')
            ->select(
                DB::raw('DATE(tanggalbeli) as date'),
                DB::raw('SUM(totalbeli) as total_revenue_harian'),
                DB::raw('COUNT(idpembelian) as total_orders_harian'),
                DB::raw('GROUP_CONCAT(DISTINCT metodepembayaran) as metode_pembayaran_harian') // Ambil metode pembayaran
            )
            ->whereIn('statusbayar', ['Sudah Di Bayar', 'Lunas']);

        if ($filter_date) {
            $dailyReportsQuery->whereDate('tanggalbeli', $filter_date);
        } elseif ($filter_month) {
            $year = Carbon::parse($filter_month)->year;
            $month = Carbon::parse($filter_month)->month;
            $dailyReportsQuery->whereYear('tanggalbeli', $year)
                              ->whereMonth('tanggalbeli', $month);
        } else {
            // Default: ambil data 30 hari terakhir
            $dailyReportsQuery->whereDate('tanggalbeli', '>=', Carbon::now()->subDays(29)->toDateString());
        }
        $dailyReports = $dailyReportsQuery->groupBy(DB::raw('DATE(tanggalbeli)'), DB::raw('metodepembayaran')) // Group by date and method
                                          ->orderBy('date', 'desc')
                                          ->get();


        // Laporan Bulanan
        $monthlyReportsQuery = DB::table('pembelian')
            ->select(
                DB::raw('DATE_FORMAT(tanggalbeli, "%Y-%m") as month'),
                DB::raw('SUM(totalbeli) as total_revenue_bulanan'),
                DB::raw('COUNT(idpembelian) as total_orders_bulanan'),
                DB::raw('GROUP_CONCAT(DISTINCT metodepembayaran) as metode_pembayaran_bulanan') // Ambil metode pembayaran
            )
            ->whereIn('statusbayar', ['Sudah Di Bayar', 'Lunas']);

        if ($filter_month) {
            $year = Carbon::parse($filter_month)->year;
            $month = Carbon::parse($filter_month)->month;
            $monthlyReportsQuery->whereYear('tanggalbeli', $year)
                                ->whereMonth('tanggalbeli', $month);
        } else {
            // Default: ambil data 12 bulan terakhir
            $monthlyReportsQuery->whereYear('tanggalbeli', '>=', Carbon::now()->subYears(1)->year);
        }
        $monthlyReports = $monthlyReportsQuery->groupBy(DB::raw('DATE_FORMAT(tanggalbeli, "%Y-%m")'), DB::raw('metodepembayaran')) // Group by month and method
                                              ->orderBy('month', 'desc')
                                              ->get();

        // Produk Terlaris (Top 5) - Diperbarui untuk menyertakan kategori
        $topProductsQuery = DB::table('pembeliandetail')
            ->join('produk', 'pembeliandetail.idproduk', '=', 'produk.idproduk')
            ->join('subkategori', 'produk.idsubkategori', '=', 'subkategori.idsubkategori') // Join dengan tabel subkategori
            ->join('kategori', 'subkategori.idkategori', '=', 'kategori.idkategori')     // Join dengan tabel kategori
            ->join('pembelian', 'pembeliandetail.idpembelian', '=', 'pembelian.idpembelian')
            ->select(
                'produk.namaproduk',
                DB::raw('SUM(pembeliandetail.jumlah) as total_qty_sold'),
                'kategori.namakategori as kategori' // Pilih nama kategori
            )
            ->whereIn('pembelian.statusbayar', ['Sudah Di Bayar', 'Lunas']); // Pastikan hanya dari pesanan yang sudah dibayar/lunas

        if ($filter_date) {
            $topProductsQuery->whereDate('pembelian.tanggalbeli', $filter_date);
        } elseif ($filter_month) {
            $year = Carbon::parse($filter_month)->year;
            $month = Carbon::parse($filter_month)->month;
            $topProductsQuery->whereYear('pembelian.tanggalbeli', $year)
                             ->whereMonth('pembelian.tanggalbeli', $month);
        }

        $topProducts = $topProductsQuery->groupBy('produk.namaproduk', 'kategori.namakategori') // Group by product name AND category name
                                          ->orderBy('total_qty_sold', 'desc')
                                          ->limit(5)
                                          ->get();

        return view('admin.financial', compact(
            'totalRevenueOverall',
            'totalOrdersOverall',
            'dailyReports',
            'monthlyReports',
            'topProducts',
            'filter_date',
            'filter_month'
        ));
    }

    public function kategori()
    {
        $data['kategori'] = DB::table('kategori')->get();
        return view('admin.kategori', $data);
    }

    public function tambahkategori()
    {
        return view('admin.tambahkategori');
    }

    public function simpankategori(Request $request)
    {
        $data = [
            'namakategori' => $request->kategori,
            'idkategori' => $request->kategori, // Perlu dipastikan idkategori ini unik atau auto-increment
        ];
        KategoriModel::create($data); // Atau DB::table('kategori')->insert($data);
        session()->flash('alert', 'Berhasil menambahkan data!');
        return redirect('admin/kategori');
    }

    public function ubahkategori($id)
    {
        $data['kategori'] = KategoriModel::where('idkategori', $id)->first();
        return view('admin.ubahkategori', $data);
    }

    public function updatekategori(Request $request, $id)
    {
        $data = [
            'namakategori' => $request->kategori
        ];
        KategoriModel::where('idkategori', $id)->update($data);
        session()->flash('alert', 'Berhasil mengubah data!');
        return redirect('admin/kategori');
    }

    public function hapuskategori($id)
    {
        DB::table('kategori')->where('idkategori', $id)->delete();
        session()->flash('alert', 'Berhasil menghapus data!');
        return redirect('admin/kategori');
    }

    public function produk()
    {
        $produk = DB::table('produk')
            ->join('subkategori', 'produk.idsubkategori', '=', 'subkategori.idsubkategori')
            ->join('kategori', 'subkategori.idkategori', '=', 'kategori.idkategori')
            ->get();
        $data['produk'] = $produk;
        return view('admin.produk', $data);
    }

    public function tambahproduk()
    {
        $data['subkategori'] = DB::table('subkategori')->get();
        return view('admin.tambahproduk', $data);
    }

    public function simpanproduk(Request $request)
    {
        $namafoto = $request->file('foto')->getClientOriginalName();
        $request->file('foto')->move(public_path('foto'), $namafoto);

        $idproduk = DB::table('produk')->insertGetId([
            'namaproduk' => $request->input('nama'),
            'idsubkategori' => $request->input('idsubkategori'),
            'hargaproduk' => $request->input('harga'),
            'fotoproduk' => $namafoto,
            'deskripsiproduk' => $request->input('deskripsi'),
            'ketersediaanproduk' => $request->input('ketersediaanproduk'),
        ]);

        DB::table('produkjenis')->insert([
            'idproduk' => $idproduk,
            'namaprodukjenis' => 'Reguler',
            'hargaproduk' => $request->input('harga'),
        ]);

        session()->flash('alert', 'Berhasil menambah data!');
        return redirect('admin/produk');
    }

    public function ubahproduk($id)
    {
        $data['produk'] = DB::table('produk')->where('idproduk', $id)->first();
        $data['subkategori'] = DB::table('subkategori')->get();
        $data['produkjenis'] = DB::table('produkjenis')->where('idproduk', $id)->get();
        return view('admin.ubahproduk', $data);
    }

    public function updateproduk(Request $request, $id)
    {
        $data = [
            'namaproduk' => $request->input('nama'),
            'idsubkategori' => $request->input('idsubkategori'),
            'hargaproduk' => $request->input('harga'),
            'deskripsiproduk' => $request->input('deskripsi'),
            'ketersediaanproduk' => $request->input('ketersediaanproduk'),
        ];
        
        if ($request->hasFile('foto')) {
            $namafoto = $request->file('foto')->getClientOriginalName();
            $request->file('foto')->move(public_path('foto'), $namafoto);
            $data['fotoproduk'] = $namafoto;
        }
        
        DB::table('produk')->where('idproduk', $id)->update($data);

        $idprodukjenis = $request->idprodukjenis ?? [];
        $namaprodukjenis = $request->namaprodukjenis ?? [];
        $hargaprodukjenis = $request->hargaprodukjenis ?? [];

        $idprodukjenis_lama = DB::table('produkjenis')->where('idproduk', $id)->pluck('idprodukjenis')->toArray();
        $idprodukjenis_terpakai = [];

        foreach ($namaprodukjenis as $i => $nama_jenis) {
            $harga_jenis = $hargaprodukjenis[$i];
            $id_jenis = $idprodukjenis[$i] ?? null;

            if ($id_jenis && in_array($id_jenis, $idprodukjenis_lama)) {
                DB::table('produkjenis')->where('idprodukjenis', $id_jenis)->update([
                    'namaprodukjenis' => $nama_jenis,
                    'hargaproduk' => $harga_jenis,
                ]);
                $idprodukjenis_terpakai[] = $id_jenis;
            } else {
                $newId = DB::table('produkjenis')->insertGetId([
                    'idproduk' => $id,
                    'namaprodukjenis' => $nama_jenis,
                    'hargaproduk' => $harga_jenis,
                ]);
                $idprodukjenis_terpakai[] = $newId;
            }
        }

        $id_hapus = array_diff($idprodukjenis_lama, $idprodukjenis_terpakai);
        if (!empty($id_hapus)) {
            DB::table('produkjenis')->whereIn('idprodukjenis', $id_hapus)->delete();
        }
        session()->flash('alert', 'Berhasil mengubah data!');
        return redirect('admin/produk');
    }

    public function hapusproduk($id)
    {
        DB::table('produk')->where('idproduk', $id)->delete();
        session()->flash('alert', 'Berhasil menghapus data!');
        return redirect('admin/produk');
    }

    public function logout()
    {
        session()->flush();
        return redirect('home')->with('alert', 'Anda Telah Logout');
    }

    public function pembelian()
    {
        $pembelian = DB::table('pembelian')
            ->leftJoin('meja', 'pembelian.idmeja', '=', 'meja.idmeja')
            ->select('pembelian.*', 'meja.nomeja')
            ->orderBy('pembelian.tanggalbeli', 'desc')
            ->orderBy('pembelian.idpembelian', 'desc')
            ->get();
            
        $dataproduk = [];
        foreach ($pembelian as $row) {
            $idpembelian = $row->idpembelian;
            $produk = DB::table('pembeliandetail')
                ->join('produk', 'pembeliandetail.idproduk', '=', 'produk.idproduk')
                ->where('idpembelian', $idpembelian)
                ->get();
            $dataproduk[$idpembelian] = $produk;
        }

        $data = [
            'pembelian' => $pembelian,
            'dataproduk' => $dataproduk,
        ];
        return view('admin.pembelian', $data);
    }

    public function pembeliantambah()
    {
        $data['produk'] = DB::table('produk')->where('ketersediaanproduk', 'Tersedia')->get();
        $data['meja'] = DB::table('meja')->get();
        return view('admin.pembeliantambah', $data);
    }

    public function pembeliantambahsimpan(Request $request)
    {
        $notransaksi = 'TP' . date("Ymdhis");
        $waktu = date("Y-m-d H:i:s");
        $idpembelian = DB::table('pembelian')->insertGetId([
            'notransaksi' => $notransaksi,
            'idmeja' => $request->input('idmeja'),
            'nama' => $request->input('nama'),
            'nohp' => $request->input('nohp'),
            'metodepembayaran' => $request->input('metodepembayaran'),
            'tanggalbeli' => $request->input('tanggalbeli'),
            'totalbeli' => $request->input('totalbeli'),
            'statusbeli' => 'Belum di Konfirmasi',
            'statusbayar' => 'Belum Bayar', // Default status pembayaran
            'waktu' => $waktu,
        ]);
        
        if ($request->has('produk')) {
            $produkjenisInput = $request->input('produkjenis', []);
            foreach ($request->input('produk') as $idproduk => $jumlah) {
                if ($jumlah > 0) {
                    $produk = DB::table('produk')->where('idproduk', $idproduk)->first();
                    $harga = $produk->hargaproduk;
                    $namaVarian = null;
                    
                    $idprodukjenis = null;
                    if (isset($produkjenisInput[$idproduk]) && $produkjenisInput[$idproduk] != '') {
                        $idprodukjenis = $produkjenisInput[$idproduk];
                        $varian = DB::table('produkjenis')->where('idprodukjenis', $idprodukjenis)->first();
                        if ($varian) {
                            $harga = $varian->hargaproduk;
                            $namaVarian = $varian->namaprodukjenis;
                        }
                    }

                    DB::table('pembeliandetail')->insert([
                        'idpembelian' => $idpembelian,
                        'idproduk' => $idproduk,
                        'jumlah' => $jumlah,
                        'nama' => $produk->namaproduk . ($namaVarian ? ' - ' . $namaVarian : ''),
                        'harga' => $harga,
                        'subharga' => $harga * $jumlah,
                    ]);
                }
            }
        }
        
        session()->flash('alert', 'Berhasil menambah pembelian!');
        return redirect('admin/pembelian');
    }

    public function pembelianhapus($id)
    {
        DB::table('pembeliandetail')->where('idpembelian', $id)->delete();
        DB::table('pembelianmejadetail')->where('idpembelian', $id)->delete();
        DB::table('pembelian')->where('idpembelian', $id)->delete();
        session()->flash('alert', 'Berhasil menghapus data!');
        return redirect('admin/pembelian');
    }

    public function pembayaran($id)
    {
        $datapembelian = DB::table('pembelian')
            ->leftJoin('meja', 'pembelian.idmeja', '=', 'meja.idmeja')
            ->where('pembelian.idpembelian', $id)->first();
        $dataproduk = DB::table('pembeliandetail')
            ->join('produk', 'pembeliandetail.idproduk', '=', 'produk.idproduk')
            ->where('idpembelian', $id)
            ->get();

        $data = [
            'id' => $id,
            'datapembelian' => $datapembelian,
            'dataproduk' => $dataproduk,
        ];

        return view('admin.pembayaran', $data);
    }

    public function simpanpembayaran($id, Request $request)
    {
        if ($request->has('proses')) {
            $catatan = $request->input('catatan');
            $statusbeli = $request->input('statusbeli');
            $statusbayar = $request->input('statusbayar');
            DB::table('pembelian')->where('idpembelian', $id)->update([
                'catatan' => $catatan,
                'statusbeli' => $statusbeli,
                'statusbayar' => $statusbayar,
            ]);

            // Jika statusbayar diubah menjadi 'Sudah Bayar' atau 'Lunas', 
            // perbarui juga statusbelinya menjadi 'Selesai' jika belum
            if (in_array($statusbayar, ['Sudah Di Bayar', 'Lunas']) && $statusbeli != 'Selesai') {
                 DB::table('pembelian')->where('idpembelian', $id)->update([
                    'statusbeli' => 'Selesai',
                ]);
            }
            
            session()->flash('alert', 'Status pembelian berhasil diubah!');
            return redirect('admin/pembelian');
        }
    }

    public function subkategoridaftar()
    {
        $subkategori = DB::table('subkategori')->Join('kategori', 'subkategori.idkategori', '=', 'kategori.idkategori')->orderBy('idsubkategori', 'desc')->get();
        $data = [
            'title' => 'Daftar Sub Kategori',
            'subkategori' => $subkategori
        ];
        return view('admin/subkategoridaftar', $data);
    }
    public function subkategoritambah()
    {
        $data = [
            'title' => 'Tambah Sub Kategori',
        ];
        $data['kategori'] = DB::table('kategori')->get();
        return view('admin/subkategoritambah', $data);
    }
    public function subkategoritambahsimpan(Request $request)
    {
        $namasubkategori = $request->input('namasubkategori');
        $idkategori = $request->input('idkategori');
        DB::table('subkategori')->insert([
            'namasubkategori' => $namasubkategori,
            'idkategori' => $idkategori,
        ]);
        session()->flash('success', 'Berhasil menambahkan data!');
        return redirect('admin/subkategoridaftar');
    }
    public function subkategoriedit($id)
    {
        $row = DB::table('subkategori')->where('idsubkategori', $id)->first();
        $data = [
            'title' => 'Edit Sub Kategori',
            'row' => $row,
        ];
        $data['kategori'] = DB::table('kategori')->get();
        return view('admin/subkategoriedit', $data);
    }
    public function subkategorieditsimpan(Request $request, $id)
    {
        $data = [
            'namasubkategori' => $request->input('namasubkategori'),
            'idkategori' => $request->input('idkategori'),
        ];
        DB::table('subkategori')->where('idsubkategori', $id)->update($data);
        session()->flash('success', 'Data berhasil diedit!');
        return redirect('admin/subkategoridaftar');
    }
    public function subkategorihapus($id)
    {
        DB::table('subkategori')->where('idsubkategori', $id)->delete();
        session()->flash('success', 'Berhasil menghapus data!');
        return redirect('admin/subkategoridaftar');
    }
    public function mejadaftar()
    {
        $meja = DB::table('meja')->orderBy('nomeja', 'asc')->get();
        $data = [
            'title' => 'Daftar Meja',
            'meja' => $meja
        ];
        return view('admin/mejadaftar', $data);
    }
    public function mejatambah()
    {
        $data = [
            'title' => 'Tambah Meja',
        ];
        return view('admin/mejatambah', $data);
    }
    public function mejatambahsimpan(Request $request)
    {
        $nomeja = $request->input('nomeja');
        $deskripsi = $request->input('deskripsi');
        $hargameja = $request->input('hargameja');

        if ($request->hasFile('foto')) {
            $foto = $request->file('foto')->getClientOriginalName();
            $request->file('foto')->move(public_path('foto'), $foto);
        } else {
            $foto = 'default.jpg';
        }

        DB::table('meja')->insert([
            'nomeja' => $nomeja,
            'deskripsi' => $deskripsi,
            'hargameja' => $hargameja,
            'fotomeja' => $foto,
        ]);
        session()->flash('success', 'Berhasil menambahkan data!');
        return redirect('admin/mejadaftar');
    }
    public function mejaedit($id)
    {
        $row = DB::table('meja')->where('idmeja', $id)->first();
        $data = [
            'title' => 'Edit Meja',
            'row' => $row,
        ];
        return view('admin/mejaedit', $data);
    }
    public function mejaeditsimpan(Request $request, $id)
    {
        $data = [
            'nomeja' => $request->input('nomeja'),
            'deskripsi' => $request->input('deskripsi'),
            'hargameja' => $request->input('hargameja'),
        ];
        if ($request->hasFile('foto')) {
            $foto = $request->file('foto')->getClientOriginalName();
            $request->file('foto')->move(public_path('foto'), $foto);
            $data['fotomeja'] = $foto;
        }
        DB::table('meja')->where('idmeja', $id)->update($data);
        session()->flash('success', 'Data berhasil diedit!');
        return redirect('admin/mejadaftar');
    }
    public function mejahapus($id)
    {
        DB::table('meja')->where('idmeja', $id)->delete();
        session()->flash('success', 'Berhasil menghapus data!');
        return redirect('admin/mejadaftar');
    }

    public function mejaulasan($id)
    {
        $detail = DB::table('meja')->where('meja.idmeja', $id)->first();
        $ulasan = DB::table('ulasanmeja')->where('idmeja', $id)->orderBy('idulasan', 'desc')->get();
        $data = [
            'detail' => $detail,
            'ulasan' => $ulasan,
        ];
        return view('admin.mejaulasan', $data);
    }

    public function mejaulasanhapus($idmeja, $idulasan)
    {
        DB::table('ulasanmeja')->where('idulasan', $idulasan)->delete();
        session()->flash('success', 'Berhasil menghapus data!');
        return redirect('admin/mejaulasan/' . $idmeja);
    }

    public function produkulasan($id)
    {
        $detail = DB::table('produk')->where('produk.idproduk', $id)->first();
        $ulasan = DB::table('ulasanmakanan')->where('idproduk', $id)->orderBy('idulasan', 'desc')->get();
        $data = [
            'detail' => $detail,
            'ulasan' => $ulasan,
        ];
        return view('admin.produkulasan', $data);
    }

    public function produkulasanhapus($idproduk, $idulasan)
    {
        DB::table('ulasanmakanan')->where('idulasan', $idulasan)->delete();
        session()->flash('success', 'Berhasil menghapus data!');
        return redirect('admin/produkulasan/' . $idproduk);
    }
}