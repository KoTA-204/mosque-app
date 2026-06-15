<?php

namespace Tests\Feature\Integration;

use Illuminate\Database\QueryException;
use Tests\TestCase;
use Tests\Feature\Integration\Concerns\InteractsWithRbac;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Integrasi constraint DB: transaksi.user_id memakai restrictOnDelete,
 * sehingga user yang masih memiliki transaksi tidak boleh dihapus pada level DB.
 *
 * CATATAN PENTING TENTANG DUA LAYER PERLINDUNGAN:
 *
 * Layer 1 (HTTP/Controller): UserController::destroy() memanggil
 * $user->hasTransaksi() sebelum $user->delete(). Jika user punya transaksi,
 * controller mengembalikan response JSON 422 atau redirect dengan error —
 * delete tidak pernah sampai ke DB.
 *
 * Layer 2 (DB/FK): Jika $user->delete() dipanggil langsung di model (bypass
 * controller), PostgreSQL FK restrictOnDelete akan melempar QueryException
 * karena transaksi masih mereferensikan user tersebut.
 *
 * Test IT-F09-01 menguji Layer 2 secara langsung (model level), yang
 * memverifikasi integritas referensial DB terlepas dari logika controller.
 * Ini adalah pendekatan whitebox yang valid untuk memverifikasi constraint DB.
 */
class UserConstraintIntegrationTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithRbac;

    /**
     * IT-F09-01 (-): Hapus user yang punya transaksi -> ditolak FK (QueryException).
     *
     * CATATAN: Test ini menguji constraint DB (Layer 2) secara langsung via
     * $user->delete(), bukan via HTTP. Di layer HTTP (Layer 1), UserController
     * sudah memiliki guard hasTransaksi() yang mencegah delete sebelum sampai
     * ke DB. Test ini memverifikasi bahwa meskipun guard HTTP dilewati,
     * DB tetap melindungi integritas data via FK restrictOnDelete.
     *
     * Bergantung pada penegakan foreign key PostgreSQL yang aktif secara default.
     */
    public function test_it_f09_01_user_dengan_transaksi_tidak_bisa_dihapus(): void
    {
        $user = $this->buatUser($this->buatRole('Bendahara'));
        $this->buatTransaksi(['user_id' => $user->id]);

        $this->expectException(QueryException::class);

        $user->delete();
    }
}