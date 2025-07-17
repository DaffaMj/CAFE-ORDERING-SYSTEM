<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KategoriController extends Controller
{
    public function index()
    {
        $data['kategori'] = DB::table('kategori')->get();
        
        return view('admin/kategori', $data);
    }
    
}
