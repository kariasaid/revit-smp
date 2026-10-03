<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Auth::login');
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attemptLogin');
$routes->get('logout', 'Auth::logout');

$routes->group('', ['filter' => 'auth'], static function (RouteCollection $routes): void {
	$routes->get('dashboard', 'Dashboard::index');
	$routes->get('admin/keuangan/(:segment)', 'AdminKeuangan::index/$1');
	$routes->get('admin/keuangan/(:segment)/cetak', 'AdminKeuangan::print/$1');
	$routes->post('admin/keuangan/(:segment)/simpan', 'AdminKeuangan::store/$1');
	$routes->post('admin/keuangan/(:segment)/unggah', 'AdminKeuangan::uploadDocument/$1');
	$routes->get('admin/keuangan/(:segment)/dokumen/(:num)', 'AdminKeuangan::viewDocument/$1/$2');
	$routes->post('admin/keuangan/(:segment)/dokumen/(:num)/hapus', 'AdminKeuangan::deleteDocument/$1/$2');
	$routes->post('admin/keuangan/(:segment)/(:num)/update', 'AdminKeuangan::update/$1/$2');
	$routes->post('admin/keuangan/(:segment)/(:num)/hapus', 'AdminKeuangan::delete/$1/$2');
	$routes->post('admin/keuangan/(:segment)/upload', 'AdminKeuangan::uploadDocument/$1');
	$routes->get('admin/sekolah', 'AdminSekolah::index');
	$routes->get('admin/sekolah/(:num)', 'AdminSekolah::detail/$1');
	$routes->post('admin/sekolah/simpan', 'AdminSekolah::store');
	$routes->post('admin/sekolah/(:num)/bantuan', 'AdminSekolah::storeBantuan/$1');
	$routes->post('admin/bantuan/(:num)/update', 'AdminSekolah::updateBantuan/$1');
	$routes->post('admin/bantuan/(:num)/hapus', 'AdminSekolah::deleteBantuan/$1');
	$routes->get('admin/verifikasi-time-schedule', 'VerifikasiTimeSchedule::index');
	$routes->post('admin/verifikasi-time-schedule/(:num)', 'VerifikasiTimeSchedule::update/$1');
	$routes->get('admin/pelaporan', 'Pelaporan::adminIndex');
	$routes->get('admin/pelaporan/(:num)', 'Pelaporan::adminIndex/$1');
	$routes->post('admin/pelaporan/(:num)/validasi', 'Pelaporan::validasiAdmin/$1');
	$routes->post('admin/pelaporan/(:num)/hapus', 'Pelaporan::hapusAdmin/$1');
	$routes->get('perencana/time-schedule', 'TimeSchedule::index');
	$routes->post('perencana/time-schedule/simpan', 'TimeSchedule::save');
	$routes->get('tim-p2sp', 'TimP2SP::index');
	$routes->post('tim-p2sp/simpan', 'TimP2SP::save');
	$routes->get('perencana/monitoring-progres', 'MonitoringPerencana::index');

	$routes->get('pelaksanaan/progres', 'Pelaksanaan::progres');
	$routes->get('pelaksanaan/progres/(:num)', 'Pelaksanaan::progres/$1');
	$routes->get('pelaksanaan/progres/lihat/(:num)', 'Pelaksanaan::lihatProgres/$1');
	$routes->get('pelaksanaan/progres/print/(:num)', 'Pelaksanaan::printProgres/$1');
	$routes->get('pelaksanaan/progres/input/(:num)', 'Pelaksanaan::formProgres/$1');
	$routes->post('pelaksanaan/progres/simpan', 'Pelaksanaan::simpanProgres');
	$routes->get('pelaksanaan/progres/hapus/(:num)', 'Pelaksanaan::hapusProgres/$1');
	$routes->get('pelaksanaan/kurva-s', 'Pelaksanaan::kurvaS');
	$routes->get('pelaksanaan/kurva-s/(:num)', 'Pelaksanaan::kurvaS/$1');
	$routes->post('pelaksanaan/kurva-s/kalkulasi', 'Pelaksanaan::kalkulasiUlang');
	$routes->post('pelaksanaan/kurva-s/pdf', 'Pelaksanaan::unduhPdfKurvaS');
	$routes->post('pelaksanaan/kurva-s/pdf/(:num)', 'Pelaksanaan::unduhPdfKurvaS/$1');

	$routes->get('pelaporan/50', 'Pelaporan::index/50');
	$routes->get('pelaporan/100', 'Pelaporan::index/100');
	$routes->get('pelaporan/lihat/(:num)', 'Pelaporan::lihat/$1');
	$routes->post('pelaporan/unggah', 'Pelaporan::unggah');

	$routes->get('profil', 'Profil::index');
	$routes->post('profil/update', 'Profil::update');
	$routes->post('profil/upload-foto', 'Profil::uploadFoto');
	$routes->post('profil/upload-ttd', 'Profil::uploadTtd');

	$routes->get('adendum', 'Adendum::index');
	$routes->get('adendum/(:num)', 'Adendum::index/$1');
	$routes->get('adendum/form', 'Adendum::form');
	$routes->get('adendum/form/(:num)', 'Adendum::form/$1');
	$routes->post('adendum/simpan', 'Adendum::simpan');
	$routes->get('adendum/hapus/(:num)', 'Adendum::hapus/$1');

	$routes->get('validasi/progres', 'ValidasiProgres::index');
	$routes->post('validasi/progres/(:num)', 'ValidasiProgres::update/$1');
	$routes->post('validasi/progres/(:num)/hapus', 'ValidasiProgres::delete/$1');
	$routes->post('validasi/progres/hapus-massal', 'ValidasiProgres::deleteMultiple');
});
