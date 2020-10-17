<?php

namespace App\Controllers;

use App\Models\KomikModel;
use CodeIgniter\CodeIgniter;
use CodeIgniter\Config\Config;
use CodeIgniter\HTTP\Files\UploadedFile;

class Komik extends BaseController{
    protected $komikModel;

    public function __construct(){
        $this->komikModel = new KomikModel();
    }

    public function index(){
        //$komik = $this->komikmodel->findAll();

        // siapkan data untuk dikirim ke komik/index
        $data = [
            'title' => 'Daftar Komik',
            'komik' => $this->komikModel->getKomik()
        ];

        return view('komik/index', $data); // tampilkan view dari komik/index sekaligus kirimkan $data
    }

    public function detail($slug){
        $data = [
            'title' => 'Detail Komik',
            'komik' => $this->komikModel->getKomik($slug)
        ];

        // jika komik tidak ada
        if(empty($data['komik'])){
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Judul komik ' . $slug .' tidak ditemukan.');
        }

        return view('komik/detail', $data);
    }

    public function create(){

        $data = [
            'title' => 'Form Tambah Data Komik',
            'validation' => \Config\Services::validation()
        ];

        return view('komik/create',$data);
    }

    public function save(){

        // set validasi Judul dan Sampul
        if(!$this->validate([
            'judul' => [
                'rules' => 'required|is_unique[komik.judul]',
                'errors' => [
                    'required' => '{field} tidak boleh kosong.',
                    'is_unique' => '{field} sudah terdaftar.'
                ]
            ],
            'sampul' => [
                'rules' => 'max_size[sampul,1024]|is_image[sampul]|mime_in[sampul,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    'max_size' => 'Ukuran gambar terlalu besar',
                    'is_image' => 'Yang anda pilih bukan gambar',
                    'mime_in' => 'Format gambar tidak di dukung'
                ]
            ]
        ])) {
            // $validation = \Config\Services::validation();
            // return redirect()->to('/komik/create')->withInput()->with('validation',$validation);

            // kalo beres, direct ke halaman Create
            return redirect()->to('/komik/create')->withInput();
        }

        // simpan gambar sampul ke database

        // dapatkan nama file nya
        $fileSampul = $this->request->getFile('sampul');

        // jika user tidak upload sampul
        if($fileSampul->getError()==4){
            // pakai sampul default
            $namaSampul = "default.png";
        }else{
            // jika upload, sampul... pindahkan ke folder public/img
            $fileSampul->move('img');
            $namaSampul = $fileSampul->getName();
        }

        $slug = url_title($this->request->getVar('judul'),'-',true);

        $this->komikModel->save([
            'judul' => $this->request->getVar('judul'),
            'slug' => $slug,
            'penulis' => $this->request->getVar('penulis'),
            'penerbit' => $this->request->getVar('penerbit'),
            'sampul' => $namaSampul
        ]);
        
        // alert action success
        session()->setFlashdata('pesan','Data berhasil ditambahkan.');
    
        // direct ke halaman utama, jika berhasil
        return redirect()->to('/komik');
    }

    public function delete($id){

        //dapatkan nama gambar nya
        $komik = $this->komikModel->find($id);

        // cek, apakah gambar default yang akan dihapus?
        if($komik['sampul'] != 'default.png'){

            //jika bukan, maka hapus gambar yang ada di folder public/img juga
            unlink('img/' . $komik['sampul']);
        }

        // hapus dari database
        $this->komikModel->delete($id);
        session()->setFlashdata('pesan', 'Data berhasil dihapus.');
        return redirect()->to('/komik');
    }

    public function edit($slug){
        $data = [
            'title' => 'Form Ubah Data Komik',
            'validation' => \Config\Services::validation(),
            'komik' => $this->komikModel->getKomik($slug)
        ];

        return view('komik/edit', $data);
    }

    public function update($id){

        $komikLama = $this->komikModel->getKomik($this->request->getVar('slug'));

        // kondisikan rule untuk judul
        if($komikLama['judul'] == $this->request->getVar('judul')){
            $rule_judul = 'required';
        }else{
            $rule_judul = 'required|is_unique[komik.judul]';
        }

        if (!$this->validate([
            'judul' => [
                'rules' => $rule_judul,
                'errors' => [
                    'required' => '{field} tidak boleh kosong.',
                    'is_unique' => '{field} sudah terdaftar.'
                ]
                ],
            'sampul' => [
                'rules' => 'max_size[sampul,1024]|is_image[sampul]|mime_in[sampul,image/jpg,image/jpeg,image/png]',
                'errors' => [
                    'max_size' => 'Ukuran gambar terlalu besar',
                    'is_image' => 'Yang anda pilih bukan gambar',
                    'mime_in' => 'Format gambar tidak di dukung'
                ]
            ]
        ])) {
            return redirect()->to('/komik/edit/' . $this->request->getVar('slug'))->withInput();
        }

        $fileSampul = $this->request->getFile('sampul');

        // cek gambar, pake gambar lama atau upload baru?
        if($fileSampul->getError() == 4){
            $namaSampul = $this->request->getVar('sampulLama');
        }else{
            $namaSampul = $fileSampul->getName();
            //upload gambar
            $fileSampul->move('img',$namaSampul);
            
            //hapus file lama
            unlink('img/'. $this->request->getVar('sampulLama'));
        }

        $slug = url_title($this->request->getVar('judul'), '-', true);

        $this->komikModel->save([
            'id' => $id,
            'judul' => $this->request->getVar('judul'),
            'slug' => $slug,
            'penulis' => $this->request->getVar('penulis'),
            'penerbit' => $this->request->getVar('penerbit'),
            'sampul' => $namaSampul
        ]);

        session()->setFlashdata('pesan', 'Data berhasil diubah.');

        return redirect()->to('/komik');
    }

}
