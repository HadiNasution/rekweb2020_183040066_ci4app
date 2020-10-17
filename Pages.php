<?php

namespace App\Controllers;

class Pages extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Home | Hadi Nasution'
        ];

        return view('pages/home', $data);
    }

    public function about()
    {
        $data = [
            'title' => 'About | Hadi Nasution'
        ];

        return view('pages/about', $data);
    }

    public function contact()
    {
        $data = [
            'title' => 'Contact | Hadi Nasution',
            'alamat' => [
                [
                    'tipe' => 'Rumah',
                    'alamat' => 'Kp.Kaum Cililin rt01/rw07, no.33',
                    'kota' => 'Cililin' 
                ],
                [
                    'tipe' => 'Universitas',
                    'alamat' => 'Jl. Dr. Setiabudi No.193, Gegerkalong, Kec. Sukasari, Kota Bandung, Jawa Barat 40153',
                    'kota' => 'Bandung' 
                ]
            ]
        ];

        return view('pages/contact',$data);
    }

    //--------------------------------------------------------------------

}
