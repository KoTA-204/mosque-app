<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Akun;
use App\Models\KategoriAkun;

class NamaMiripRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */

    public function __construct(
        protected string $level,
        protected ?int $scopeId = null,
        protected ?int $exceptId = null,
        protected float $threshold = 85.0,
    ) {}


    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $normalizedNew = $this->normalize($value);

        foreach ($this->existingNames() as $id => $existingName) {
            if ($this->exceptId && (int) $id === (int) $this->exceptId) continue;

            $normalizedExisting = $this->normalize($existingName);

            if ($normalizedNew === $normalizedExisting) {
                $fail("Nama \"{$existingName}\" sudah ada.");
                return;
            }

            similar_text($normalizedNew, $normalizedExisting, $percent);
            if ($percent >= $this->threshold) {
                $fail("Nama \"{$existingName}\" sudah ada.");
                return;
            }
        }
    }

    protected function existingNames()
    {
        return match ($this->level) {
            'kategori'    => KategoriAkun::pluck('nama_kategori', 'id'),
            'subkategori' => Akun::whereNull('parent_id')
                ->when($this->scopeId, fn ($q) => $q->where('kategori_akun_id', $this->scopeId))
                ->pluck('nama_akun', 'id'),
            'akun'        => Akun::whereNotNull('parent_id')
                ->when($this->scopeId, fn ($q) => $q->where('parent_id', $this->scopeId))
                ->pluck('nama_akun', 'id'),
            default       => collect(),
        };
    }

    protected function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $leet = ['0'=>'o','1'=>'l','3'=>'e','4'=>'a','5'=>'s','7'=>'t','8'=>'b','@'=>'a','$'=>'s','!'=>'i','+'=>'t'];
        $value = strtr($value, $leet);
        return preg_replace('/[^a-z0-9]/', '', $value);
    }
}
