<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Global legal subjects available to every user.
     *
     * @var list<string>
     */
    protected array $subjects = [
        'Direito Constitucional',
        'Direito Administrativo',
        'Direito Civil',
        'Direito Processual Civil',
        'Direito Penal',
        'Direito Processual Penal',
        'Direito Tributário',
        'Direito Empresarial',
        'Direito Ambiental',
        'Direito Previdenciário',
        'Direito Eleitoral',
        'Direito do Trabalho',
        'Direito Processual do Trabalho',
        'Direitos Humanos',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->subjects as $name) {
            Subject::firstOrCreate([
                'owner_id' => null,
                'name' => $name,
            ]);
        }
    }
}
