<?php

namespace Tests\Browser;

use App\Models\Kegiatan;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\SeedsFullApp;
use Tests\DuskTestCase;

class KegiatanSystemTest extends DuskTestCase
{
    use DatabaseMigrations, SeedsFullApp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedFullApp();
    }

    private function admin()
    {
        return $this->userByEmail('admin@masjid.id');
    }

    /** ST-F63-01 (+) Tambah Kegiatan Baru */
    public function test_st_f63_01_tambah_kegiatan(): void
    {
        $panitiaId = (string) $this->userByEmail('panitia@masjid.id')->id;

        $this->browse(function (Browser $b) use ($panitiaId) {
            $b->loginAs($this->admin())
                ->visit('/dashboard/kegiatan')
                ->press('Tambah Kegiatan')
                ->waitFor('#createKegiatanForm', 10)
                ->within('#createKegiatanForm', function (Browser $m) use ($panitiaId) {
                    $m->type('nama_kegiatan', 'Bakti Sosial Uji Sistem')
                        ->select('jenis_kegiatan', 'SOSIAL')
                        ->select('panitia_id', $panitiaId);
                });
            $b->script('document.getElementById("create-anggaran-hidden").value = 5000000;');
            $b->script('document.getElementById("create-tanggal_mulai").value = "' . now()->format('Y-m-d') . '";');
            $b->script('document.getElementById("create-tanggal_selesai").value = "' . now()->addDays(3)->format('Y-m-d') . '";');
            $b->press('Simpan')
                ->waitForText('Bakti Sosial Uji Sistem', 10)
                ->assertSee('Bakti Sosial Uji Sistem');
        });
    }

    /** ST-F63-02 (+) Tampilkan Daftar Kegiatan */
    public function test_st_f63_02_daftar_kegiatan(): void
    {
        $this->browse(function (Browser $b) {
            $b->loginAs($this->admin())
                ->visit('/dashboard/kegiatan')
                ->assertSee('Manajemen Kegiatan Khusus')
                ->assertSee('Qurban 1447 H');
        });
    }

    /** ST-F63-03 (+) Edit Kegiatan (modal AJAX) */
    public function test_st_f63_03_edit_kegiatan(): void
    {
        $id  = (int) Kegiatan::where('nama_kegiatan', 'Renovasi Serambi Masjid')->value('id');
        $url = route('dashboard.kegiatan.update', $id);

        $this->browse(function (Browser $b) use ($id, $url) {
            $b->loginAs($this->admin())->visit('/dashboard/kegiatan');

            // Buka modal edit
            $b->script('openEditModal(' . $id . ');');
            $b->waitFor('#editKegiatanForm', 10);

            // Set nama via JS (bypass Alpine/flatpickr reactivity issue)
            $b->script('
                var input = document.querySelector("#editKegiatanForm input[name=\"nama_kegiatan\"]");
                if (input) {
                    var nativeInputValueSetter = Object.getOwnPropertyDescriptor(
                        window.HTMLInputElement.prototype, "value"
                    ).set;
                    nativeInputValueSetter.call(input, "Renovasi Serambi (Diedit)");
                    input.dispatchEvent(new Event("input", { bubbles: true }));
                }
            ');

            // Panggil fungsi submit JS langsung
            $b->script('submitKegiatanForm("editKegiatanForm", "PUT", "' . $url . '");');

            // Tunggu alert sukses muncul di #alertArea (AJAX sukses)
            $b->waitForText('berhasil', 8);

            // Reload halaman untuk verifikasi data tersimpan
            $b->visit('/dashboard/kegiatan')
                ->waitForText('Renovasi Serambi (Diedit)', 10)
                ->assertSee('Renovasi Serambi (Diedit)');
        });
    }

    /** ST-F63-04 (+) Hapus Kegiatan Tanpa Transaksi */
    public function test_st_f63_04_hapus_kegiatan(): void
    {
        $id = (int) Kegiatan::where('nama_kegiatan', 'Renovasi Serambi Masjid')->value('id');

        $this->browse(function (Browser $b) use ($id) {
            $b->loginAs($this->admin())
                ->visit('/dashboard/kegiatan')
                ->assertSee('Renovasi Serambi Masjid');
            $b->script('openDeleteModal(' . $id . ');');
            $b->waitFor('#deleteKegiatanModal', 5);
            $b->script('document.getElementById("deleteKegiatanModalForm").submit();');
            $b->waitUntilMissingText('Renovasi Serambi Masjid', 10)
                ->assertDontSee('Renovasi Serambi Masjid');
        });
    }
}