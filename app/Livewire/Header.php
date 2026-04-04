<?php

namespace App\Livewire;

use Livewire\Component;

class Header extends Component
{
    public string $type = 'candidate';

    public function mount(string $type = 'candidate')
    {
        $this->type = in_array($type, ['candidate', 'employer']) ? $type : 'candidate';
    }

    public function render()
    {
        return view('livewire.client.header', [
            'isEmployerHeader' => $this->type === 'employer',
        ]);
    }
}
